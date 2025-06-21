<?php

namespace Azuriom\Plugin\ApiLimiter\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\ApiLimiter\Models\LimiterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class LimiterController extends Controller
{
    /**
     * Constructor - ensure admin access
     */
    public function __construct()
    {
        $this->middleware('can:api-limiter.manage');
        
        // Check compatibility on every admin request
        $this->checkCompatibility();
    }
    
    /**
     * Check plugin compatibility with current Azuriom version
     */
    protected function checkCompatibility(): void
    {
        try {
            // Check if plugin is disabled
            $pluginDisabled = LimiterSetting::getValue('plugin_disabled_due_to_error', false);
            
            if ($pluginDisabled) {
                // Show warning to administrator
                session()->flash('error', trans('api-limiter::admin.compatibility_error'));
            }
            
        } catch (\Exception $e) {
            \Log::error('ApiLimiter: Compatibility check failed', [
                'error' => $e->getMessage()
            ]);
        }
    }




    /**
     * Clear all rate limit data.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        // Clear all rate limiting data
        Cache::flush();
        
        // Clear Laravel Rate Limiter
        RateLimiter::clear('api_rate_limit:*');

        return redirect()->route('api-limiter.admin.settings')
            ->with('success', trans('api-limiter::admin.messages.cache_cleared'));
    }

    /**
     * Show all API routes discovered in the system.
     *
     * @return \Illuminate\View\View
     */
    public function apiRoutes()
    {
        $routes = $this->discoverApiRoutes();
        $settings = LimiterSetting::getAllSettings();
        
        // Add rule information and sort
        $processedRoutes = [];
        $adminRoutes = [];
        
        foreach ($routes as $route) {
            $isAdminRoute = str_starts_with($route['uri'], 'admin/');
            
            if (!$isAdminRoute) {
                // For API routes add rule information
                $ruleData = LimiterSetting::getRuleParamsForRoute($route['uri'], $route['name']);
                $route['rule_info'] = $this->getRuleDisplayInfo($ruleData, $settings);
                $route['has_api_limiter'] = $this->hasApiLimiterProtection($route);
                

                
                $processedRoutes[] = $route;
            } else {
                // Admin routes separately
                $route['rule_info'] = null; // Don't show rules for admin routes
                $route['has_api_limiter'] = false;
                $adminRoutes[] = $route;
            }
        }
        
        // Combine: API routes first, then Admin routes
        $allRoutes = array_merge($processedRoutes, $adminRoutes);
        
        return view('api-limiter::admin.api-routes', [
            'routes' => $allRoutes,
            'apiRoutesCount' => count($processedRoutes),
            'adminRoutesCount' => count($adminRoutes),
            'settings' => $settings
        ]);
    }

