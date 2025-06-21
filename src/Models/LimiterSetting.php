<?php

namespace Azuriom\Plugin\ApiLimiter\Models;

use Azuriom\Models\Setting;

class LimiterSetting
{
    /**
     * Get a setting value with a default fallback
     */
    public static function getValue(string $key, $default = null)
    {
        $fullKey = 'api-limiter.' . $key;
        
        // Check if setting exists, if not create it with default value
        $exists = Setting::where('name', $fullKey)->exists();
        if (!$exists && $default !== null) {
            self::setValue($key, $default);
        }
        
        // Use Azuriom's Setting model to get the value
        $value = setting($fullKey, $default);
        
        // Convert boolean-like strings to actual booleans for specific keys
        if (in_array($key, ['enabled', 'whitelist_mode']) && is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        return $value;
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $key, $value): void
    {
        $fullKey = 'api-limiter.' . $key;
        
        // Use Azuriom's Setting model to set the value
        Setting::updateSettings([$fullKey => $value]);
    }

    /**
     * Get multiple setting values
     */
    public static function getValues(array $keys, array $defaults = []): array
    {
        $values = [];
        
        foreach ($keys as $key) {
            $default = $defaults[$key] ?? null;
            $values[$key] = self::getValue($key, $default);
        }
        
        return $values;
    }

    /**
     * Set multiple setting values
     */
    public static function setValues(array $values): void
    {
        $settings = [];
        
        foreach ($values as $key => $value) {
            $fullKey = 'api-limiter.' . $key;
            
            // Convert boolean values to strings for database storage
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            
            $settings[$fullKey] = $value;
        }
        
        Setting::updateSettings($settings);
    }

    /**
     * Get all rate limiter settings with defaults
     */
    public static function getAllSettings(): array
    {
        // Initialize missing settings first
        self::initializeSettings();
        
        $defaults = self::getDefaultSettings();
        $keys = array_keys($defaults);
        
        return self::getValues($keys, $defaults);
    }

    /**
     * Get route-specific rules.
     */
    public static function getRouteRules(): array
    {
        $rules = self::getValue('route_rules', '{}');
        return is_string($rules) ? json_decode($rules, true) ?: [] : $rules;
    }

    /**
     * Set route-specific rules.
     */
    public static function setRouteRules(array $rules): void
    {
        self::setValue('route_rules', json_encode($rules));
    }

    /**
     * Get rule for a specific route.
     * Returns: array with rule data or string for simple rules
     */
    public static function getRuleForRoute(string $routePath, ?string $routeName = null)
    {
        $rules = self::getRouteRules();
        

        
        // Check by exact route name first
        if ($routeName && isset($rules[$routeName])) {
            return $rules[$routeName];
        }
        
        // Check exact path match
        if (isset($rules[$routePath])) {
            return $rules[$routePath];
        }
        
        // Check by route path patterns
        foreach ($rules as $pattern => $rule) {
            // Check wildcard patterns
            if (fnmatch($pattern, $routePath)) {
                return $rule;
            }
            
            // Check prefix matching for patterns ending with *
            if (str_ends_with($pattern, '*') && str_starts_with($routePath, rtrim($pattern, '*'))) {
                return $rule;
            }
        }
        
        // Return default rule
        return self::getValue('default_rule', 'rate_limit');
    }

    /**
     * Get rule type for a specific route.
     */
    public static function getRuleTypeForRoute(string $routePath, ?string $routeName = null): string
    {
        $rule = self::getRuleForRoute($routePath, $routeName);
        
        if (is_array($rule)) {
            return $rule['type'] ?? 'rate_limit';
        }
        
        return $rule;
    }

    /**
     * Get rule parameters for a specific route.
     */
    public static function getRuleParamsForRoute(string $routePath, ?string $routeName = null): array
    {
        $rule = self::getRuleForRoute($routePath, $routeName);
        
        if (is_array($rule)) {
            return $rule;
        }
        
        return ['type' => $rule];
    }

    /**
     * Check if rate limiting is enabled.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return (bool) self::getValue('enabled', true);
    }

    /**
     * Get requests per minute limit.
     *
     * @return int
     */
    public static function getRequestsPerMinute(): int
    {
        return (int) self::getValue('max_attempts', 60);
    }

    /**
     * Get whitelist IPs.
     *
     * @return array
     */
    public static function getWhitelistIps(): array
    {
        $ips = self::getValue('whitelist_ips', '127.0.0.1,::1');
        return array_filter(array_map('trim', explode(',', $ips)));
    }

    /**
     * Get limit by setting (ip or user).
     *
     * @return string
     */
    public static function getLimitBy(): string
    {
        return self::getValue('limit_by', 'ip');
    }

    /**
     * Update multiple settings at once.
     *
     * @param array $newSettings
     * @return void
     */
    public static function updateSettings(array $newSettings): void
    {
        $prefixedSettings = [];
        foreach ($newSettings as $key => $value) {
            $prefixedSettings['api-limiter.' . $key] = $value;
        }
        
        Setting::updateSettings($prefixedSettings);
    }

    /**
     * Initialize all plugin settings with default values if they don't exist.
     *
     * @return void
     */
    public static function initializeSettings(): void
    {
        $defaultSettings = [
            'enabled' => true,
            'max_attempts' => 60,
            'per_minutes' => 1,
            'limit_by' => 'ip',
            'whitelist_ips' => '127.0.0.1,::1',
            'whitelist_mode' => false,
            'route_selection_mode' => 'all',
            'selected_routes' => '',
            'route_rules' => '{"api-limiter.api.":"restricted"}',
            'default_rule' => 'rate_limit',
            'logging_enabled' => true,
            'auto_cleanup_logs' => '1_week',
        ];

        $settingsToCreate = [];
        
        foreach ($defaultSettings as $key => $defaultValue) {
            $fullKey = 'api-limiter.' . $key;
            
            // Check if setting exists
            if (!Setting::where('name', $fullKey)->exists()) {
                $settingsToCreate[$fullKey] = $defaultValue;
            }
        }
        
        // Create missing settings in one batch
        if (!empty($settingsToCreate)) {
            Setting::updateSettings($settingsToCreate);
            
            \Log::channel('api-limiter')->debug('ApiLimiter: Initialized missing settings', [
                'created_settings' => array_keys($settingsToCreate),
                'count' => count($settingsToCreate)
            ]);
        }
    }

    /**
     * Get default settings values.
     *
     * @return array
     */
    public static function getDefaultSettings(): array
    {
        return [
            'enabled' => true,
            'max_attempts' => 60,
            'per_minutes' => 1,
            'limit_by' => 'ip',
            'whitelist_ips' => '127.0.0.1,::1',
            'whitelist_mode' => false,
            'route_selection_mode' => 'all',
            'selected_routes' => '',
            'route_rules' => '{}',
            'default_rule' => 'rate_limit',
            'logging_enabled' => true,
            'auto_cleanup_logs' => '1_week',
        ];
    }
} 