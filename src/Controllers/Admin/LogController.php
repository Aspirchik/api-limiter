<?php

namespace Azuriom\Plugin\ApiLimiter\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\ApiLimiter\Models\LimiterSetting;
use Azuriom\Plugin\ApiLimiter\Support\LogCleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * Constructor - ensure admin access
     */
    public function __construct()
    {
        $this->middleware('can:api-limiter.manage');
    }

    /**
     * Show API request logs.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Auto-cleanup logs on page refresh - always run cleanup by time
        try {
            $logCleaner = new LogCleaner();
            $logCleaner->cleanOldEntries();
        } catch (\Exception $e) {
            // Don't break the page if cleanup fails
        }

        $logs = $this->getApiLogs();
        $filters = $request->only(['level', 'search', 'date', 'route', 'status']);
        
        // Apply filters
        if (!empty($filters['level'])) {
            $logs = array_filter($logs, function($log) use ($filters) {
                return $log['level'] === $filters['level'];
            });
        }
        
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $logs = array_filter($logs, function($log) use ($search) {
                return str_contains(strtolower($log['ip'] ?? ''), $search) ||
                       str_contains(strtolower($log['route'] ?? ''), $search) ||
                       str_contains(strtolower($log['uri'] ?? ''), $search) ||
                       str_contains(strtolower($log['reason'] ?? ''), $search);
            });
        }
        
        if (!empty($filters['date'])) {
            $logs = array_filter($logs, function($log) use ($filters) {
                return str_starts_with($log['date'], $filters['date']);
            });
        }
        
        if (!empty($filters['route'])) {
            $logs = array_filter($logs, function($log) use ($filters) {
                return $log['route'] === $filters['route'];
            });
        }
        
        if (!empty($filters['status'])) {
            $logs = array_filter($logs, function($log) use ($filters) {
                return $log['status'] === $filters['status'];
            });
        }
        
        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 50;
        $total = count($logs);
        $logs = array_slice($logs, ($page - 1) * $perPage, $perPage);
        
        // Get unique routes for filter dropdown
        $allLogs = $this->getApiLogs();
        $uniqueRoutes = array_unique(array_column($allLogs, 'route'));
        sort($uniqueRoutes);
        
        return view('api-limiter::admin.logs', [
            'logs' => $logs,
            'filters' => $filters,
            'total' => $total,
            'currentPage' => $page,
            'totalPages' => ceil($total / $perPage),
            'uniqueRoutes' => $uniqueRoutes,
        ]);
    }
    
    /**
     * Clear API logs.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clear()
    {
        // Additional permission check
        if (!auth()->user()->can('api-limiter.manage')) {
            abort(403, 'Access denied');
        }
        
        $logFile = storage_path('logs/api-limiter-logs.log');
        
        if (File::exists($logFile)) {
            File::delete($logFile);
        }
        
        return redirect()->route('api-limiter.admin.logs')
            ->with('success', trans('api-limiter::admin.logs.logs_cleared'));
    }

    /**
     * Manual cleanup of old logs.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanup()
    {
        // Additional permission check
        if (!auth()->user()->can('api-limiter.manage')) {
            abort(403, 'Access denied');
        }
        
        try {
            $logCleaner = new LogCleaner();
            $result = $logCleaner->cleanOldEntries();
            
            if ($result['success']) {
                $message = trans('api-limiter::admin.logs.cleanup_success', [
                    'deleted' => $result['deleted_entries'],
                    'space_freed' => $logCleaner->formatBytes($result['space_freed'] ?? 0),
                    'period' => $result['cleanup_period'] ?? 'unknown'
                ]);
                
                return redirect()->route('api-limiter.admin.logs')
                    ->with('success', $message);
            } else {
                return redirect()->route('api-limiter.admin.logs')
                    ->with('error', $result['message'] ?? 'Cleanup failed');
            }
        } catch (\Exception $e) {
            return redirect()->route('api-limiter.admin.logs')
                ->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download API logs.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        // Additional permission check
        if (!auth()->user()->can('api-limiter.manage')) {
            abort(403, 'Access denied');
        }
        
        $logFile = storage_path('logs/api-limiter-logs.log');
        
        if (!File::exists($logFile)) {
            return redirect()->route('api-limiter.admin.logs')
                ->with('error', trans('api-limiter::admin.logs.log_file_not_found'));
        }
        
        $filename = 'api-limiter-logs-' . date('Y-m-d-H-i-s') . '.log';
        
        return response()->download($logFile, $filename);
    }
    
    /**
     * Update logging settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        // Additional permission check
        if (!auth()->user()->can('api-limiter.manage')) {
            abort(403, 'Access denied');
        }
        
        // Validate input
        $request->validate([
            'logging_enabled' => 'required|string|in:0,1',
            'auto_cleanup_logs' => 'required|string|in:1_hour,3_hours,6_hours,12_hours,1_day,3_days,1_week,2_weeks,1_month,3_months,6_months,1_year',
        ]);
        
        // Save logging settings
        LimiterSetting::setValues([
            'logging_enabled' => $request->input('logging_enabled') === '1',
            'auto_cleanup_logs' => $request->input('auto_cleanup_logs', '1_week'),
        ]);
        
        // Clear cache
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        
        return redirect()->route('api-limiter.admin.logs')
            ->with('success', trans('api-limiter::admin.messages.settings_saved'));
    }
    
    /**
     * Get API logs from the single log file.
     *
     * @return array
     */
    private function getApiLogs()
    {
        $logs = [];
        $logFile = storage_path('logs/api-limiter-logs.log');
        
        if (!File::exists($logFile)) {
            return $logs;
        }
        
        try {
            $content = File::get($logFile);
            
            // Handle multiline logs - normalize content by joining broken lines
            $normalizedContent = preg_replace('/\n(?!\[)/', ' ', $content);
            $lines = explode("\n", $normalizedContent);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                
                $log = $this->parseLogLine(trim($line));
                if ($log) {
                    $logs[] = $log;
                }
            }
            
            // Sort by date descending (newest first)
            usort($logs, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
            
        } catch (\Exception $e) {
            \Log::error('ApiLimiter: Failed to read log file', [
                'error' => $e->getMessage(),
                'file' => $logFile
            ]);
        }
        
        return $logs;
    }
    
    /**
     * Parse a single log line.
     *
     * @param string $line
     * @return array|null
     */
    private function parseLogLine(string $line): ?array
    {
        try {
            // Updated regex to handle production.INFO format and optional trailing []
            // Format: [2025-06-22 07:19:35.413] production.INFO: API Request {"ip":"192.168.0.21",...} 
            if (!preg_match('/\[([^\]]+)\]\s+\w+\.(\w+):\s+(API Request)\s+({[^}]+})\s*(?:\[\])?/', $line, $matches)) {
                return null;
            }
            
            $date = $matches[1];
            $level = strtolower($matches[2]);
            $message = trim($matches[3]);
            $jsonData = $matches[4];
            
            // Parse JSON data
            $data = json_decode($jsonData, true);
            if (!$data) {
                return null;
            }
            
            return [
                'date' => $date,
                'level' => $level,
                'message' => $message,
                'ip' => $data['ip'] ?? 'unknown',
                'method' => $data['method'] ?? 'unknown',
                'route' => $data['route'] ?? 'unknown',
                'uri' => $data['uri'] ?? 'unknown',
                'status' => $data['status'] ?? 'unknown',
                'reason' => $data['reason'] ?? 'unknown',
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }
} 