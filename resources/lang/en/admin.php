<?php

return [
    // Page titles
    'title' => 'API Limiter',
    'settings' => 'Settings',
    'api_routes' => 'API Routes & Admin Discovery',
    'route_rules' => 'Route-specific Rules',
    
    // Permissions
    'permissions' => [
        'manage' => 'Manage API Limiter',
    ],
    
    // Navigation
    'nav' => [
        'settings' => 'Settings',
        'api_routes' => 'API Routes',
        'route_rules' => 'Route Rules',
        'logs' => 'Logs',
        'back_to_settings' => 'Back to Settings',
    ],
    
    // General settings
    'general' => [
        'title' => 'General Settings',
        'enabled' => 'Enable API Limiter',
        'enabled_help' => 'Enable/disable API rate limiting globally',
        'max_attempts' => 'Max Attempts',
        'max_attempts_help' => 'Maximum number of requests allowed',
        'per_minutes' => 'Per Minutes',
        'per_minutes_help' => 'Time period in minutes',
        'limit_by' => 'Limit By',
        'limit_by_help' => 'Limit requests by IP address or authenticated user',
        'limit_by_ip' => 'IP Address',
        'limit_by_user' => 'User',
        'whitelist_ips' => 'Whitelist IPs',
        'whitelist_ips_help' => 'IP addresses that bypass rate limiting (comma-separated)',
        'default_rule' => 'Default Rule',
        'default_rule_help' => 'Default rule applied to all API routes',
    ],
    
    // Route rules
    'rules' => [
        'title' => 'Route-specific Rules',
        'description' => 'Configure custom rules for specific API routes',
        'add_rule' => 'Add Rule',
        'remove_rule' => 'Remove Rule',
        'route' => 'Route',
        'rule_type' => 'Rule Type',
        'custom_settings' => 'Custom Settings',
        'select_route' => 'Select API Route',
        'no_routes' => 'No API routes found',
        'api_route' => 'API Route',
        'rule' => 'Rule',
        'max_attempts' => 'Max Attempts',
        'per_minutes' => 'Per Minutes',
        'whitelist_ips' => 'Whitelist IP addresses',
        'no_restrictions' => '✅ No Restrictions',
        'rate_limit' => '🚦 Rate Limiting',
        'rate_limit_custom' => '🚦 Rate Limiting (Custom)',
        'whitelist_only' => '🔒 Whitelist Only',
        'whitelist_custom' => '🔒 Whitelist (Custom)',
        'rate_limit_whitelist' => '🚦🔒 Rate Limit + Whitelist',
        'rate_limit_whitelist_custom' => '🚦🔒 Rate Limit + Whitelist (Custom)',
        'restricted' => '🚫 Restricted',
    ],
    
    // Rule types
    'rule_types' => [
        'no_restrictions' => 'No Restrictions',
        'rate_limit' => 'Rate Limiting',
        'rate_limit_custom' => 'Rate Limiting (Custom)',
        'whitelist_only' => 'Whitelist Only',
        'whitelist_custom' => 'Whitelist (Custom)',
        'rate_limit_and_whitelist' => 'Rate Limit + Whitelist',
        'rate_limit_and_whitelist_custom' => 'Rate Limit + Whitelist (Custom)',
        'restricted' => 'Restricted',
    ],
    
    // API Routes Discovery
    'discovery' => [
        'title' => 'API Routes & Admin Discovery',
        'description' => 'Automatic discovery of all API routes and related administrative routes in Azuriom system and installed plugins.',
        'api_routes' => 'API Routes',
        'admin_routes' => 'Admin Routes',
        'protection_coverage' => 'Protection Coverage',
        'source_filter' => 'Filter by Source',
        'method_filter' => 'Filter by Method',
        'search_filter' => 'Search by URI',
        'all_sources' => 'All Sources',
        'all_methods' => 'All Methods',
        'search_placeholder' => 'Enter part of URI...',
    ],
    
    // Table headers
    'table' => [
        'methods' => 'Methods',
        'uri' => 'URI',
        'source' => 'Source',
        'description' => 'Description',
        'middleware' => 'Middleware',
        'rule' => 'Rule',
        'status' => 'Status',
        'routes' => 'routes',
    ],
    
    // Statuses
    'status' => [
        'protected' => 'Protected',
        'not_protected' => 'Not Protected',
        'admin' => 'Admin',
        'public' => 'Public',
        'kernel' => 'Kernel',
        'api_limiter' => 'API Limiter',
        'default' => 'Default',
    ],
    
    // Rule display
    'rule_display' => [
        'no_restrictions' => 'No Restrictions',
        'rate_limiting' => 'Rate Limiting',
        'rl_custom' => 'RL Custom',
        'whitelist_only' => 'Whitelist Only',
        'whitelist_custom' => 'Whitelist Custom',
        'rl_plus_w' => 'RL + W',
        'rl_plus_w_custom' => 'RL + W Custom',
        'restricted' => 'Restricted',
        'default' => 'Default',
    ],
    
    // Messages
    'messages' => [
        'settings_saved' => 'Settings and rules saved successfully!',
        'cache_cleared' => 'API Limiter cache cleared!',
        'error' => 'An error occurred while saving settings.',
        'no_changes' => 'No changes were made.',
    ],
    
    // Compatibility messages
    'compatibility_error' => 'Warning: API Limiter plugin is disabled due to incompatibility with current Azuriom version. Please contact plugin developer.',
    'compatibility_warning' => 'Plugin is running in compatibility mode with limited functionality.',
    
    // Buttons
    'buttons' => [
        'save' => 'Save Settings',
        'clear_cache' => 'Clear Cache',
        'refresh' => 'Refresh Data',
        'add' => 'Add',
        'remove' => 'Remove',
        'cancel' => 'Cancel',
    ],
    
    // Help text
    'help' => [
        'rate_limiting' => 'Limits the number of requests per time period',
        'whitelist' => 'Only allows requests from whitelisted IP addresses',
        'restricted' => 'Blocks all requests to this route',
        'no_restrictions' => 'Allows unlimited requests',
        'custom' => 'Uses custom parameters instead of global settings',
    ],
    
    // Custom rule fields
    'custom_fields' => [
        'requests_count' => 'Requests Count',
        'period_minutes' => 'Period (minutes)',
        'whitelist_ip' => 'Whitelist IP',
        'add_empty_rule' => 'Add Empty Rule',
        'select_api_route' => 'Select API Route',
        'examples' => 'Route Examples',
        'route_names' => 'Route Names',
        'wildcard_paths' => 'Wildcard Paths',
        'exact_paths' => 'Exact Paths',
        'rule_descriptions' => 'Rule Type Descriptions',
        'back_to_plugins' => 'Back to Plugins',
        'save_settings' => 'Save Settings',
    ],
    
    // Rule descriptions
    'rule_descriptions' => [
        'no_restrictions' => 'No restrictions. Full access for all IP addresses.',
        'rate_limiting' => 'Rate limiting from general settings. IPs from global whitelist pass without restrictions.',
        'rate_limiting_custom' => 'Individual limits for this route.',
        'whitelist_only' => 'Access only for IPs from global whitelist. Others get 403.',
        'whitelist_custom' => 'Access only for IPs from individual whitelist of this route.',
        'rate_limiting_whitelist' => 'General limits + global whitelist passes without restrictions.',
        'rate_limiting_whitelist_custom' => 'Individual limits + individual whitelist passes without restrictions.',
        'restricted' => 'Complete access ban. All requests get 403.',
    ],
    
    // Coverage information
    'coverage' => [
        'title' => 'API Limiter Coverage Information',
        'total_api_routes' => 'Total API Routes',
        'admin_routes' => 'Admin Routes',
        'protected_routes' => 'Protected by API Limiter',
        'protection_coverage' => 'Protection Coverage',
        'status_explanation' => 'Status Explanation',
        'al_active' => 'API Limiter is active for this route (via api group or explicitly)',
        'al_inactive' => 'API Limiter is disabled or rule is "no_restrictions"',
        'public_route' => 'Public API route (accessible to all users)',
        'admin_route' => 'Administrative route (requires admin.access permission)',
        'important' => 'Important',
        'api_group_info' => 'All routes with api group automatically get API Limiter protection if plugin is enabled and route does not have "no_restrictions" rule.',
        'admin_routes' => 'Admin Routes',
        'admin_middleware_info' => 'Use admin-access middleware group = web + auth + can:admin.access + 2FA check',
    ],
    
    // Tooltips
    'tooltips' => [
        'admin_protected' => 'Administrative route protected by Kernel middleware',
        'api_limiter_active' => 'API Limiter is active',
        'api_limiter_inactive' => 'API Limiter is NOT applied',
        'middleware_count' => 'Middleware count',
    ],
    
    // Logs page
    'logs' => [
        'title' => 'API Limiter Logs',
        'description' => 'View API request logs processed by the API Limiter plugin.',
        'no_logs' => 'No logs found',
        'no_logs_filtered' => 'Try adjusting your search filters.',
        'no_logs_empty' => 'API request logs will appear here after system activity.',
        'level' => 'Level',
        'date' => 'Date',
        'search' => 'Search',
        'search_placeholder' => 'Search in messages...',
        'filter' => 'Filter',
        'reset' => 'Reset',
        'download' => 'Download',
        'clear' => 'Clear',
        'clear_confirm' => 'Are you sure you want to clear all logs?',
        'datetime' => 'Date/Time',
        'message' => 'Message',
        'context' => 'Context',
        'show_context' => 'Show Context',
        'found_records' => 'Found records',
        'page_of' => 'page :current of :total',
        'previous' => 'Previous',
        'next' => 'Next',
        'all_levels' => 'All levels',
        'all_routes' => 'All routes',
        'all_statuses' => 'All statuses',
        'route_filter' => 'Filter by route',
        'status_filter' => 'Filter by status',
        'allowed' => 'Allowed',
        'blocked' => 'Blocked',
        'logs_cleared' => 'API Limiter logs cleared!',
        'log_file_not_found' => 'Log file not found.',
        'reason' => 'Reason',
        'datetime' => 'Date/Time',
        'logging_settings' => 'Logging settings',
        'logging_enabled' => 'Logging enabled',
        'auto_cleanup' => 'Auto cleanup logs',
        'cleanup_periods' => [
            '15_min' => '15 minutes',
            '30_min' => '30 minutes', 
            '1_hour' => '1 hour',
            '3_hours' => '3 hours',
            '6_hours' => '6 hours',
            '12_hours' => '12 hours',
            '1_day' => '1 day',
            '3_days' => '3 days',
            '1_week' => '1 week',
            '2_weeks' => '2 weeks',
            '1_month' => '1 month',
            '3_months' => '3 months',
            '6_months' => '6 months',
            '1_year' => '1 year',
        ],
    ],
    
    // Source types
    'source_types' => [
        'core' => 'Azuriom Core',
        'plugin' => 'Plugin: :name',
        'unknown' => 'Unknown',
    ],
    
    // Route descriptions
    'route_descriptions' => [
        'auth_authenticate' => 'User authentication (token retrieval)',
        'auth_verify' => 'User token verification',
        'auth_logout' => 'System logout',
        'azlink' => 'AzLink server integration',
        'skin_api_skins' => 'API for skin management',
        'skin_api_capes' => 'API for cape management',
        'skin_api_avatars' => 'Avatar generation',
        'apiextender' => 'Extended API functionality',
        'posts' => 'API for working with posts',
        'servers' => 'Server information',
        'rss' => 'RSS feed',
        'atom' => 'Atom feed',
        'admin_api_limiter_settings' => 'API Limiter settings (admin panel)',
        'admin_api_limiter_routes' => 'API route discovery (admin panel)',
        'admin_apiextender' => 'API Extender settings (admin panel)',
        'admin_skin_api' => 'Skin API settings (admin panel)',
    ],
]; 