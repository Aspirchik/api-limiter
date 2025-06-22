<?php

namespace Azuriom\Plugin\ApiLimiter\Console\Commands;

use Azuriom\Plugin\ApiLimiter\Support\LogCleaner;
use Illuminate\Console\Command;

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
    protected $description = 'Clean up old API Limiter log entries based on auto_cleanup_logs setting';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $logCleaner = new LogCleaner();
            $result = $logCleaner->cleanOldEntries();
            
            if (!$result['success']) {
                $this->error("Cleanup failed: " . ($result['message'] ?? 'Unknown error'));
                return 1;
            }
            
            if ($result['deleted_entries'] > 0) {
                $this->info("Cleanup completed successfully:");
                $this->info("- Deleted entries: {$result['deleted_entries']}");
                $this->info("- Space freed: " . $logCleaner->formatBytes($result['space_freed'] ?? 0));
                $this->info("- Cutoff date: {$result['cutoff_date']}");
                $this->info("- Cleanup period: {$result['cleanup_period']}");
            } else {
                $this->info("No entries to cleanup (period: {$result['cleanup_period']})");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Cleanup failed: " . $e->getMessage());
            \Log::error('ApiLimiter: Log cleanup command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 