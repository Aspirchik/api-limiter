<?php

namespace Azuriom\Plugin\ApiLimiter\Console\Commands;

use Azuriom\Plugin\ApiLimiter\Models\LimiterSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CleanupLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-limiter:cleanup-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old API Limiter log files based on auto_cleanup_logs setting';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $cleanupPeriod = LimiterSetting::getValue('auto_cleanup_logs', '1_week');
            
            // Convert period to Carbon date
            $cutoffDate = $this->getCutoffDate($cleanupPeriod);
            
            if (!$cutoffDate) {
                $this->error("Invalid cleanup period: {$cleanupPeriod}");
                return 1;
            }
            
            // Get all API Limiter log files
            $logFiles = glob(storage_path('logs/api-limiter-*.log'));
            
            // Security check: only API Limiter log files
            $logFiles = array_filter($logFiles, function($file) {
                return preg_match('/api-limiter-\d{4}-\d{2}-\d{2}\.log$/', basename($file));
            });
            
            $deletedCount = 0;
            $totalSize = 0;
            
            foreach ($logFiles as $logFile) {
                if (!File::exists($logFile)) {
                    continue;
                }
                
                // Extract date from filename (api-limiter-YYYY-MM-DD.log)
                if (preg_match('/api-limiter-(\d{4}-\d{2}-\d{2})\.log$/', basename($logFile), $matches)) {
                    $fileDate = Carbon::createFromFormat('Y-m-d', $matches[1]);
                    
                    // Delete if file is older than cutoff date
                    if ($fileDate->lt($cutoffDate)) {
                        $fileSize = File::size($logFile);
                        $totalSize += $fileSize;
                        
                        File::delete($logFile);
                        $deletedCount++;
                        
                        $this->info("Deleted: {$logFile} ({$this->formatBytes($fileSize)})");
                    }
                }
            }
            
            if ($deletedCount > 0) {
                $this->info("Cleanup completed: {$deletedCount} files deleted, {$this->formatBytes($totalSize)} freed");
            } else {
                $this->info("No files to cleanup (period: {$cleanupPeriod})");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Cleanup failed: " . $e->getMessage());
            \Log::error('ApiLimiter: Log cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Get cutoff date based on cleanup period.
     *
     * @param string $period
     * @return \Carbon\Carbon|null
     */
    protected function getCutoffDate(string $period): ?Carbon
    {
        $now = Carbon::now();
        
        return match($period) {
            '1_hour' => $now->subHour(),
            '3_hours' => $now->subHours(3),
            '6_hours' => $now->subHours(6),
            '12_hours' => $now->subHours(12),
            '1_day' => $now->subDay(),
            '3_days' => $now->subDays(3),
            '1_week' => $now->subWeek(),
            '2_weeks' => $now->subWeeks(2),
            '1_month' => $now->subMonth(),
            '3_months' => $now->subMonths(3),
            '6_months' => $now->subMonths(6),
            '1_year' => $now->subYear(),
            default => null,
        };
    }
    
    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 