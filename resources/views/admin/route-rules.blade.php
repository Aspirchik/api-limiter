@extends('admin.layouts.admin')

@section('title', trans('api-limiter::admin.title') . ' - ' . trans('api-limiter::admin.settings'))

@section('content')
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">🛡️ {{ trans('api-limiter::admin.title') }} {{ trans('api-limiter::admin.settings') }}</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('api-limiter.admin.update') }}">
                @csrf

                <!-- Основные настройки -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">⚙️ {{ trans('api-limiter::admin.general.title') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="enabled" name="enabled" 
                                               @if(setting('api-limiter.enabled', false)) checked @endif>
                                        <label class="form-check-label" for="enabled">
                                            {{ trans('api-limiter::admin.general.enabled') }}
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ trans('api-limiter::admin.general.enabled_help') }}
                                    </small>
                                </div>

                                <h6 class="text-muted mt-4 mb-3">📊 {{ trans('api-limiter::admin.general.max_attempts') }} {{ trans('api-limiter::admin.general.per_minutes_help') }}</h6>

                                <div class="form-group mb-3">
                                    <label for="max_attempts" class="form-label">{{ trans('api-limiter::admin.general.max_attempts') }}</label>
                                    <input type="number" class="form-control @error('max_attempts') is-invalid @enderror" 
                                           id="max_attempts" name="max_attempts" value="{{ old('max_attempts', $settings['max_attempts']) }}" 
                                           min="1" max="10000" required>
                                    <small class="form-text text-muted">
                                        {{ trans('api-limiter::admin.general.max_attempts_help') }}
                                    </small>
                                    @error('max_attempts')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="per_minutes" class="form-label">{{ trans('api-limiter::admin.general.per_minutes') }}</label>
                                    <input type="number" class="form-control @error('per_minutes') is-invalid @enderror" 
                                           id="per_minutes" name="per_minutes" value="{{ old('per_minutes', $settings['per_minutes']) }}" 
                                           min="1" max="60" required>
                                    <small class="form-text text-muted">
                                        {{ trans('api-limiter::admin.general.per_minutes_help') }}
                                    </small>
                                    @error('per_minutes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="whitelist_ips" class="form-label">{{ trans('api-limiter::admin.general.whitelist_ips') }}</label>
                                    <textarea class="form-control @error('whitelist_ips') is-invalid @enderror" 
                                              id="whitelist_ips" name="whitelist_ips" rows="3" 
                                              placeholder="127.0.0.1, 192.168.1.0/24, ::1">{{ old('whitelist_ips', $settings['whitelist_ips']) }}</textarea>
                                    <small class="form-text text-muted">
                                        {{ trans('api-limiter::admin.general.whitelist_ips_help') }}
                                    </small>
                                    @error('whitelist_ips')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3" style="margin-top: 102px;">
                                    <label for="default_rule" class="form-label">{{ trans('api-limiter::admin.general.default_rule') }}</label>
                                    <select class="form-control @error('default_rule') is-invalid @enderror" 
                                            id="default_rule" name="default_rule" required>
                                        <option value="no_restrictions" @if(($settings['default_rule'] ?? 'rate_limit') === 'no_restrictions') selected @endif>
                                            ✅ {{ trans('api-limiter::admin.rule_types.no_restrictions') }}
                                        </option>
                                        <option value="rate_limit" @if(($settings['default_rule'] ?? 'rate_limit') === 'rate_limit') selected @endif>
                                            🚦 {{ trans('api-limiter::admin.rule_types.rate_limit') }}
                                        </option>
                                        <option value="whitelist_only" @if(($settings['default_rule'] ?? 'rate_limit') === 'whitelist_only') selected @endif>
                                            🔒 {{ trans('api-limiter::admin.rule_types.whitelist_only') }}
                                        </option>
                                        <option value="restricted" @if(($settings['default_rule'] ?? 'rate_limit') === 'restricted') selected @endif>
                                            🚫 {{ trans('api-limiter::admin.rule_types.restricted') }}
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        {{ trans('api-limiter::admin.general.default_rule_help') }}
                                    </small>
                                    @error('default_rule')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="limit_by" class="form-label">{{ trans('api-limiter::admin.general.limit_by') }}</label>
                                    <select class="form-control @error('limit_by') is-invalid @enderror" id="limit_by" name="limit_by" required>
                                        <option value="ip" @if($settings['limit_by'] === 'ip') selected @endif>{{ trans('api-limiter::admin.general.limit_by_ip') }}</option>
                                        <option value="user" @if($settings['limit_by'] === 'user') selected @endif>{{ trans('api-limiter::admin.general.limit_by_user') }}</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        {{ trans('api-limiter::admin.general.limit_by_help') }}
                                    </small>
                                    @error('limit_by')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Специальные правила для роутов -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🎯 {{ trans('api-limiter::admin.rules.title') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="customRules">
                            @foreach($currentRules as $route => $ruleData)
                                @php
                                    $rule = is_array($ruleData) ? $ruleData['type'] : $ruleData;
                                    $customParams = is_array($ruleData) ? $ruleData : [];
                                @endphp
                                <div class="custom-rule mb-4 p-3 border rounded">
                                    <div class="row align-items-start">
                                        <div class="col-md-4">
                                            <label class="form-label">{{ trans('api-limiter::admin.rules.route') }}</label>
                                            <input type="text" class="form-control" name="custom_rules[{{ $loop->index }}][route]" 
                                                   value="{{ $route }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ trans('api-limiter::admin.rules.rule_type') }}</label>
                                            <select class="form-control rule-selector" name="custom_rules[{{ $loop->index }}][rule]" 
                                                    onchange="toggleRuleOptions(this)" required>
                                                <option value="no_restrictions" @if($rule === 'no_restrictions') selected @endif>✅ {{ trans('api-limiter::admin.rule_types.no_restrictions') }}</option>
                                                <option value="rate_limit" @if($rule === 'rate_limit') selected @endif>🚦 {{ trans('api-limiter::admin.rule_types.rate_limit') }}</option>
                                                <option value="rate_limit_custom" @if($rule === 'rate_limit_custom') selected @endif>🚦 {{ trans('api-limiter::admin.rule_types.rate_limit_custom') }}</option>
                                                <option value="whitelist_only" @if($rule === 'whitelist_only') selected @endif>🔒 {{ trans('api-limiter::admin.rule_types.whitelist_only') }}</option>
                                                <option value="whitelist_custom" @if($rule === 'whitelist_custom') selected @endif>🔒 {{ trans('api-limiter::admin.rule_types.whitelist_custom') }}</option>
                                                <option value="rate_limit_whitelist" @if($rule === 'rate_limit_whitelist') selected @endif>🚦🔒 {{ trans('api-limiter::admin.rule_types.rate_limit_and_whitelist') }}</option>
                                                <option value="rate_limit_whitelist_custom" @if($rule === 'rate_limit_whitelist_custom') selected @endif>🚦🔒 {{ trans('api-limiter::admin.rule_types.rate_limit_and_whitelist_custom') }}</option>
                                                <option value="restricted" @if($rule === 'restricted') selected @endif>🚫 {{ trans('api-limiter::admin.rule_types.restricted') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeRule(this)">
                                                🗑️ {{ trans('api-limiter::admin.buttons.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Настройки для Rate Limiting Custom -->
                                    <div class="rule-options mt-3" data-rule="rate_limit_custom" style="display: {{ $rule === 'rate_limit_custom' ? 'block' : 'none' }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ trans('api-limiter::admin.custom_fields.requests_count') }}</label>
                                                <input type="number" class="form-control" name="custom_rules[{{ $loop->index }}][max_attempts]" 
                                                       value="{{ $customParams['max_attempts'] ?? '' }}" min="1" max="10000">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ trans('api-limiter::admin.custom_fields.period_minutes') }}</label>
                                                <input type="number" class="form-control" name="custom_rules[{{ $loop->index }}][per_minutes]" 
                                                       value="{{ $customParams['per_minutes'] ?? '' }}" min="1" max="60">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Настройки для Whitelist Custom -->
                                    <div class="rule-options mt-3" data-rule="whitelist_custom" style="display: {{ $rule === 'whitelist_custom' ? 'block' : 'none' }}">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="form-label">{{ trans('api-limiter::admin.custom_fields.whitelist_ip') }}</label>
                                                <textarea class="form-control" name="custom_rules[{{ $loop->index }}][whitelist_ips]" 
                                                          rows="2" placeholder="127.0.0.1, 192.168.1.0/24">{{ $customParams['whitelist_ips'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Настройки для Rate Limiting + Whitelist Custom -->
                                    <div class="rule-options mt-3" data-rule="rate_limit_whitelist_custom" style="display: {{ $rule === 'rate_limit_whitelist_custom' ? 'block' : 'none' }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">{{ trans('api-limiter::admin.custom_fields.requests_count') }}</label>
                                                <input type="number" class="form-control" name="custom_rules[{{ $loop->index }}][max_attempts]" 
                                                       value="{{ $customParams['max_attempts'] ?? '' }}" min="1" max="10000">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">{{ trans('api-limiter::admin.custom_fields.period_minutes') }}</label>
                                                <input type="number" class="form-control" name="custom_rules[{{ $loop->index }}][per_minutes]" 
                                                       value="{{ $customParams['per_minutes'] ?? '' }}" min="1" max="60">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">{{ trans('api-limiter::admin.custom_fields.whitelist_ip') }}</label>
                                                <textarea class="form-control" name="custom_rules[{{ $loop->index }}][whitelist_ips]" 
                                                          rows="1" placeholder="127.0.0.1, 192.168.1.0/24">{{ $customParams['whitelist_ips'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success btn-sm" onclick="addCustomRule()">
                                ➕ {{ trans('api-limiter::admin.custom_fields.add_empty_rule') }}
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="showRouteSelector()">
                                🔍 {{ trans('api-limiter::admin.custom_fields.select_api_route') }}
                            </button>
                        </div>
                        
                        <div class="mt-3">
                            <small class="form-text text-muted">
                                <strong>{{ trans('api-limiter::admin.custom_fields.examples') }}:</strong><br>
                                • <strong>{{ trans('api-limiter::admin.custom_fields.route_names') }}:</strong> auth.verify, auth.authenticate<br>
                                • <strong>{{ trans('api-limiter::admin.custom_fields.wildcard_paths') }}:</strong> api/auth/*, api/skin-api/*<br>
                                • <strong>{{ trans('api-limiter::admin.custom_fields.exact_paths') }}:</strong> api/azlink, api/posts
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Типы правил -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ℹ️ {{ trans('api-limiter::admin.custom_fields.rule_descriptions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-12">
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-success">✅ {{ trans('api-limiter::admin.rule_types.no_restrictions') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.no_restrictions') }}</small>
                                </div>
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-primary">🚦 {{ trans('api-limiter::admin.rule_types.rate_limit') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.rate_limiting') }}</small>
                                </div>
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-info">🚦 {{ trans('api-limiter::admin.rule_types.rate_limit_custom') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.rate_limiting_custom') }}</small>
                                </div>
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-warning">🔒 {{ trans('api-limiter::admin.rule_types.whitelist_only') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.whitelist_only') }}</small>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-12">
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-secondary">🔒 {{ trans('api-limiter::admin.rule_types.whitelist_custom') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.whitelist_custom') }}</small>
                                </div>
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-dark">🚦🔒 {{ trans('api-limiter::admin.rule_types.rate_limit_and_whitelist') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.rate_limiting_whitelist') }}</small>
                                </div>
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge" style="background-color: #6f42c1; color: white;">🚦🔒 {{ trans('api-limiter::admin.rule_types.rate_limit_and_whitelist_custom') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.rate_limiting_whitelist_custom') }}</small>
                                </div>
                                <div class="p-2 border rounded mb-2">
                                    <span class="badge bg-danger">🚫 {{ trans('api-limiter::admin.rule_types.restricted') }}</span>
                                    <small class="text-muted d-block">{{ trans('api-limiter::admin.rule_descriptions.restricted') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        💾 {{ trans('api-limiter::admin.custom_fields.save_settings') }}
                    </button>
                    <a href="{{ route('admin.plugins.index') }}" class="btn btn-secondary">
                        ← {{ trans('api-limiter::admin.custom_fields.back_to_plugins') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal для выбора API роутов -->
    <div class="modal fade" id="routeSelectorModal" tabindex="-1" aria-labelledby="routeSelectorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="routeSelectorModalLabel">🔍 {{ trans('api-limiter::admin.custom_fields.select_api_route') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('api-limiter::admin.table.methods') }}</th>
                                    <th>{{ trans('api-limiter::admin.table.uri') }}</th>
                                    <th>{{ trans('api-limiter::admin.table.source') }}</th>
                                    <th>{{ trans('api-limiter::admin.buttons.add') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($routes as $route)
                                    <tr>
                                        <td>
                                            @foreach(explode('|', $route['methods']) as $method)
                                                <span class="badge badge-{{ $method === 'GET' ? 'info' : ($method === 'POST' ? 'warning' : 'secondary') }}">{{ $method }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <code>{{ $route['uri'] }}</code>
                                            @if($route['name'])
                                                <br><small class="text-muted">{{ $route['name'] }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $route['source']['type'] === 'core' ? 'primary' : 'info' }}">
                                                {{ $route['source']['display'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="selectRoute('{{ $route['name'] ?: $route['uri'] }}')">
                                                {{ trans('api-limiter::admin.buttons.add') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let ruleIndex = {{ count($currentRules) }};

        function addCustomRule(route = '', rule = 'no_restrictions') {
            const rulesContainer = document.getElementById('customRules');
            const ruleHtml = `
                <div class="custom-rule mb-4 p-3 border rounded">
                    <div class="row align-items-start">
                        <div class="col-md-4">
                            <label class="form-label">API роут</label>
                            <input type="text" class="form-control" name="custom_rules[${ruleIndex}][route]" 
                                   value="${route}" placeholder="api/auth/*, auth.verify, api/posts" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Правило</label>
                            <select class="form-control rule-selector" name="custom_rules[${ruleIndex}][rule]" 
                                    onchange="toggleRuleOptions(this)" required>
                                <option value="no_restrictions" ${rule === 'no_restrictions' ? 'selected' : ''}>✅ No Restrictions</option>
                                <option value="rate_limit" ${rule === 'rate_limit' ? 'selected' : ''}>🚦 Rate Limiting</option>
                                <option value="rate_limit_custom" ${rule === 'rate_limit_custom' ? 'selected' : ''}>🚦 Rate Limiting Custom</option>
                                <option value="whitelist_only" ${rule === 'whitelist_only' ? 'selected' : ''}>🔒 Whitelist Only</option>
                                <option value="whitelist_custom" ${rule === 'whitelist_custom' ? 'selected' : ''}>🔒 Whitelist Custom</option>
                                                                                <option value="rate_limit_whitelist" ${rule === 'rate_limit_whitelist' ? 'selected' : ''}>🚦🔒 Rate Limiting + Whitelist</option>
                                                <option value="rate_limit_whitelist_custom" ${rule === 'rate_limit_whitelist_custom' ? 'selected' : ''}>🚦🔒 Rate Limiting + Whitelist Custom</option>
                                                <option value="restricted" ${rule === 'restricted' ? 'selected' : ''}>🚫 Restricted</option>
                                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeRule(this)">
                                🗑️ Удалить
                            </button>
                        </div>
                    </div>
                    
                    <!-- Настройки для Rate Limiting Custom -->
                    <div class="rule-options mt-3" data-rule="rate_limit_custom" style="display: none">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Количество запросов</label>
                                <input type="number" class="form-control" name="custom_rules[${ruleIndex}][max_attempts]" 
                                       min="1" max="10000" placeholder="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Период (минуты)</label>
                                <input type="number" class="form-control" name="custom_rules[${ruleIndex}][per_minutes]" 
                                       min="1" max="60" placeholder="1">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Настройки для Whitelist Custom -->
                    <div class="rule-options mt-3" data-rule="whitelist_custom" style="display: none">
                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label">Whitelist IP адресов</label>
                                <textarea class="form-control" name="custom_rules[${ruleIndex}][whitelist_ips]" 
                                          rows="2" placeholder="127.0.0.1, 192.168.1.0/24"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Настройки для Rate Limiting + Whitelist Custom -->
                    <div class="rule-options mt-3" data-rule="rate_limit_whitelist_custom" style="display: none">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Количество запросов</label>
                                <input type="number" class="form-control" name="custom_rules[${ruleIndex}][max_attempts]" 
                                       min="1" max="10000" placeholder="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Период (минуты)</label>
                                <input type="number" class="form-control" name="custom_rules[${ruleIndex}][per_minutes]" 
                                       min="1" max="60" placeholder="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Whitelist IP</label>
                                <textarea class="form-control" name="custom_rules[${ruleIndex}][whitelist_ips]" 
                                          rows="1" placeholder="127.0.0.1"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            rulesContainer.insertAdjacentHTML('beforeend', ruleHtml);
            ruleIndex++;
        }

        function toggleRuleOptions(selectElement) {
            const ruleContainer = selectElement.closest('.custom-rule');
            const selectedRule = selectElement.value;
            
            // Скрыть все опции
            const allOptions = ruleContainer.querySelectorAll('.rule-options');
            allOptions.forEach(option => {
                option.style.display = 'none';
            });
            
            // Показать нужные опции
            if (selectedRule === 'rate_limit_custom') {
                const options = ruleContainer.querySelector('[data-rule="rate_limit_custom"]');
                if (options) options.style.display = 'block';
            } else if (selectedRule === 'whitelist_custom') {
                const options = ruleContainer.querySelector('[data-rule="whitelist_custom"]');
                if (options) options.style.display = 'block';
            } else if (selectedRule === 'rate_limit_whitelist_custom') {
                const options = ruleContainer.querySelector('[data-rule="rate_limit_whitelist_custom"]');
                if (options) options.style.display = 'block';
            }
        }

        function removeRule(button) {
            button.closest('.custom-rule').remove();
        }

        function showRouteSelector() {
            const modal = new bootstrap.Modal(document.getElementById('routeSelectorModal'));
            modal.show();
        }

        function selectRoute(route) {
            addCustomRule(route);
            const modal = bootstrap.Modal.getInstance(document.getElementById('routeSelectorModal'));
            modal.hide();
        }

        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            // Настроить отображение опций для существующих правил
            document.querySelectorAll('.rule-selector').forEach(function(select) {
                toggleRuleOptions(select);
            });
        });
    </script>
@endsection 