    /**
     * Show route-specific rules management page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $allRoutes = $this->discoverApiRoutes();
        $currentRules = LimiterSetting::getRouteRules();
        $settings = LimiterSetting::getAllSettings();
        
        // Filter only API routes (exclude Admin routes)
        $routes = array_filter($allRoutes, function($route) {
            return !str_starts_with($route['uri'], 'admin/');
        });
        
        return view('api-limiter::admin.route-rules', compact('routes', 'currentRules', 'settings'));
    }

    /**
     * Update route-specific rules and general settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {


        // Basic validation - all custom fields are optional
        $request->validate([
            // General settings
            'enabled' => 'nullable|string',
            'max_attempts' => 'required|integer|min:1|max:10000',
            'per_minutes' => 'required|integer|min:1|max:1440',
            'limit_by' => 'required|string|in:ip,user',
            'whitelist_ips' => 'nullable|string|max:1000',
            'default_rule' => 'required|string|in:no_restrictions,rate_limit,whitelist_only,restricted,rate_limit_custom,whitelist_custom,rate_limit_whitelist,rate_limit_whitelist_custom,whitelist_rate_limit_custom',
            // Route rules - all fields optional for now
            'custom_rules' => 'nullable|array|max:100',
            'custom_rules.*.route' => 'required|string|min:1|max:255|regex:/^[a-zA-Z0-9\/_\*\.\-]+$/',
            'custom_rules.*.rule' => 'required|string|in:no_restrictions,rate_limit,whitelist_only,restricted,rate_limit_custom,whitelist_custom,rate_limit_whitelist,rate_limit_whitelist_custom,whitelist_rate_limit_custom',
            'custom_rules.*.max_attempts' => 'nullable|integer|min:1|max:10000',
            'custom_rules.*.per_minutes' => 'nullable|integer|min:1|max:1440',
            'custom_rules.*.whitelist_ips' => 'nullable|string|max:2000',
        ]);

        // Additional validation for custom rules
        $customRules = $request->input('custom_rules', []);
        $errors = [];
        
        foreach ($customRules as $index => $customRule) {
            $ruleType = $customRule['rule'] ?? '';
            
            // Check required fields for rate limiting custom rules
            if (in_array($ruleType, ['rate_limit_custom', 'rate_limit_whitelist_custom', 'whitelist_rate_limit_custom'])) {
                if (!isset($customRule['max_attempts']) || trim($customRule['max_attempts']) === '') {
                    $errors["custom_rules.{$index}.max_attempts"] = trans('api-limiter::admin.validation.max_attempts_required');
                }
                if (!isset($customRule['per_minutes']) || trim($customRule['per_minutes']) === '') {
                    $errors["custom_rules.{$index}.per_minutes"] = trans('api-limiter::admin.validation.per_minutes_required');
                }
            }
            
            // Check required fields for whitelist custom rules
            if (in_array($ruleType, ['whitelist_custom', 'rate_limit_whitelist_custom', 'whitelist_rate_limit_custom'])) {
                if (!isset($customRule['whitelist_ips']) || trim($customRule['whitelist_ips']) === '') {
                    $errors["custom_rules.{$index}.whitelist_ips"] = trans('api-limiter::admin.validation.whitelist_ips_required');
                } else {
                    // IP address validation
                    $ips = array_map('trim', explode(',', $customRule['whitelist_ips']));
                    foreach ($ips as $ip) {
                        if (!empty($ip)) {
                            // Check for valid IP or CIDR
                            if (!filter_var($ip, FILTER_VALIDATE_IP) && 
                                !preg_match('/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$/', $ip) && 
                                !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                                $errors["custom_rules.{$index}.whitelist_ips"] = trans('api-limiter::admin.validation.invalid_ip', ['ip' => $ip]);
                                break; // Stop checking other IPs for this field
                            }
                        }
                    }
                }
            }
        }
        
        // If there are validation errors, return with errors
        if (!empty($errors)) {
            return redirect()->back()
                ->withErrors($errors)
                ->withInput();
        }

        // Convert checkbox value to boolean
        $enabled = $request->has('enabled') && $request->input('enabled') === 'on';
        
        // Save general settings
        LimiterSetting::setValues([
            'enabled' => $enabled,
            'max_attempts' => (int) $request->input('max_attempts'),
            'per_minutes' => (int) $request->input('per_minutes'),
            'limit_by' => $request->input('limit_by'),
            'whitelist_ips' => $request->input('whitelist_ips', ''),
            'default_rule' => $request->input('default_rule', 'rate_limit'),
        ]);
        
        // If enabling plugin - remove error disable flag
        if ($enabled) {
            LimiterSetting::setValue('plugin_disabled_due_to_error', false);
        }
        
        // Process custom rules
        $customRules = $request->input('custom_rules', []);
        $rules = [];
        
        foreach ($customRules as $customRule) {
            if (!empty($customRule['route']) && !empty($customRule['rule'])) {
                $ruleType = $customRule['rule'];
                
                // For custom rules, always create array with parameters
                if (str_contains($ruleType, '_custom')) {
                    $ruleData = [
                        'type' => $ruleType
                    ];
                    
                    // Add parameters for rate limiting custom rules
                    if (in_array($ruleType, ['rate_limit_custom', 'rate_limit_whitelist_custom', 'whitelist_rate_limit_custom'])) {
                        // Only add if values are provided and valid
                        if (isset($customRule['max_attempts']) && is_numeric($customRule['max_attempts'])) {
                            $ruleData['max_attempts'] = (int) $customRule['max_attempts'];
                        }
                        if (isset($customRule['per_minutes']) && is_numeric($customRule['per_minutes'])) {
                            $ruleData['per_minutes'] = (int) $customRule['per_minutes'];
                        }
                    }
                    
                    // Add parameters for whitelist custom rules
                    if (in_array($ruleType, ['whitelist_custom', 'rate_limit_whitelist_custom', 'whitelist_rate_limit_custom'])) {
                        // Only add if value is provided and not empty
                        if (isset($customRule['whitelist_ips']) && trim($customRule['whitelist_ips']) !== '') {
                            $ruleData['whitelist_ips'] = trim($customRule['whitelist_ips']);
                        }
                    }
                    
                    $rules[$customRule['route']] = $ruleData;
                } else {
                    // For regular rules, save only the type
                    $rules[$customRule['route']] = $ruleType;
                }
            }
        }
        
        LimiterSetting::setRouteRules($rules);

        // Clear cache
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');

        return redirect()
            ->route('api-limiter.admin.settings')
            ->with('success', trans('api-limiter::admin.messages.settings_saved'));
    }

    /**
     * Get current rate limiting statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        // Here we can add API usage statistics
        // For now return basic information
        
        return response()->json([
            'enabled' => LimiterSetting::isEnabled(),
            'requests_per_minute' => LimiterSetting::getRequestsPerMinute(),
            'whitelist_count' => count(LimiterSetting::getWhitelistIps()),
            'limit_by' => LimiterSetting::getLimitBy(),
            'api_routes_count' => count($this->discoverApiRoutes()),
        ]);
    }

    /**
     * Discover all API routes in the system.
     *
     * @return array
     */
    private function discoverApiRoutes()
    {
        $routes = [];
        $routeCollection = Route::getRoutes();
        
        foreach ($routeCollection as $route) {
            $uri = $route->uri();
            
            // Filter API routes and administrative routes related to API
            if (str_starts_with($uri, 'api/') || str_starts_with($uri, 'api') || 
                (str_starts_with($uri, 'admin/') && str_contains($uri, 'api'))) {
                $action = $route->getAction();
                $controller = $action['controller'] ?? 'Closure';
                
                // Determine route source (core Azuriom or plugin)
                $source = $this->determineRouteSource($controller, $uri);
                
                $routes[] = [
                    'uri' => $uri,
                    'methods' => implode('|', $route->methods()),
                    'name' => $route->getName(),
                    'controller' => $controller,
                    'source' => $source,
                    'middleware' => $this->getRouteMiddleware($route),
                    'description' => $this->getRouteDescription($uri, $source),
                ];
            }
        }
        
        // Sort by URI
        usort($routes, function($a, $b) {
            return strcmp($a['uri'], $b['uri']);
        });
        
        return $routes;
    }

