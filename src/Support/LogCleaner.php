<?php

namespace Azuriom\Plugin\ApiLimiter\Support;

use Azuriom\Plugin\ApiLimiter\Models\LimiterSetting;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class LogCleaner
{
    /**
     * The log file path.
     *
     * @var string
     */
    protected $logFile;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->logFile = storage_path('logs/api-limiter-logs.log');
    }

    /**
     * Clean old log entries from the single log file based on timestamp.
     *
     * @return array
     */
    public function cleanOldEntries(): array
    {
        try {
            if (!File::exists($this->logFile)) {
                return [
                    'success' => true,
                    'deleted_entries' => 0,
                    'file_size_before' => 0,
                    'file_size_after' => 0,
                    'message' => 'Log file does not exist'
                ];
            }

            $cleanupPeriod = LimiterSetting::getValue('auto_cleanup_logs', '1_week');
            $cutoffDate = $this->getCutoffDate($cleanupPeriod);

            if (!$cutoffDate) {
                return [
                    'success' => false,
                    'message' => "Invalid cleanup period: {$cleanupPeriod}"
                ];
            }

            $originalSize = File::size($this->logFile);
            $content = File::get($this->logFile);
            
            // Find all log entries by timestamp pattern
            $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3})\]\s+\w+\.\w+:\s+API Request\s+\{[^}]*\}/';
            $keptEntries = [];
            $deletedCount = 0;

            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $timestamp = $match[1][0];
                    $fullMatch = $match[0][0];
                    
                    try {
                        $logDate = Carbon::createFromFormat('Y-m-d H:i:s.v', $timestamp);
                        
                        // Keep entry if it's newer than cutoff date
                        if ($logDate->gte($cutoffDate)) {
                            $keptEntries[] = $fullMatch;
                        } else {
                            $deletedCount++;
                        }
                    } catch (\Exception $e) {
                        // If we can't parse the date, keep the entry to be safe
                        $keptEntries[] = $fullMatch;
                    }
                }
            } else {
                // No matches found, keep original content
                $keptEntries[] = $content;
            }

            // Write cleaned content back to file
            $newContent = implode("\n", $keptEntries);
            File::put($this->logFile, $newContent);
            
            $newSize = File::size($this->logFile);

            return [
                'success' => true,
                'deleted_entries' => $deletedCount,
                'file_size_before' => $originalSize,
                'file_size_after' => $newSize,
                'space_freed' => $originalSize - $newSize,
                'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                'cleanup_period' => $cleanupPeriod
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Clean logs when adding new entries (called from middleware).
     *
     * @return void
     */
    public function cleanOnNewEntry(): void
    {
        try {
            // Only clean if logging is enabled
            if (!LimiterSetting::getValue('logging_enabled', true)) {
                return;
            }

            // Always check by time, not file size
            // Run cleanup randomly (1 in 5 chance) to avoid performance impact
            if (rand(1, 5) === 1) {
                $this->cleanOldEntries();
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('ApiLimiter: Failed to clean logs on new entry', [
                'error' => $e->getMessage()
            ]);
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
    public function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 