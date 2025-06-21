<?php

namespace Azuriom\Plugin\ApiLimiter\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
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
        
        // Get all API Limiter log files
        $logFiles = glob(storage_path('logs/api-limiter-*.log'));
        
        // Security check: only API Limiter log files
        $logFiles = array_filter($logFiles, function($file) {
            return preg_match('/api-limiter-\d{4}-\d{2}-\d{2}\.log$/', basename($file));
        });
        
        foreach ($logFiles as $logFile) {
            if (File::exists($logFile)) {
                File::delete($logFile);
            }
        }
        
        return redirect()->route('api-limiter.admin.logs')
            ->with('success', trans('api-limiter::admin.logs.logs_cleared'));
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
        
        // Get the most recent log file
        $logFiles = glob(storage_path('logs/api-limiter-*.log'));
        
        if (empty($logFiles)) {
            return redirect()->route('api-limiter.admin.logs')
                ->with('error', trans('api-limiter::admin.logs.log_file_not_found'));
        }
        
        // Security check: only API Limiter log files
        $logFiles = array_filter($logFiles, function($file) {
            return preg_match('/api-limiter-\d{4}-\d{2}-\d{2}\.log$/', basename($file));
        });
        
        if (empty($logFiles)) {
            return redirect()->route('api-limiter.admin.logs')
                ->with('error', 'No valid log files found');
        }
        
        // Sort and get the newest file
        rsort($logFiles);
        $latestLogFile = $logFiles[0];
        
        $filename = 'api-limiter-' . date('Y-m-d-H-i-s') . '.log';
        
        return response()->download($latestLogFile, $filename);
    }
    
    /**
     * Get API logs from log files.
     *
     * @return array
     */
    private function getApiLogs()
    {
        $logs = [];
        
        // Get all API Limiter log files (daily rotation)
        $logFiles = glob(storage_path('logs/api-limiter-*.log'));
        
        // Security check: only API Limiter log files
        $logFiles = array_filter($logFiles, function($file) {
            return preg_match('/api-limiter-\d{4}-\d{2}-\d{2}\.log$/', basename($file));
        });
        
        // Sort files by date (newest first)
        rsort($logFiles);
        
        foreach ($logFiles as $logPath) {
            if (!File::exists($logPath)) {
                continue;
            }
            
            $content = File::get($logPath);
            
            // Handle multiline log entries - join lines that don't start with [
            $normalizedContent = preg_replace('/\n(?!\[)/', '', $content);
            $lines = explode("\n", $normalizedContent);
            
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }
                
                // Parse Laravel log format for API Request logs (with milliseconds)
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3})\] \w+\.(\w+): API Request (\{.+\})\s*$/', trim($line), $matches)) {
                    $datetime = $matches[1];
                    $level = strtoupper($matches[2]);
                    $contextJson = $matches[3];
                    
                    // Parse JSON context
                    $context = json_decode($contextJson, true);
                    if ($context) {
                        $logs[] = [
                            'date' => $datetime,
                            'level' => $level,
                            'ip' => $context['ip'] ?? 'unknown',
                            'method' => $context['method'] ?? 'unknown',
                            'route' => $context['route'] ?? 'unknown',
                            'uri' => $context['uri'] ?? 'unknown',
                            'status' => $context['status'] ?? 'unknown',
                            'reason' => $context['reason'] ?? 'unknown',
                            'raw' => $line,
                        ];
                    }
                }
            }
        }
        
        // Sort by date (newest first)
        usort($logs, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        return $logs;
    }
} 