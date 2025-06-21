<?php

namespace Azuriom\Plugin\ApiLimiter\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Plugin\ApiLimiter\Middleware\ApiLimiter;
use Azuriom\Plugin\ApiLimiter\Models\LimiterSetting;
use Illuminate\Support\Facades\Route;

class ApiLimiterServiceProvider extends BasePluginServiceProvider
{
    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        // Register rate limiter override in register method (executes earlier)
        $this->overrideBuiltinApiThrottling();
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        $this->loadViews();

        $this->loadTranslations();

        // $this->loadMigrations();

        $this->configureLogging();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        Permission::registerPermissions([
            'api-limiter.manage' => 'api-limiter::admin.permissions.manage',
        ]);

        // Initialize plugin settings with default values
        $this->initializePluginSettings();
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     */
    protected function routeDescriptions(): array
    {
        return [
            // Empty array since we have no public routes
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     */
    protected function adminNavigation(): array
    {
        return [
            'api-limiter' => [
                'name' => trans('api-limiter::admin.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-shield-check',
                'route' => 'api-limiter.admin.*',
                'permission' => 'api-limiter.manage',
                'items' => [
                    'api-limiter.admin.settings' => [
                        'name' => trans('api-limiter::admin.nav.settings'),
                        'permission' => 'api-limiter.manage',
                    ],
                    'api-limiter.admin.api-routes' => [
                        'name' => trans('api-limiter::admin.nav.api_routes'),
                        'permission' => 'api-limiter.manage',
                    ],
                    'api-limiter.admin.logs' => [
                        'name' => trans('api-limiter::admin.nav.logs'),
                        'permission' => 'api-limiter.manage',
                    ],
                ],
            ],
        ];
    }

    /**
     * Override built-in API throttling with our custom rate limiter.
     * SAFE VERSION: with protection against failures during Azuriom updates
     */
    protected function overrideBuiltinApiThrottling(): void
    {
        // Override middleware groups via reflection
        $this->app->booted(function () {
            try {
                // Check compatibility with current Azuriom version
                if (!$this->isCompatibleWithCurrentVersion()) {
                    \Log::warning('ApiLimiter: Incompatible Azuriom version detected, using fallback mode');
                    $this->useFallbackMode();
                    return;
                }

                $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
                
                // Additional kernel structure validation
                if (!$this->validateKernelStructure($kernel)) {
                    \Log::warning('ApiLimiter: Kernel structure changed, using fallback mode');
                    $this->useFallbackMode();
                    return;
                }
                
                $reflection = new \ReflectionClass($kernel);
                
                // Get protected middlewareGroups property
                $property = $reflection->getProperty('middlewareGroups');
                $property->setAccessible(true);
                $middlewareGroups = $property->getValue($kernel);
                
                // Check that api group exists
                if (!isset($middlewareGroups['api'])) {
                    \Log::warning('ApiLimiter: API middleware group not found, using fallback mode');
                    $this->useFallbackMode();
                    return;
                }
                
                // Save original group for restoration
                $originalApiGroup = $middlewareGroups['api'];
                
                // Replace middleware in api group
                $middlewareGroups['api'] = [
                    ApiLimiter::class,
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                ];
                
                // Set updated middleware groups
                $property->setValue($kernel, $middlewareGroups);
                
                // Also update router
                $router = $this->app['router'];
                $router->middlewareGroup('api', $middlewareGroups['api']);
                
            } catch (\Exception $e) {
                // Log error and use safe fallback
                \Log::error('ApiLimiter: Failed to override API middleware, using fallback mode', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->useFallbackMode();
            }
            
            try {
                // Override rate limiter - disable built-in
                \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
                    // Return null to completely disable built-in rate limiter
                    // Our middleware in api group will handle all rate limiting
                    return null;
                });
            } catch (\Exception $e) {
                \Log::error('ApiLimiter: Failed to override RateLimiter', [
                    'error' => $e->getMessage()
                ]);
            }
        });
        
        try {
            // Create alias
            $this->app['router']->aliasMiddleware('api.rate.limit', ApiLimiter::class);
        } catch (\Exception $e) {
            \Log::error('ApiLimiter: Failed to create middleware alias', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check compatibility with current Azuriom version
     */
    protected function isCompatibleWithCurrentVersion(): bool
    {
        try {
            // Check existence of core Azuriom classes
            if (!class_exists(\Azuriom\Azuriom::class)) {
                return false;
            }
            
            // Check Azuriom version
            $version = \Azuriom\Azuriom::version();
            
            // Support versions 1.0.0 and above
            return version_compare($version, '1.0.0', '>=');
            
        } catch (\Exception $e) {
            \Log::error('ApiLimiter: Version check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate Kernel structure for compatibility
     */
    protected function validateKernelStructure($kernel): bool
    {
        try {
            $reflection = new \ReflectionClass($kernel);
            
            // Check that middlewareGroups property exists
            if (!$reflection->hasProperty('middlewareGroups')) {
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Safe fallback mode - only add middleware without overriding
     */
    protected function useFallbackMode(): void
    {
        try {
            $router = $this->app['router'];
            
            // Simply add our middleware to api group
            $router->pushMiddlewareToGroup('api', ApiLimiter::class);
            
            \Log::debug('ApiLimiter: Using fallback mode - middleware added to api group');
            
        } catch (\Exception $e) {
            \Log::error('ApiLimiter: Even fallback mode failed', [
                'error' => $e->getMessage()
            ]);
            
            // Last resort - completely disable plugin
            $this->disablePlugin();
        }
    }

    /**
     * Disable plugin in case of critical error
     */
    protected function disablePlugin(): void
    {
        try {
            \Log::critical('ApiLimiter: Plugin disabled due to compatibility issues');
            
            // Set disable flag in settings
            LimiterSetting::setValue('enabled', false);
            LimiterSetting::setValue('plugin_disabled_due_to_error', true);
            
        } catch (\Exception $e) {
            // If even this doesn't work, just log
            \Log::emergency('ApiLimiter: Complete plugin failure', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Configure separate logging channel for API Limiter with milliseconds.
     */
    protected function configureLogging(): void
    {
        config([
            'logging.channels.api-limiter' => [
                'driver' => 'daily',
                'path' => storage_path('logs/api-limiter.log'),
                'level' => 'info',
                'days' => 14,
                'replace_placeholders' => true,
                'tap' => [\Azuriom\Plugin\ApiLimiter\Logging\MillisecondsFormatter::class],
            ]
        ]);
    }

    /**
     * Initialize plugin settings with default values.
     */
    protected function initializePluginSettings(): void
    {
        // Run in booted callback to ensure database is ready
        $this->app->booted(function () {
            try {
                LimiterSetting::initializeSettings();
            } catch (\Exception $e) {
                // Log error but don't break the application
                \Log::error('ApiLimiter: Failed to initialize settings', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });
    }
} 