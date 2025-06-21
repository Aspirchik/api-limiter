# 🛡️ API Limiter Plugin for Azuriom

**[English version](#-english-version) | [Русская версия](#-русская-версия)**

---

## 🇷🇺 Русская версия

Мощный и гибкий плагин для ограничения API запросов в Azuriom CMS с расширенными возможностями настройки и мониторинга.

## 🚀 Возможности

### 🔧 **Основная функциональность:**
- ✅ **Глобальное ограничение API** - применяется ко всем API эндпоинтам
- ✅ **Гибкие правила для роутов** - индивидуальные настройки для каждого API
- ✅ **7 типов правил** - от полного запрета до кастомных настроек
- ✅ **Whitelist IP адресов** - обход ограничений для доверенных IP
- ✅ **CIDR поддержка** - поддержка диапазонов IP адресов (192.168.1.0/24)
- ✅ **Два режима ограничения** - по IP адресу или по пользователю

### 📊 **Мониторинг и логирование:**
- ✅ **Детальное логирование** - полная история API запросов
- ✅ **Веб-интерфейс логов** - просмотр, фильтрация и поиск в админке
- ✅ **Автоочистка логов** - 12 периодов от 1 часа до 1 года
- ✅ **Статистика покрытия** - анализ защищенности API роутов

### 🛡️ **Безопасность и надежность:**
- ✅ **Защита от обновлений** - fallback режимы при сбоях Azuriom
- ✅ **Автоматическое восстановление** - самодиагностика и исправление
- ✅ **Кеширование настроек** - высокая производительность
- ✅ **Простая установка** - стандартная установка плагина Azuriom

## 📦 Установка

1. Скачайте плагин и распакуйте в папку `plugins/api-limiter/`
2. Зайдите в админ-панель Azuriom
3. Перейдите в раздел "Плагины" и активируйте "API Limiter"
4. Плагин автоматически создаст необходимые таблицы и настройки по умолчанию

## ⚙️ Настройка

После установки перейдите в админ-панель:
**Админ-панель → Ограничение API**

### 🎛️ Основные настройки:

- **Включить ограничение запросов** - глобальное включение/отключение
- **Запросов в минуту** - максимальное количество запросов (1-10000)
- **Ограничивать по** - выбор между IP адресом или пользователем
- **Разрешенные IP адреса** - whitelist для обхода ограничений
- **Включить логирование** - запись всех API запросов в логи
- **Автоочистка логов** - период хранения логов (1 часа - 1 год)

### 📋 Управление роутами:

- **API роуты** - просмотр всех обнаруженных API эндпоинтов
- **Правила роутов** - индивидуальные настройки для каждого API
- **Статистика покрытия** - анализ защищенности API роутов

### 📊 Мониторинг:

- **Логи запросов** - просмотр, фильтрация и анализ API запросов
- **Фильтрация по роутам** - отбор логов по конкретным эндпоинтам
- **Статус запросов** - ✅ Разрешено / ❌ Заблокировано

### Настройки по умолчанию:

```
Включено: Да
Запросов в минуту: 60
Ограничивать по: IP адресу
Whitelist: 127.0.0.1, ::1
Логирование: Включено
Автоочистка: 2 недели
```

## 🔧 Типы правил для роутов

### 📋 Доступные правила:

1. **✅ No Restrictions** (`no_restrictions`) - полный доступ без лимитов
2. **🚦 Rate Limiting** (`rate_limit`) - стандартный лимит из глобальных настроек  
3. **🚦 Rate Limiting (Custom)** (`rate_limit_custom`) - индивидуальный лимит для роута
4. **🔒 Whitelist Only** (`whitelist_only`) - доступ только для общего whitelist
5. **🔒 Whitelist (Custom)** (`whitelist_custom`) - доступ только для кастомного whitelist
6. **🚦🔒 Rate Limit + Whitelist** (`rate_limit_and_whitelist_custom`) - Индивидуальные лимиты + индивидуальный whitelist проходит без ограничений.
7. **🚦🔒 Whitelist + Rate Limit (Custom)** (`whitelist_and_rate_limit_custom`) - Только IP из whitelist допускаются, но с индивидуальными лимитами запросов.
8. **🚫 Restricted** (`restricted`) - Полный запрет доступа. Все запросы получают 403.

### 🔧 Конфигурация Whitelist

#### Поддерживаемые форматы:

- **Отдельный IP**: `192.168.1.100`
- **CIDR диапазон**: `192.168.1.0/24`
- **IPv6**: `::1, 2001:db8::/32`
- **Несколько IP**: `127.0.0.1, 192.168.1.1, 10.0.0.0/8`

#### Примеры использования:

```
# Локальные адреса
127.0.0.1, ::1

# Внутренняя сеть
192.168.0.0/16, 10.0.0.0/8

# Конкретные серверы
203.0.113.1, 203.0.113.2

# Смешанный формат
127.0.0.1, 192.168.1.0/24, 203.0.113.1
```

## 🚫 Ответ при превышении лимита

При превышении лимита API возвращает HTTP 429:

```http
HTTP/1.1 429 Too Many Requests
Content-Type: application/json

{
    "message": "Too Many Requests"
}
```

## 📝 Логирование

### 📊 Веб-интерфейс логов

Все API запросы записываются в отдельные логи и доступны через админ-панель:
**Админ-панель → Ограничение API → Логи запросов**

#### Возможности:
- 🔍 **Поиск по IP, роуту, URI**
- 📅 **Фильтрация по дате**
- 🎯 **Фильтр по статусу** (Разрешено/Заблокировано)
- 🛣️ **Фильтр по роутам**
- 📄 **Пагинация** (50 записей на страницу)
- 💾 **Скачивание логов**
- 🗑️ **Очистка логов**

### 📋 Формат логов

Компактный однострочный формат с миллисекундами в файлах `storage/logs/api-limiter-YYYY-MM-DD.log`:

```json
[2025-01-20 10:30:00.123] local.INFO: API Request {
    "ip": "192.168.1.100",
    "method": "POST", 
    "route": "api.auth.authenticate",
    "uri": "/api/auth/authenticate",
    "status": "allowed",
    "reason": "Whitelist IP"
}
```

### ⚙️ Настройки логирования

- **Включить логирование** - полное отключение записи логов
- **Автоочистка** - автоматическое удаление старых логов
- **14 периодов** - от 15 минут до 1 года

## 🔄 Режимы ограничения

### По IP адресу (по умолчанию)
- Лимит применяется к каждому IP адресу отдельно
- Подходит для публичных API
- Защищает от DDoS атак

### По пользователю
- Лимит применяется к каждому аутентифицированному пользователю
- Для неаутентифицированных запросов используется IP
- Подходит для персонализированных API

## 🛠️ Администрирование

### 🎛️ Панель управления
- **Настройки** - глобальные параметры ограничений
- **API роуты** - обзор всех обнаруженных эндпоинтов
- **Правила роутов** - индивидуальные настройки для каждого API
- **Логи запросов** - мониторинг и анализ трафика

### 🧹 Обслуживание
- **Очистка лимитов** - сброс всех счетчиков запросов
- **Очистка логов** - удаление файлов логов
- **Автоматическая очистка** - настраиваемая автоочистка логов

### 📊 Статистика
- **Покрытие защиты** - процент защищенных API роутов
- **Активные правила** - количество настроенных правил
- **Обнаруженные плагины** - автоматическое определение API

## 🔌 Автоматическое обнаружение API

Плагин автоматически сканирует и применяется ко всем API маршрутам:

### 🎯 Автоматическое обнаружение:
Плагин **автоматически обнаруживает ВСЕ API роуты** в системе через сканирование Laravel Route Collection:

- **Azuriom Core API** - встроенные эндпоинты (`/api/auth/*`, `/api/posts`, `/api/servers`)
- **Установленные плагины** - автоматически определяет по namespace (`Azuriom\Plugin\PluginName\*`)
- **Пользовательские API** - любые роуты начинающиеся с `api/`
- **Admin API** - административные эндпоинты с `api` в пути

**Примеры обнаруженных плагинов:**
- `SkinApi` - API скинов и аватаров
- `Shop` - API платежных уведомлений
- `Vote` - API обратных вызовов голосований
- `AuthMe` - интеграция с AuthMe
- `ApiLimiter` - собственные тестовые эндпоинты
- Любые другие плагины с API роутами

### 🔍 Автоматическая категоризация:
- **Core** - эндпоинты Azuriom (`Azuriom\Http\Controllers\Api\*`)
- **Plugin** - эндпоинты плагинов (`Azuriom\Plugin\PluginName\*`)
- **Unknown** - неопределенные источники (обрабатываются как Core)

## ⚡ Производительность

- **Кеширование настроек** - настройки кешируются на 1 час
- **Оптимизированные запросы** - минимальное воздействие на производительность  
- **Laravel Rate Limiter** - использует встроенные механизмы Laravel
- **Отдельные логи** - не засоряют основные логи Laravel
- **Компактное логирование** - однострочный формат записей

## 🔒 Безопасность

### 🛡️ Защита от атак:
- **DDoS защита** - предотвращает атаки на API
- **Брутфорс защита** - ограничение попыток аутентификации
- **Справедливое использование** - равный доступ для всех пользователей

### 🔐 Дополнительная безопасность:
- **Защита админки** - требует права `api-limiter.manage`
- **Валидация настроек** - проверка корректности конфигурации
- **Безопасные defaults** - консервативные настройки по умолчанию

### 🛠️ Устойчивость к обновлениям:
- **Fallback режимы** - 4 уровня деградации при сбоях
- **Автоматическое восстановление** - самодиагностика и исправление
- **Emergency режим** - пропуск всех запросов при критических сбоях

## 📋 Требования

- Azuriom CMS 1.2.0+
- PHP 8.1+
- Laravel 9.0+

## 🆘 Поддержка

При возникновении проблем:
1. Проверьте логи Laravel в `storage/logs/`
2. Убедитесь что плагин активирован
3. Проверьте настройки в админ-панели
4. Очистите кеш: `php artisan cache:clear`

## 📄 Лицензия

MIT License - свободное использование и модификация.

Плагин был сгенерирован с помощью модели claude-4-sonnet в Cursor IDE примерно за 100-150 запросов

---

## 🇺🇸 English Version

Powerful and flexible API rate limiting plugin for Azuriom CMS with advanced configuration and monitoring capabilities.

## 🚀 Features

### 🔧 **Core Functionality:**
- ✅ **Global API Rate Limiting** - applies to all API endpoints
- ✅ **Flexible Route Rules** - individual settings for each API
- ✅ **7 Rule Types** - from complete blocking to custom configurations
- ✅ **IP Whitelist** - bypass limitations for trusted IPs
- ✅ **CIDR Support** - support for IP ranges (192.168.1.0/24)
- ✅ **Two Limiting Modes** - by IP address or by user

### 📊 **Monitoring and Logging:**
- ✅ **Detailed Logging** - complete history of API requests
- ✅ **Web Log Interface** - view, filter and search in admin panel
- ✅ **Auto Log Cleanup** - 14 periods from 1 hour to 1 year
- ✅ **Coverage Statistics** - analysis of API route protection

### 🛡️ **Security and Reliability:**
- ✅ **Update Protection** - fallback modes during Azuriom failures
- ✅ **Automatic Recovery** - self-diagnosis and repair
- ✅ **Settings Caching** - high performance
- ✅ **Easy Installation** - standard Azuriom plugin installation

## 📦 Installation

1. Download the plugin and extract to `plugins/api-limiter/` folder
2. Go to Azuriom admin panel
3. Navigate to "Plugins" section and activate "API Limiter"
4. Plugin will automatically create necessary tables and default settings

## ⚙️ Configuration

After installation, go to admin panel:
**Admin Panel → API Limiter**

### 🎛️ Main Settings:

- **Enable Rate Limiting** - global enable/disable
- **Requests per Minute** - maximum requests count (1-10000)
- **Limit By** - choice between IP address or user
- **Whitelisted IPs** - whitelist for bypassing limitations
- **Enable Logging** - record all API requests to logs
- **Auto Log Cleanup** - log retention period (1 hour - 1 year)

### 📋 Route Management:

- **API Routes** - view all discovered API endpoints
- **Route Rules** - individual settings for each API
- **Coverage Statistics** - analysis of API route protection

### 📊 Monitoring:

- **Request Logs** - view, filter and analyze API requests
- **Route Filtering** - select logs by specific endpoints
- **Request Status** - ✅ Allowed / ❌ Blocked

### Default Settings:

```
Enabled: Yes
Requests per Minute: 60
Limit By: IP address
Whitelist: 127.0.0.1, ::1
Logging: Enabled
Auto Cleanup: 2 weeks
```

## 🔧 Route Rule Types

### 📋 Available Rules:

1. **✅ No Restrictions** (`no_restrictions`) - full access without limits
2. **🚦 Rate Limiting** (`rate_limit`) - standard limit from global settings
3. **🚦 Rate Limiting (Custom)** (`rate_limit_custom`) - individual limit for route
4. **🔒 Whitelist Only** (`whitelist_only`) - access only for general whitelist
5. **🔒 Whitelist (Custom)** (`whitelist_custom`) - access only for custom whitelist
6. **🚦🔒 Rate Limit + Whitelist** (`rate_limit_and_whitelist_custom`) - Individual limits + individual whitelist passes without restrictions.
7. **🚦🔒 Whitelist + Rate Limit (Custom)** (`whitelist_and_rate_limit_custom`) - Only whitelisted IPs are allowed, but with individual rate limits.
8. **🚫 Restricted** (`restricted`) - block all requests

### 🔧 Whitelist Configuration

#### Supported Formats:

- **Single IP**: `192.168.1.100`
- **CIDR Range**: `192.168.1.0/24`
- **IPv6**: `::1, 2001:db8::/32`
- **Multiple IPs**: `127.0.0.1, 192.168.1.1, 10.0.0.0/8`

#### Usage Examples:

```
# Local addresses
127.0.0.1, ::1

# Internal network
192.168.0.0/16, 10.0.0.0/8

# Specific servers
203.0.113.1, 203.0.113.2

# Mixed format
127.0.0.1, 192.168.1.0/24, 203.0.113.1
```

## 🚫 Rate Limit Exceeded Response

When rate limit is exceeded, API returns HTTP 429:

```http
HTTP/1.1 429 Too Many Requests
Content-Type: application/json

{
    "message": "Too Many Requests"
}
```

## 📝 Logging

### 📊 Web Log Interface

All API requests are recorded in separate logs and available through admin panel:
**Admin Panel → API Limiter → Request Logs**

#### Features:
- 🔍 **Search by IP, route, URI**
- 📅 **Date filtering**
- 🎯 **Status filter** (Allowed/Blocked)
- 🛣️ **Route filter**
- 📄 **Pagination** (50 records per page)
- 💾 **Download logs**
- 🗑️ **Clear logs**

### 📋 Log Format

Compact single-line format with milliseconds in `storage/logs/api-limiter-YYYY-MM-DD.log` files:

```json
[2025-01-20 10:30:00.123] local.INFO: API Request {
    "ip": "192.168.1.100",
    "method": "POST", 
    "route": "api.auth.authenticate",
    "uri": "/api/auth/authenticate",
    "status": "allowed",
    "reason": "Whitelist IP"
}
```

### ⚙️ Logging Settings

- **Enable Logging** - completely disable log recording
- **Auto Cleanup** - automatic deletion of old logs
- **14 Periods** - from 15 minutes to 1 year

## 🔄 Limiting Modes

### By IP Address (default)
- Limit applies to each IP address separately
- Suitable for public APIs
- Protects against DDoS attacks

### By User
- Limit applies to each authenticated user
- For unauthenticated requests, IP is used
- Suitable for personalized APIs

## 🛠️ Administration

### 🎛️ Control Panel
- **Settings** - global limitation parameters
- **API Routes** - overview of all discovered endpoints
- **Route Rules** - individual settings for each API
- **Request Logs** - monitoring and traffic analysis

### 🧹 Maintenance
- **Clear Limits** - reset all request counters
- **Clear Logs** - delete log files
- **Automatic Cleanup** - configurable auto-cleanup of logs

### 📊 Statistics
- **Protection Coverage** - percentage of protected API routes
- **Active Rules** - number of configured rules
- **Discovered Plugins** - automatic API detection

## 🔌 Automatic API Discovery

Plugin automatically scans and applies to all API routes:

### 🎯 Automatic Discovery:
Plugin **automatically discovers ALL API routes** in the system by scanning Laravel Route Collection:

- **Azuriom Core API** - built-in endpoints (`/api/auth/*`, `/api/posts`, `/api/servers`)
- **Installed Plugins** - automatically detects by namespace (`Azuriom\Plugin\PluginName\*`)
- **Custom APIs** - any routes starting with `api/`
- **Admin APIs** - administrative endpoints with `api` in path

**Examples of discovered plugins:**
- `SkinApi` - skins and avatars API
- `Shop` - payment notification API
- `Vote` - voting callback API
- `AuthMe` - AuthMe integration
- `ApiLimiter` - own testing endpoints
- Any other plugins with API routes

### 🔍 Automatic Categorization:
- **Core** - Azuriom endpoints (`Azuriom\Http\Controllers\Api\*`)
- **Plugin** - plugin endpoints (`Azuriom\Plugin\PluginName\*`)
- **Unknown** - unidentified sources (treated as Core)

## ⚡ Performance

- **Settings Caching** - settings cached for 1 hour
- **Optimized Queries** - minimal performance impact
- **Laravel Rate Limiter** - uses built-in Laravel mechanisms
- **Separate Logs** - doesn't clutter main Laravel logs
- **Compact Logging** - single-line record format

## 🔒 Security

### 🛡️ Attack Protection:
- **DDoS Protection** - prevents API attacks
- **Brute Force Protection** - limits authentication attempts
- **Fair Usage** - equal access for all users

### 🔐 Additional Security:
- **Admin Protection** - requires `api-limiter.manage` permission
- **Settings Validation** - configuration correctness check
- **Safe Defaults** - conservative default settings

### 🛠️ Update Resilience:
- **Fallback Modes** - 4 degradation levels during failures
- **Automatic Recovery** - self-diagnosis and repair
- **Emergency Mode** - pass all requests during critical failures

## 📋 Requirements

- Azuriom CMS 1.2.0+
- PHP 8.1+
- Laravel 9.0+

## 🆘 Support

If you encounter issues:
1. Check Laravel logs in `storage/logs/`
2. Make sure the plugin is activated
3. Check settings in admin panel
4. Clear cache: `php artisan cache:clear`

## 📄 License

MIT License - free use and modification.

The plugin was generated using the claude-4-sonnet model in the Cursor IDE in about 100-150 requests

---

**Created for the Azuriom Community** 🚀 
