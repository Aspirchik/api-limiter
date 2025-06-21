<?php

namespace Azuriom\Plugin\ApiLimiter\Middleware;

use Azuriom\Plugin\ApiLimiter\Models\LimiterSetting;
use Illuminate\Http\Request;
use Closure;

class ApiLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        try {
            // Check if plugin is disabled due to errors
            $pluginDisabled = LimiterSetting::getValue('plugin_disabled_due_to_error', false);
            if ($pluginDisabled) {
                \Log::debug('ApiLimiter: Plugin is disabled due to compatibility issues, passing request through');
                return $next($request);
            }
            
            // Check if logging is enabled
            $loggingEnabled = LimiterSetting::getValue('logging_enabled', true);
            
            // Check if rate limiting is enabled
            $enabled = LimiterSetting::getValue('enabled', true);
            
        } catch (\Exception $e) {
            // If we can't get settings - pass request through without processing
            \Log::error('ApiLimiter: Failed to get settings, passing request through', [
                'error' => $e->getMessage()
            ]);
            return $next($request);
        }
        
        if (!$enabled) {
            if ($loggingEnabled) {
                \Log::channel('api-limiter')->info('API Request', [
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'route' => $request->route() ? $request->route()->getName() : 'unknown',
                    'uri' => $request->getPathInfo(),
                    'status' => 'allowed',
                    'reason' => 'limiter_disabled'
                ]);
            }
            return $next($request);
        }

        // Get rule for this specific route
        $currentRoute = $request->route() ? $request->route()->getName() : null;
        $currentPath = ltrim($request->getPathInfo(), '/');
        $ruleData = LimiterSetting::getRuleParamsForRoute($currentPath, $currentRoute);
        $ruleType = $ruleData['type'] ?? 'rate_limit';
        
        // If no restrictions, pass through immediately
        if ($ruleType === 'no_restrictions') {
            if ($loggingEnabled) {
                \Log::channel('api-limiter')->info('API Request', [
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'route' => $currentRoute ?: 'unknown',
                    'uri' => $request->getPathInfo(),
                    'status' => 'allowed',
                    'reason' => 'no_restrictions'
                ]);
            }
            return $next($request);
        }

        // Check if IP is whitelisted (general or custom)
        $isGeneralWhitelisted = $this->isWhitelisted($request);
        $isCustomWhitelisted = $this->isCustomWhitelisted($request, $ruleData);
        $isAnyWhitelisted = $isGeneralWhitelisted || $isCustomWhitelisted;
        
        // Handle different rule types
        $status = 'allowed';
        $reason = '';
        
        switch ($ruleType) {
            case 'restricted':
                $status = 'blocked';
                $reason = 'restricted';
                if ($loggingEnabled) {
                    \Log::channel('api-limiter')->info('API Request', [
                        'ip' => $request->ip(),
                        'method' => $request->method(),
                        'route' => $currentRoute ?: 'unknown',
                        'uri' => $request->getPathInfo(),
                        'status' => $status,
                        'reason' => $reason
                    ]);
                }
                abort(403, 'Access denied.');
                
            case 'whitelist_only':
                if (!$isGeneralWhitelisted) {
                    $status = 'blocked';
                    $reason = 'not_in_whitelist';
                    if ($loggingEnabled) {
                        \Log::channel('api-limiter')->info('API Request', [
                            'ip' => $request->ip(),
                            'method' => $request->method(),
                            'route' => $currentRoute ?: 'unknown',
                            'uri' => $request->getPathInfo(),
                            'status' => $status,
                            'reason' => $reason
                        ]);
                    }
                    abort(403, 'Access denied.');
                }
                $reason = 'whitelisted';
                if ($loggingEnabled) {
                    \Log::channel('api-limiter')->info('API Request', [
                        'ip' => $request->ip(),
                        'method' => $request->method(),
                        'route' => $currentRoute ?: 'unknown',
                        'uri' => $request->getPathInfo(),
                        'status' => $status,
                        'reason' => $reason
                    ]);
                }
                return $next($request);
                
            case 'whitelist_custom':
                if (!$isCustomWhitelisted) {
                    $status = 'blocked';
                    $reason = 'not_in_custom_whitelist';
                    if ($loggingEnabled) {
                        \Log::channel('api-limiter')->info('API Request', [
                            'ip' => $request->ip(),
                            'method' => $request->method(),
                            'route' => $currentRoute ?: 'unknown',
                            'uri' => $request->getPathInfo(),
                            'status' => $status,
                            'reason' => $reason
                        ]);
                    }
                    abort(403, 'Access denied.');
                }
                $reason = 'custom_whitelisted';
                if ($loggingEnabled) {
                    \Log::channel('api-limiter')->info('API Request', [
                        'ip' => $request->ip(),
                        'method' => $request->method(),
                        'route' => $currentRoute ?: 'unknown',
                        'uri' => $request->getPathInfo(),
                        'status' => $status,
                        'reason' => $reason
                    ]);
                }
                return $next($request);
                
            case 'rate_limit':
                // Standard rate limiting with general whitelist bypass
                if ($isGeneralWhitelisted) {
                    $reason = 'whitelisted';
                    if ($loggingEnabled) {
                        \Log::channel('api-limiter')->info('API Request', [
                            'ip' => $request->ip(),
                            'method' => $request->method(),
                            'route' => $currentRoute ?: 'unknown',
                            'uri' => $request->getPathInfo(),
                            'status' => $status,
                            'reason' => $reason
                        ]);
                    }
                    return $next($request);
                }
                $this->applyRateLimiting($request, 
                    LimiterSetting::getValue('max_attempts', 60),
                    LimiterSetting::getValue('per_minutes', 1),
                    $loggingEnabled,
                    $currentRoute,
                    'rate_limited'
                );
                break;
                
            case 'rate_limit_custom':
                // Custom rate limiting, no whitelist bypass
                $this->applyRateLimiting($request,
                    $ruleData['max_attempts'] ?? LimiterSetting::getValue('max_attempts', 60),
                    $ruleData['per_minutes'] ?? LimiterSetting::getValue('per_minutes', 1),
                    $loggingEnabled,
                    $currentRoute,
                    'rate_limited_custom'
                );
                break;
                
            case 'rate_limit_whitelist':
                // Standard rate limiting with general whitelist bypass
                if ($isGeneralWhitelisted) {
                    $reason = 'whitelisted';
                    if ($loggingEnabled) {
                        \Log::channel('api-limiter')->info('API Request', [
                            'ip' => $request->ip(),
                            'method' => $request->method(),
                            'route' => $currentRoute ?: 'unknown',
                            'uri' => $request->getPathInfo(),
                            'status' => $status,
                            'reason' => $reason
                        ]);
                    }
                    return $next($request);
                }
                $this->applyRateLimiting($request,
                    LimiterSetting::getValue('max_attempts', 60),
                    LimiterSetting::getValue('per_minutes', 1),
                    $loggingEnabled,
                    $currentRoute,
                    'rate_limited'
                );
                break;
                
            case 'rate_limit_whitelist_custom':
                // Custom rate limiting with custom whitelist bypass
                if ($isCustomWhitelisted) {
                    $reason = 'custom_whitelisted';
                    if ($loggingEnabled) {
                        \Log::channel('api-limiter')->info('API Request', [
                            'ip' => $request->ip(),
                            'method' => $request->method(),
                            'route' => $currentRoute ?: 'unknown',
                            'uri' => $request->getPathInfo(),
                            'status' => $status,
                            'reason' => $reason
                        ]);
                    }
                    return $next($request);
                }
                $this->applyRateLimiting($request,
                    $ruleData['max_attempts'] ?? LimiterSetting::getValue('max_attempts', 60),
                    $ruleData['per_minutes'] ?? LimiterSetting::getValue('per_minutes', 1),
                    $loggingEnabled,
                    $currentRoute,
                    'rate_limited_custom'
                );
                break;
                
            case 'whitelist_rate_limit_custom':
                // Only whitelisted IPs are allowed, but with rate limiting
                if (!$isCustomWhitelisted) {
                    if ($loggingEnabled) {
                        \Log::channel('api-limiter')->info('API Request', [
                            'ip' => $request->ip(),
                            'method' => $request->method(),
                            'route' => $currentRoute ?: 'unknown',
                            'uri' => $request->getPathInfo(),
                            'status' => 'blocked',
                            'reason' => 'not_in_custom_whitelist'
                        ]);
                    }
                    abort(403, 'Access Forbidden');
                }
                // Apply rate limiting to whitelisted IPs
                $this->applyRateLimiting($request,
                    $ruleData['max_attempts'] ?? LimiterSetting::getValue('max_attempts', 60),
                    $ruleData['per_minutes'] ?? LimiterSetting::getValue('per_minutes', 1),
                    $loggingEnabled,
                    $currentRoute,
                    'whitelisted_rate_limited'
                );
                break;
                
            default:
                $this->applyRateLimiting($request,
                    LimiterSetting::getValue('max_attempts', 60),
                    LimiterSetting::getValue('per_minutes', 1),
                    $loggingEnabled,
                    $currentRoute,
                    'rate_limited'
                );
        }
        
        if ($loggingEnabled) {
            \Log::channel('api-limiter')->info('API Request', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'route' => $currentRoute ?: 'unknown',
                'uri' => $request->getPathInfo(),
                'status' => 'allowed',
                'reason' => 'rate_limit_passed'
            ]);
        }
        return $next($request);
    }

    /**
     * Check if this route should be rate limited based on settings.
     */
    protected function shouldLimitRoute($request): bool
    {
        $mode = LimiterSetting::getValue('route_selection_mode', 'all');
        
        if ($mode === 'all') {
            return true; // Limit all API routes
        }
        
        if ($mode === 'selected') {
            $selectedRoutes = LimiterSetting::getValue('selected_routes', '');
            if (empty($selectedRoutes)) {
                return false; // If nothing selected, don't limit
            }
            
            $routesToLimit = array_map('trim', explode(',', $selectedRoutes));
            $currentRoute = $request->route() ? $request->route()->getName() : null;
            $currentPath = ltrim($request->getPathInfo(), '/');
            
            
            foreach ($routesToLimit as $routePattern) {
                if (empty($routePattern)) {
                    continue;
                }
                
                // Check by route name
                if ($currentRoute && ($currentRoute === $routePattern || fnmatch($routePattern, $currentRoute))) {
                    return true;
                }
                
                // Check by path
                if (fnmatch($routePattern, $currentPath)) {
                    return true;
                }
                
                // Check path prefix
                if (str_starts_with($currentPath, $routePattern)) {
                    return true;
                }
            }
            
            return false;
        }
        
        return true; // Limit by default
    }

    /**
     * Apply rate limiting logic.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $maxAttempts
     * @param int $decayMinutes
     */
    protected function applyRateLimiting(Request $request, int $maxAttempts, int $decayMinutes, bool $loggingEnabled = true, ?string $currentRoute = null, string $reason = 'rate_limited'): void
    {
        $key = $this->resolveRequestSignature($request);
        $cache = app('cache');
        $currentAttempts = $cache->get($key, 0);
        
        if ($currentAttempts >= $maxAttempts) {
            if ($loggingEnabled) {
                \Log::channel('api-limiter')->info('API Request', [
                    'ip' => $request->ip(),
                    'method' => $request->method(),
                    'route' => $currentRoute ?: 'unknown',
                    'uri' => $request->getPathInfo(),
                    'status' => 'blocked',
                    'reason' => 'rate_limit_exceeded'
                ]);
            }
            abort(429, 'Too Many Requests');
        }
        
        // Increment attempt counter
        $cache->put($key, $currentAttempts + 1, $decayMinutes * 60);
    }

    /**
     * Check if the current IP is whitelisted (general whitelist).
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function isWhitelisted(Request $request): bool
    {
        $whitelistIps = LimiterSetting::getValue('whitelist_ips', '');
        return $this->checkIpInList($request->ip(), $whitelistIps);
    }

    /**
     * Check if the current IP is in custom whitelist for specific rule.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $ruleData
     * @return bool
     */
    protected function isCustomWhitelisted(Request $request, array $ruleData): bool
    {
        $customWhitelistIps = $ruleData['whitelist_ips'] ?? '';
        return $this->checkIpInList($request->ip(), $customWhitelistIps);
    }

    /**
     * Check if IP is in the given whitelist string.
     *
     * @param string $clientIp
     * @param string $whitelistIps
     * @return bool
     */
    protected function checkIpInList(string $clientIp, string $whitelistIps): bool
    {
        if (empty($whitelistIps)) {
            return false;
        }

        $whitelist = array_filter(array_map('trim', explode(',', $whitelistIps)));

        foreach ($whitelist as $ip) {
            if ($this->ipMatches($clientIp, $ip)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if client IP matches whitelist entry (supports CIDR notation).
     *
     * @param string $clientIp
     * @param string $whitelistEntry
     * @return bool
     */
    protected function ipMatches(string $clientIp, string $whitelistEntry): bool
    {
        // Exact match
        if ($clientIp === $whitelistEntry) {
            return true;
        }

        // CIDR notation check
        if (strpos($whitelistEntry, '/') !== false) {
            list($subnet, $mask) = explode('/', $whitelistEntry);
            
            // IPv4 CIDR
            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $clientLong = ip2long($clientIp);
                $subnetLong = ip2long($subnet);
                $maskLong = -1 << (32 - (int)$mask);
                
                return ($clientLong & $maskLong) === ($subnetLong & $maskLong);
            }
            
            // IPv6 CIDR (basic implementation)
            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                // For IPv6, we'll use a simpler prefix match for now
                $prefixLength = (int)$mask;
                $clientBinary = inet_pton($clientIp);
                $subnetBinary = inet_pton($subnet);
                
                if ($clientBinary && $subnetBinary) {
                    $bytesToCheck = intval($prefixLength / 8);
                    $bitsToCheck = $prefixLength % 8;
                    
                    // Check full bytes
                    if (substr($clientBinary, 0, $bytesToCheck) !== substr($subnetBinary, 0, $bytesToCheck)) {
                        return false;
                    }
                    
                    // Check remaining bits
                    if ($bitsToCheck > 0 && $bytesToCheck < strlen($clientBinary)) {
                        $clientByte = ord($clientBinary[$bytesToCheck]);
                        $subnetByte = ord($subnetBinary[$bytesToCheck]);
                        $mask = 0xFF << (8 - $bitsToCheck);
                        
                        return ($clientByte & $mask) === ($subnetByte & $mask);
                    }
                    
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolve the request signature for rate limiting.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        $limitBy = LimiterSetting::getValue('limit_by', 'ip');
        
        if ($limitBy === 'user' && auth()->check()) {
            return 'azuriom_api_rl_user:' . auth()->id();
        }
        
        return 'azuriom_api_rl_ip:' . $request->ip();
    }
} 