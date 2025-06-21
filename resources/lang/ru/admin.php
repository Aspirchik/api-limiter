<?php

return [
    // Page titles
    'title' => 'API Limiter',
    'settings' => 'Настройки',
    'api_routes' => 'API Routes & Admin Discovery',
    'route_rules' => 'Специальные правила для роутов',
    
    // Permissions
    'permissions' => [
        'manage' => 'Управление API Limiter',
    ],
    
    // Navigation
    'nav' => [
        'settings' => 'Настройки',
        'api_routes' => 'API роуты',
        'route_rules' => 'Правила роутов',
        'logs' => 'Логи',
        'back_to_settings' => 'Назад к настройкам',
    ],
    
    // General settings
    'general' => [
        'title' => 'Основные настройки',
        'enabled' => 'Включить API Limiter',
        'enabled_help' => 'Включить/отключить ограничение частоты API запросов глобально',
        'max_attempts' => 'Максимум попыток',
        'max_attempts_help' => 'Максимальное количество разрешенных запросов',
        'per_minutes' => 'За минут',
        'per_minutes_help' => 'Временной период в минутах',
        'limit_by' => 'Ограничивать по',
        'limit_by_help' => 'Ограничивать запросы по IP-адресу или аутентифицированному пользователю',
        'limit_by_ip' => 'IP-адресу',
        'limit_by_user' => 'Пользователю',
        'whitelist_ips' => 'Белый список IP',
        'whitelist_ips_help' => 'IP-адреса, которые обходят ограничения (через запятую)',
        'default_rule' => 'Правило по умолчанию',
        'default_rule_help' => 'Правило по умолчанию, применяемое ко всем API роутам',
    ],
    
    // Route rules
    'rules' => [
        'title' => 'Специальные правила для роутов',
        'description' => 'Настройте пользовательские правила для конкретных API роутов',
        'add_rule' => 'Добавить правило',
        'remove_rule' => 'Удалить правило',
        'route' => 'Роут',
        'rule_type' => 'Тип правила',
        'custom_settings' => 'Пользовательские настройки',
        'select_route' => 'Выбрать API роут',
        'no_routes' => 'API роутов не найдены',
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
        'title' => 'Обнаружение API и Admin роутов',
        'description' => 'Автоматическое обнаружение всех API маршрутов и связанных административных роутов в системе Azuriom и установленных плагинах.',
        'api_routes' => 'API роутов',
        'admin_routes' => 'Admin роутов',
        'protection_coverage' => 'Покрытие защитой',
        'source_filter' => 'Фильтр по источнику',
        'method_filter' => 'Фильтр по методу',
        'search_filter' => 'Поиск по URI',
        'all_sources' => 'Все источники',
        'all_methods' => 'Все методы',
        'search_placeholder' => 'Введите часть URI...',
    ],
    
    // Table headers
    'table' => [
        'methods' => 'Методы',
        'uri' => 'URI',
        'source' => 'Источник',
        'description' => 'Описание',
        'middleware' => 'Middleware',
        'rule' => 'Правило',
        'status' => 'Статус',
        'routes' => 'роутов',
    ],
    
    // Statuses
    'status' => [
        'protected' => 'Защищен',
        'not_protected' => 'Не защищен',
        'admin' => 'Админ',
        'public' => 'Публичный',
        'kernel' => 'Kernel',
        'api_limiter' => 'API Limiter',
        'default' => 'По умолчанию',
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
        'settings_saved' => 'Настройки и правила успешно сохранены!',
        'error' => 'Произошла ошибка при сохранении настроек.',
        'no_changes' => 'Изменения не были внесены.',
    ],
    
    // Compatibility messages
    'compatibility_error' => 'Внимание: Плагин API Limiter отключен из-за несовместимости с текущей версией Azuriom. Обратитесь к разработчику плагина.',
    'compatibility_warning' => 'Плагин работает в режиме совместимости с ограниченной функциональностью.',
    
    // Buttons
    'buttons' => [
        'save' => 'Сохранить настройки',
        'clear_cache' => 'Очистить кеш',
        'refresh' => 'Обновить',
        'add' => 'Добавить',
        'remove' => 'Удалить',
        'cancel' => 'Отменить',
    ],
    
    // Help text
    'help' => [
        'rate_limiting' => 'Ограничивает количество запросов за период времени',
        'whitelist' => 'Разрешает запросы только с IP-адресов из белого списка',
        'restricted' => 'Блокирует все запросы к этому роуту',
        'no_restrictions' => 'Разрешает неограниченное количество запросов',
        'custom' => 'Использует пользовательские параметры вместо глобальных настроек',
    ],
    
    // Custom rule fields
    'custom_fields' => [
        'requests_count' => 'Количество запросов',
        'period_minutes' => 'Период (минуты)',
        'whitelist_ip' => 'Whitelist IP',
        'add_empty_rule' => 'Добавить пустое правило',
        'select_api_route' => 'Выбрать API роут',
        'examples' => 'Примеры роутов',
        'route_names' => 'Имена роутов',
        'wildcard_paths' => 'Пути с wildcards',
        'exact_paths' => 'Точные пути',
        'rule_descriptions' => 'Описание типов правил',
        'back_to_plugins' => 'Назад к плагинам',
        'save_settings' => 'Сохранить настройки',
    ],
    
    // Rule descriptions
    'rule_descriptions' => [
        'no_restrictions' => 'Никаких ограничений. Полный доступ для всех IP адресов.',
        'rate_limiting' => 'Ограничение из основных настроек. IP из общего whitelist проходят без ограничений.',
        'rate_limiting_custom' => 'Индивидуальные лимиты для этого роута.',
        'whitelist_only' => 'Доступ только для IP из общего whitelist. Остальные получают 403.',
        'whitelist_custom' => 'Доступ только для IP из индивидуального whitelist этого роута.',
        'rate_limiting_whitelist' => 'Общие лимиты + общий whitelist проходит без ограничений.',
        'rate_limiting_whitelist_custom' => 'Индивидуальные лимиты + индивидуальный whitelist проходит без ограничений.',
        'restricted' => 'Полный запрет доступа. Все запросы получают 403.',
    ],
    
    // Coverage information
    'coverage' => [
        'title' => 'Информация о покрытии API Limiter',
        'total_api_routes' => 'Всего API роутов',
        'admin_routes' => 'Админ роуты',
        'protected_routes' => 'Защищены API Limiter',
        'protection_coverage' => 'Покрытие защитой',
        'status_explanation' => 'Объяснение статусов',
        'al_active' => 'API Limiter активен для этого роута (через группу api или явно)',
        'al_inactive' => 'API Limiter отключен или правило "no_restrictions"',
        'public_route' => 'Публичный API роут (доступен всем пользователям)',
        'admin_route' => 'Административный роут (требует admin.access разрешения)',
        'important' => 'Важно',
        'api_group_info' => 'Все роуты с группой api автоматически получают защиту API Limiter, если плагин включен и для роута не установлено правило "no_restrictions".',
        'admin_routes' => 'Admin роуты',
        'admin_middleware_info' => 'Используют группу middleware admin-access = web + auth + can:admin.access + 2FA проверка',
    ],
    
    // Tooltips
    'tooltips' => [
        'admin_protected' => 'Административный роут защищен Kernel middleware',
        'api_limiter_active' => 'API Limiter активен',
        'api_limiter_inactive' => 'API Limiter НЕ применен',
        'middleware_count' => 'Количество middleware',
    ],
    
    // Logs page
    'logs' => [
        'title' => 'Логи API Limiter',
        'description' => 'Просмотр логов API запросов, обработанных плагином API Limiter.',
        'no_logs' => 'Логи не найдены',
        'no_logs_filtered' => 'Попробуйте изменить фильтры поиска.',
        'no_logs_empty' => 'Логи API запросов появятся здесь после активности в системе.',
        'level' => 'Уровень',
        'date' => 'Дата',
        'search' => 'Поиск',
        'search_placeholder' => 'Поиск в сообщениях...',
        'filter' => 'Фильтр',
        'reset' => 'Сброс',
        'download' => 'Скачать',
        'clear' => 'Очистить',
        'clear_confirm' => 'Вы уверены, что хотите очистить все логи?',
        'datetime' => 'Дата/Время',
        'message' => 'Сообщение',
        'context' => 'Контекст',
        'show_context' => 'Показать контекст',
        'found_records' => 'Найдено записей',
        'page_of' => 'страница :current из :total',
        'previous' => 'Предыдущая',
        'next' => 'Следующая',
        'all_levels' => 'Все уровни',
        'all_routes' => 'Все роуты',
        'all_statuses' => 'Все статусы',
        'route_filter' => 'Фильтр по роуту',
        'status_filter' => 'Фильтр по статусу',
        'allowed' => 'Разрешено',
        'blocked' => 'Заблокировано',
        'logs_cleared' => 'Логи API Limiter очищены!',
        'log_file_not_found' => 'Файл логов не найден.',
        'reason' => 'Причина',
        'datetime' => 'Дата/Время',
        'logging_settings' => 'Настройки логирования',
        'logging_enabled' => 'Логирование включено',
        'auto_cleanup' => 'Автоочистка логов',
        'cleanup_periods' => [
            '15_min' => '15 минут',
            '30_min' => '30 минут', 
            '1_hour' => '1 час',
            '3_hours' => '3 часа',
            '6_hours' => '6 часов',
            '12_hours' => '12 часов',
            '1_day' => '1 день',
            '3_days' => '3 дня',
            '1_week' => '1 неделя',
            '2_weeks' => '2 недели',
            '1_month' => '1 месяц',
            '3_months' => '3 месяца',
            '6_months' => '6 месяцев',
            '1_year' => '1 год',
        ],
    ],
    
    // Source types
    'source_types' => [
        'core' => 'Azuriom Core',
        'plugin' => 'Плагин: :name',
        'unknown' => 'Неизвестно',
    ],
    
    // Route descriptions
    'route_descriptions' => [
        'auth_authenticate' => 'Аутентификация пользователя (получение токена)',
        'auth_verify' => 'Проверка токена пользователя',
        'auth_logout' => 'Выход из системы',
        'azlink' => 'AzLink интеграция с серверами',
        'skin_api_skins' => 'API для управления скинами',
        'skin_api_capes' => 'API для управления плащами',
        'skin_api_avatars' => 'Генерация аватаров',
        'apiextender' => 'Расширенное API функциональности',
        'posts' => 'API для работы с постами',
        'servers' => 'Информация о серверах',
        'rss' => 'RSS лента',
        'atom' => 'Atom лента',
        'admin_api_limiter_settings' => 'Настройки API Limiter (админ-панель)',
        'admin_api_limiter_routes' => 'Обнаружение API роутов (админ-панель)',
        'admin_apiextender' => 'Настройки API Extender (админ-панель)',
        'admin_skin_api' => 'Настройки Skin API (админ-панель)',
    ],
]; 