    /**
     * Determine the source of the route (core or plugin).
     *
     * @param string $controller
     * @param string $uri
     * @return array
     */
    private function determineRouteSource($controller, $uri)
    {
        if (str_contains($controller, 'Plugin\\')) {
            // This is a plugin
            preg_match('/Plugin\\\\([^\\\\]+)\\\\/', $controller, $matches);
            $pluginName = $matches[1] ?? 'Unknown';
            
            return [
                'type' => 'plugin',
                'name' => $pluginName,
                'display' => trans('api-limiter::admin.source_types.plugin', ['name' => $pluginName])
            ];
        } elseif (str_contains($controller, 'Api\\')) {
            // This is core Azuriom API
            return [
                'type' => 'core',
                'name' => 'Azuriom',
                'display' => trans('api-limiter::admin.source_types.core')
            ];
        } elseif (str_starts_with($uri, 'api/') || str_starts_with($uri, 'admin/')) {
            // If URI starts with api/ or admin/ but controller is unknown
            // Try to determine by URI
            if (str_contains($uri, '/api-limiter/') || str_contains($uri, 'api-limiter')) {
                return [
                    'type' => 'plugin',
                    'name' => 'ApiLimiter',
                    'display' => trans('api-limiter::admin.source_types.plugin', ['name' => 'ApiLimiter'])
                ];
            } elseif (str_contains($uri, '/skin-api/') || str_contains($uri, 'skin-api')) {
                return [
                    'type' => 'plugin',
                    'name' => 'SkinApi',
                    'display' => trans('api-limiter::admin.source_types.plugin', ['name' => 'SkinApi'])
                ];
            } elseif (str_contains($uri, '/apiextender/') || str_contains($uri, 'apiextender')) {
                return [
                    'type' => 'plugin',
                    'name' => 'ApiExtender',
                    'display' => trans('api-limiter::admin.source_types.plugin', ['name' => 'ApiExtender'])
                ];
            } else {
                return [
                    'type' => 'core',
                    'name' => 'Azuriom',
                    'display' => trans('api-limiter::admin.source_types.core')
                ];
            }
        } else {
            return [
                'type' => 'unknown',
                'name' => 'Unknown',
                'display' => trans('api-limiter::admin.source_types.unknown')
            ];
        }
    }

