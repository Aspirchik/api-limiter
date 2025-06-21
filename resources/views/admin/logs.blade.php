@extends('admin.layouts.admin')

@section('title', trans('api-limiter::admin.logs.title'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">📋 {{ trans('api-limiter::admin.logs.title') }}</h5>
            <div>
                <a href="{{ route('api-limiter.admin.settings') }}" class="btn btn-secondary btn-sm">
                    ⚙️ {{ trans('api-limiter::admin.nav.settings') }}
                </a>
                <a href="{{ route('api-limiter.admin.api-routes') }}" class="btn btn-info btn-sm">
                    🔍 {{ trans('api-limiter::admin.nav.api_routes') }}
                </a>
                @if(!empty($logs) || $total > 0)
                    <a href="{{ route('api-limiter.admin.logs.download') }}" class="btn btn-success btn-sm">
                        📥 {{ trans('api-limiter::admin.logs.download') }}
                    </a>
                    <form method="POST" action="{{ route('api-limiter.admin.logs.clear') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ trans('api-limiter::admin.logs.clear_confirm') }}')">
                            🗑️ {{ trans('api-limiter::admin.logs.clear') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <p>{{ trans('api-limiter::admin.logs.description') }}</p>
            
            <!-- Logging settings -->
            <div class="alert alert-info">
                <form method="POST" action="{{ route('api-limiter.admin.logs.settings') }}" class="row align-items-end">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('api-limiter::admin.logs.logging_enabled') }}:</label>
                        <select name="logging_enabled" class="form-control">
                            <option value="1" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('logging_enabled', true) ? 'selected' : '' }}>✅ {{ trans('api-limiter::admin.logs.enabled') }}</option>
                            <option value="0" {{ !\Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('logging_enabled', true) ? 'selected' : '' }}>❌ {{ trans('api-limiter::admin.logs.disabled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('api-limiter::admin.logs.auto_cleanup') }}:</label>
                        <select name="auto_cleanup_logs" class="form-control">
                            <option value="1_hour" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '1_hour' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.1_hour') }}</option>
                            <option value="3_hours" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '3_hours' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.3_hours') }}</option>
                            <option value="6_hours" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '6_hours' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.6_hours') }}</option>
                            <option value="12_hours" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '12_hours' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.12_hours') }}</option>
                            <option value="1_day" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '1_day' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.1_day') }}</option>
                            <option value="3_days" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '3_days' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.3_days') }}</option>
                            <option value="1_week" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '1_week' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.1_week') }}</option>
                            <option value="2_weeks" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '2_weeks' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.2_weeks') }}</option>
                            <option value="1_month" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '1_month' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.1_month') }}</option>
                            <option value="3_months" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '3_months' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.3_months') }}</option>
                            <option value="6_months" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '6_months' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.6_months') }}</option>
                            <option value="1_year" {{ \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getValue('auto_cleanup_logs', '1_week') === '1_year' ? 'selected' : '' }}>{{ trans('api-limiter::admin.logs.cleanup_periods.1_year') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm">💾 {{ trans('api-limiter::admin.buttons.save') }}</button>
                    </div>
                </form>
            </div>
            
            <!-- Filters -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label for="level" class="form-label">{{ trans('api-limiter::admin.logs.level') }}:</label>
                        <select name="level" id="level" class="form-control">
                            <option value="">{{ trans('api-limiter::admin.logs.all_levels') }}</option>
                            <option value="INFO" {{ ($filters['level'] ?? '') === 'INFO' ? 'selected' : '' }}>INFO</option>
                            <option value="WARNING" {{ ($filters['level'] ?? '') === 'WARNING' ? 'selected' : '' }}>WARNING</option>
                            <option value="ERROR" {{ ($filters['level'] ?? '') === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                            <option value="DEBUG" {{ ($filters['level'] ?? '') === 'DEBUG' ? 'selected' : '' }}>DEBUG</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">{{ trans('api-limiter::admin.logs.date') }}:</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label for="route" class="form-label">{{ trans('api-limiter::admin.logs.route_filter') }}:</label>
                        <select name="route" id="route" class="form-control">
                            <option value="">{{ trans('api-limiter::admin.logs.all_routes') }}</option>
                            @foreach($uniqueRoutes as $route)
                                <option value="{{ $route }}" {{ ($filters['route'] ?? '') === $route ? 'selected' : '' }}>
                                    {{ $route }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">{{ trans('api-limiter::admin.logs.status_filter') }}:</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">{{ trans('api-limiter::admin.logs.all_statuses') }}</option>
                            <option value="allowed" {{ ($filters['status'] ?? '') === 'allowed' ? 'selected' : '' }}>✅ {{ trans('api-limiter::admin.logs.allowed') }}</option>
                            <option value="blocked" {{ ($filters['status'] ?? '') === 'blocked' ? 'selected' : '' }}>❌ {{ trans('api-limiter::admin.logs.blocked') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="search" class="form-label">{{ trans('api-limiter::admin.logs.search') }}:</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="{{ trans('api-limiter::admin.logs.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">🔍 {{ trans('api-limiter::admin.logs.filter') }}</button>
                            <a href="{{ route('api-limiter.admin.logs') }}" class="btn btn-secondary btn-sm">{{ trans('api-limiter::admin.logs.reset') }}</a>
                        </div>
                    </div>
                </div>
            </form>
            
            @if($total > 0)
                <div class="alert alert-info">
                    {{ trans('api-limiter::admin.logs.found_records') }}: <strong>{{ $total }}</strong>
                    ({{ trans('api-limiter::admin.logs.page_of', ['current' => $currentPage, 'total' => $totalPages]) }})
                </div>
            @endif
        </div>
    </div>

    @if(empty($logs))
        <div class="card">
            <div class="card-body text-center">
                <h5>📄 {{ trans('api-limiter::admin.logs.no_logs') }}</h5>
                <p class="text-muted">
                    @if(!empty($filters['level']) || !empty($filters['search']) || !empty($filters['date']))
                        {{ trans('api-limiter::admin.logs.no_logs_filtered') }}
                    @else
                        {{ trans('api-limiter::admin.logs.no_logs_empty') }}
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="12%">{{ trans('api-limiter::admin.logs.datetime') }}</th>
                                <th width="8%">{{ trans('api-limiter::admin.table.status') }}</th>
                                <th width="10%">IP</th>
                                <th width="8%">{{ trans('api-limiter::admin.table.methods') }}</th>
                                <th width="25%">{{ trans('api-limiter::admin.logs.route_filter') }}</th>
                                <th width="25%">{{ trans('api-limiter::admin.table.uri') }}</th>
                                <th width="12%">{{ trans('api-limiter::admin.logs.reason') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $log['date'] }}</small>
                                    </td>
                                    <td>
                                        @if($log['status'] === 'allowed')
                                            <span class="badge badge-success">✅ {{ trans('api-limiter::admin.logs.allowed') }}</span>
                                        @elseif($log['status'] === 'blocked')
                                            <span class="badge badge-danger">❌ {{ trans('api-limiter::admin.logs.blocked') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $log['status'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="text-info">{{ $log['ip'] }}</code>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $log['method'] === 'GET' ? 'primary' : ($log['method'] === 'POST' ? 'success' : 'warning') }}">
                                            {{ $log['method'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $log['route'] }}</small>
                                    </td>
                                    <td>
                                        <code class="text-secondary">{{ $log['uri'] }}</code>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $log['reason'] }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($totalPages > 1)
                    <nav aria-label="Log pagination">
                        <ul class="pagination justify-content-center">
                            @if($currentPage > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                                        ← {{ trans('api-limiter::admin.logs.previous') }}
                                    </a>
                                </li>
                            @endif
                            
                            @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                <li class="page-item {{ $i === $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">
                                        {{ $i }}
                                    </a>
                                </li>
                            @endfor
                            
                            @if($currentPage < $totalPages)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                                        {{ trans('api-limiter::admin.logs.next') }} →
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    @endif
@endsection 