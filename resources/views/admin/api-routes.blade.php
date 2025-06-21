@extends('admin.layouts.admin')

@section('title', trans('api-limiter::admin.api_routes'))

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">🔍 {{ trans('api-limiter::admin.discovery.title') }}</h5>
            <div>
                <a href="{{ route('api-limiter.admin.settings') }}" class="btn btn-secondary btn-sm">
                    ⚙️ {{ trans('api-limiter::admin.settings') }}
                </a>
                <button type="button" class="btn btn-info btn-sm" onclick="location.reload()">
                    🔄 {{ trans('api-limiter::admin.buttons.refresh') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <p>{{ trans('api-limiter::admin.discovery.description') }}</p>
            
        </div>
    </div>

    <!-- Source statistics -->
    <div class="row mb-4">
        @php
            $sourceStats = [];
            foreach ($routes as $route) {
                $source = $route['source']['name'];
                $sourceStats[$source] = ($sourceStats[$source] ?? 0) + 1;
            }
        @endphp
        
        @foreach ($sourceStats as $source => $count)
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="card-title">{{ $source }}</h6>
                        <h4 class="text-primary">{{ $count }}</h4>
                        <small class="text-muted">{{ trans('api-limiter::admin.table.routes') }}</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="sourceFilter" class="form-label">{{ trans('api-limiter::admin.discovery.source_filter') }}:</label>
                    <select id="sourceFilter" class="form-control" onchange="filterRoutes()">
                        <option value="">{{ trans('api-limiter::admin.discovery.all_sources') }}</option>
                        @foreach (array_keys($sourceStats) as $source)
                            <option value="{{ $source }}">{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="methodFilter" class="form-label">{{ trans('api-limiter::admin.discovery.method_filter') }}:</label>
                    <select id="methodFilter" class="form-control" onchange="filterRoutes()">
                        <option value="">{{ trans('api-limiter::admin.discovery.all_methods') }}</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchFilter" class="form-label">{{ trans('api-limiter::admin.discovery.search_filter') }}:</label>
                    <input type="text" id="searchFilter" class="form-control" placeholder="{{ trans('api-limiter::admin.discovery.search_placeholder') }}" onkeyup="filterRoutes()">
                </div>
            </div>
        </div>
    </div>

    <!-- Routes table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="routesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">{{ trans('api-limiter::admin.table.methods') }}</th>
                            <th width="22%">{{ trans('api-limiter::admin.table.uri') }}</th>
                            <th width="15%">{{ trans('api-limiter::admin.table.source') }}</th>
                            <th width="20%">{{ trans('api-limiter::admin.table.description') }}</th>
                            <th width="8%">{{ trans('api-limiter::admin.table.middleware') }}</th>
                            <th width="12%">{{ trans('api-limiter::admin.table.rule') }}</th>
                            <th width="8%">{{ trans('api-limiter::admin.table.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($routes as $route)
                            <tr data-source="{{ $route['source']['name'] }}" data-methods="{{ $route['methods'] }}" data-uri="{{ $route['uri'] }}">
                                <td>
                                    @php
                                        $methods = explode('|', $route['methods']);
                                        $methodColors = [
                                            'GET' => 'success',
                                            'POST' => 'primary',
                                            'PUT' => 'warning',
                                            'DELETE' => 'danger',
                                            'PATCH' => 'info',
                                            'HEAD' => 'secondary'
                                        ];
                                    @endphp
                                    @foreach ($methods as $method)
                                        @if ($method !== 'HEAD')
                                            <span class="badge badge-{{ $methodColors[$method] ?? 'secondary' }} badge-sm">{{ $method }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    <code class="text-info">{{ $route['uri'] }}</code>
                                    @if ($route['name'])
                                        <br><small class="text-muted">{{ $route['name'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($route['source']['type'] === 'core')
                                        <span class="badge badge-success">{{ $route['source']['display'] }}</span>
                                    @elseif ($route['source']['type'] === 'plugin')
                                        <span class="badge badge-info">{{ $route['source']['display'] }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $route['source']['display'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $route['description'] }}</small>
                                </td>
                                <td>
                                    @php
                                        $isAdminRoute = str_starts_with($route['uri'], 'admin/');
                                        $hasApiLimiter = $route['has_api_limiter'] ?? false;
                                    @endphp
                                    
                                    @if ($isAdminRoute)
                                        <span class="badge badge-info" title="{{ trans('api-limiter::admin.tooltips.admin_protected') }}">🔐 Kernel</span>
                                    @elseif ($hasApiLimiter)
                                        <span class="badge badge-success" title="{{ trans('api-limiter::admin.tooltips.api_limiter_active') }}">✅ AL</span>
                                    @else
                                        <span class="badge badge-warning" title="{{ trans('api-limiter::admin.tooltips.api_limiter_inactive') }}">⚠️ No AL</span>
                                    @endif
                                    
                                    @if (count($route['middleware']) > 1)
                                        <span class="badge badge-secondary" title="{{ trans('api-limiter::admin.tooltips.middleware_count') }}">{{ count($route['middleware']) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($isAdminRoute)
                                        <span class="text-muted">—</span>
                                    @else
                                        @php $ruleInfo = $route['rule_info'] ?? null; @endphp
                                        @if ($ruleInfo)
                                            <span class="badge badge-light {{ $ruleInfo['class'] }}" title="{{ $ruleInfo['text'] }}">
                                                {{ $ruleInfo['icon'] }} {{ $ruleInfo['text'] }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">📋 Default</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($isAdminRoute)
                                        <span class="badge badge-warning">🔐 Admin</span>
                                    @else
                                        <span class="badge badge-success">🌐 Public</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed middleware information -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">ℹ️ {{ trans('api-limiter::admin.coverage.title') }}</h5>
        </div>
        <div class="card-body">
            @php
                $apiOnlyRoutes = 0;
                $adminRoutes = 0;
                $protectedApiRoutes = 0;
                $settings = \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getAllSettings();
                
                foreach ($routes as $route) {
                    $isAdminRoute = str_starts_with($route['uri'], 'admin/');
                    
                    if ($isAdminRoute) {
                        $adminRoutes++;
                    } else {
                        $apiOnlyRoutes++;
                        
                        // Check API Limiter protection only for public API routes
                        $hasApiLimiter = false;
                        
                        // Check explicit presence of our middleware
                        if (in_array('Azuriom\Plugin\ApiLimiter\Middleware\ApiLimiter', $route['middleware'])) {
                            $hasApiLimiter = true;
                        }
                        // Check presence of 'api' group - it automatically includes our middleware
                        elseif (in_array('api', $route['middleware'])) {
                            if ($settings['enabled']) {
                                // Check rules for specific route
                                $ruleData = \Azuriom\Plugin\ApiLimiter\Models\LimiterSetting::getRuleParamsForRoute($route['uri'], $route['name']);
                                $ruleType = $ruleData['type'] ?? 'rate_limit';
                                
                                // If rule is not "no_restrictions", then protection exists
                                $hasApiLimiter = ($ruleType !== 'no_restrictions');
                            }
                        }
                        
                        if ($hasApiLimiter) {
                            $protectedApiRoutes++;
                        }
                    }
                }
                
                $coverage = $apiOnlyRoutes > 0 ? round(($protectedApiRoutes / $apiOnlyRoutes) * 100, 1) : 0;
            @endphp
            
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-info">{{ $apiOnlyRoutes }}</h4>
                        <small>{{ trans('api-limiter::admin.coverage.total_api_routes') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-warning">{{ $adminRoutes }}</h4>
                        <small>{{ trans('api-limiter::admin.coverage.admin_routes') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-success">{{ $protectedApiRoutes }}</h4>
                        <small>{{ trans('api-limiter::admin.coverage.protected_routes') }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4 class="text-{{ $coverage > 80 ? 'success' : ($coverage > 50 ? 'warning' : 'danger') }}">{{ $coverage }}%</h4>
                        <small>{{ trans('api-limiter::admin.coverage.protection_coverage') }}</small>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="alert alert-info">
                <strong>{{ trans('api-limiter::admin.coverage.status_explanation') }}:</strong>
                <ul class="mb-0">
                    <li><span class="badge badge-success">✅ AL</span> - {{ trans('api-limiter::admin.coverage.al_active') }}</li>
                    <li><span class="badge badge-warning">⚠️ No AL</span> - {{ trans('api-limiter::admin.coverage.al_inactive') }}</li>
                    <li><span class="badge badge-success">Public</span> - {{ trans('api-limiter::admin.coverage.public_route') }}</li>
                    <li><span class="badge badge-warning">Admin</span> - {{ trans('api-limiter::admin.coverage.admin_route') }}</li>
                </ul>
                <div class="mt-2">
                    <small class="text-muted">
                        <strong>{{ trans('api-limiter::admin.coverage.important') }}:</strong> {{ trans('api-limiter::admin.coverage.api_group_info') }}
                    </small>
                </div>
                <div class="mt-2">
                    <small class="text-info">
                        <strong>{{ trans('api-limiter::admin.coverage.admin_routes') }}:</strong> {{ trans('api-limiter::admin.coverage.admin_middleware_info') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterRoutes() {
            const sourceFilter = document.getElementById('sourceFilter').value;
            const methodFilter = document.getElementById('methodFilter').value;
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#routesTable tbody tr');
            
            rows.forEach(row => {
                const source = row.getAttribute('data-source');
                const methods = row.getAttribute('data-methods');
                const uri = row.getAttribute('data-uri').toLowerCase();
                
                let show = true;
                
                if (sourceFilter && source !== sourceFilter) {
                    show = false;
                }
                
                if (methodFilter && !methods.includes(methodFilter)) {
                    show = false;
                }
                
                if (searchFilter && !uri.includes(searchFilter)) {
                    show = false;
                }
                
                row.style.display = show ? '' : 'none';
            });
        }
    </script>
@endsection 