    /**
     * Get middleware applied to the route.
     *
     * @param \Illuminate\Routing\Route $route
     * @return array
     */
    private function getRouteMiddleware($route)
    {
        $middleware = [];
        
        // Get middleware from route
        $routeMiddleware = $route->middleware();
        
        foreach ($routeMiddleware as $mw) {
            if (is_string($mw)) {
                $middleware[] = $mw;
            } elseif (is_object($mw)) {
                $middleware[] = get_class($mw);
            }
        }
        
        return $middleware;
    }

    /**
     * Check if route has API Limiter protection (either explicit or through api group).
     *
     * @param array $route
     * @return bool
     */
    private function hasApiLimiterProtection($route)
    {
        // Check explicit presence of our middleware
        if (in_array('Azuriom\Plugin\ApiLimiter\Middleware\ApiLimiter', $route['middleware'])) {
            return true;
        }
        
        // Check presence of 'api' group - it automatically includes our middleware
        if (in_array('api', $route['middleware'])) {
            return true;
        }
        
        // Additional check: if route starts with api/ and not explicitly excluded
        if (str_starts_with($route['uri'], 'api/')) {
            // Check if API Limiter is not disabled for this route
            $settings = LimiterSetting::getAllSettings();
            if (!$settings['enabled']) {
                return false; // API Limiter disabled globally
            }
            
            // Check rules for specific route
            $ruleData = LimiterSetting::getRuleParamsForRoute($route['uri'], $route['name']);
            $ruleType = $ruleData['type'] ?? 'rate_limit';
            
            // If rule is "no_restrictions", then no protection
            if ($ruleType === 'no_restrictions') {
                return false;
            }
            
            return true; // By default all api/ routes are protected
        }
        
        return false;
    }

    /**
     * Get description for the route based on URI pattern.
     *
     * @param string $uri
     * @param array $source
     * @return string
     */
    private function getRouteDescription($uri, $source)
    {
        $descriptions = [
            // Public API routes
            'api/auth/authenticate' => trans('api-limiter::admin.route_descriptions.auth_authenticate'),
            'api/auth/verify' => trans('api-limiter::admin.route_descriptions.auth_verify'),
            'api/auth/logout' => trans('api-limiter::admin.route_descriptions.auth_logout'),
            'api/azlink' => trans('api-limiter::admin.route_descriptions.azlink'),
            'api/skin-api/skins' => trans('api-limiter::admin.route_descriptions.skin_api_skins'),
            'api/skin-api/capes' => trans('api-limiter::admin.route_descriptions.skin_api_capes'),
            'api/skin-api/avatars' => trans('api-limiter::admin.route_descriptions.skin_api_avatars'),
            'api/apiextender' => trans('api-limiter::admin.route_descriptions.apiextender'),
            'api/posts' => trans('api-limiter::admin.route_descriptions.posts'),
            'api/servers' => trans('api-limiter::admin.route_descriptions.servers'),
            'api/rss' => trans('api-limiter::admin.route_descriptions.rss'),
            'api/atom' => trans('api-limiter::admin.route_descriptions.atom'),
            
            // Admin API routes
            'admin/api-limiter/settings' => trans('api-limiter::admin.route_descriptions.admin_api_limiter_settings'),
            'admin/api-limiter/api-routes' => trans('api-limiter::admin.route_descriptions.admin_api_limiter_routes'),
            'admin/apiextender' => trans('api-limiter::admin.route_descriptions.admin_apiextender'),
            'admin/skin-api' => trans('api-limiter::admin.route_descriptions.admin_skin_api'),
        ];
        
        // Look for exact match
        if (isset($descriptions[$uri])) {
            return $descriptions[$uri];
        }
        
        // Look for prefix match
        foreach ($descriptions as $pattern => $desc) {
            if (str_starts_with($uri, $pattern)) {
                return $desc;
            }
        }
        
        // Automatic description based on URI
        if (str_starts_with($uri, 'admin/')) {
            // Administrative routes
            if (str_contains($uri, '/auth/')) {
                return 'Authentication settings (admin panel)';
            } elseif (str_contains($uri, '/skin')) {
                return 'Skins and capes settings (admin panel)';
            } elseif (str_contains($uri, '/api')) {
                return 'API settings (admin panel)';
            } else {
                return 'Administrative panel - ' . $source['display'];
            }
        } else {
            // Public API routes
        if (str_contains($uri, '/auth/')) {
                return 'Authentication and authorization';
        } elseif (str_contains($uri, '/skin')) {
                return 'Skins and capes management';
        } elseif (str_contains($uri, '/avatar')) {
                return 'Avatar generation';
        } elseif (str_contains($uri, '/money')) {
                return 'User currency management';
        } elseif (str_contains($uri, '/user')) {
                return 'User management';
        } elseif (str_contains($uri, '/server')) {
                return 'Server management';
            }
        }
        
        return $source['display'] . ' API';
    }

    /**
     * Get rule display information for a route.
     *
     * @param array $ruleData
     * @param array $settings
     * @return array
     */
    private function getRuleDisplayInfo($ruleData, $settings)
    {
        $ruleType = $ruleData['type'] ?? 'rate_limit';
        
        switch ($ruleType) {
            case 'no_restrictions':
                return [
                    'icon' => '✅',
                    'text' => trans('api-limiter::admin.rule_display.no_restrictions'),
                    'class' => 'text-success',
                    'type' => 'no_restrictions'
                ];
                
            case 'rate_limit':
                return [
                    'icon' => '🚦',
                    'text' => trans('api-limiter::admin.rule_display.rate_limiting'),
                    'class' => 'text-warning',
                    'type' => 'rate_limit'
                ];
                
            case 'rate_limit_custom':
                return [
                    'icon' => '🚦',
                    'text' => trans('api-limiter::admin.rule_display.rl_custom'),
                    'class' => 'text-warning',
                    'type' => 'rate_limit_custom'
                ];
                
            case 'whitelist_only':
                return [
                    'icon' => '🔒',
                    'text' => trans('api-limiter::admin.rule_display.whitelist_only'),
                    'class' => 'text-info',
                    'type' => 'whitelist_only'
                ];
                
            case 'whitelist_custom':
                return [
                    'icon' => '🔒',
                    'text' => trans('api-limiter::admin.rule_display.whitelist_custom'),
                    'class' => 'text-info',
                    'type' => 'whitelist_custom'
                ];
                
            case 'rate_limit_whitelist':
                return [
                    'icon' => '🚦🔒',
                    'text' => trans('api-limiter::admin.rule_display.rl_plus_w'),
                    'class' => 'text-primary',
                    'type' => 'rate_limit_whitelist'
                ];
                
            case 'rate_limit_whitelist_custom':
                return [
                    'icon' => '🚦🔒',
                    'text' => trans('api-limiter::admin.rule_display.rl_plus_w_custom'),
                    'class' => 'text-primary',
                    'type' => 'rate_limit_whitelist_custom'
                ];
                
            case 'whitelist_rate_limit_custom':
                return [
                    'icon' => '🔒🚦',
                    'text' => trans('api-limiter::admin.rule_display.w_plus_rl_custom'),
                    'class' => 'text-info',
                    'type' => 'whitelist_rate_limit_custom'
                ];
                
            case 'restricted':
                return [
                    'icon' => '🚫',
                    'text' => trans('api-limiter::admin.rule_display.restricted'),
                    'class' => 'text-danger',
                    'type' => 'restricted'
                ];
                
            default:
                return [
                    'icon' => '📋',
                    'text' => trans('api-limiter::admin.rule_display.default'),
                    'class' => 'text-muted',
                    'type' => 'default'
                ];
        }
    }
} 