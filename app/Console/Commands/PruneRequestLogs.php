<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestLog;
use App\Models\WaMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class PruneRequestLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune database logs and file logs older than a week (or configured days) to prevent storage bloat.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting log pruning...');
        
        $days = (int) env('LOG_PRUNE_DAYS', 7);
        $thresholdDate = now()->subDays($days);
        
        // 1. Prune database request logs
        $countLogs = RequestLog::where('created_at', '<', $thresholdDate)->delete();
        $this->info("Successfully deleted {$countLogs} database request logs older than {$days} days.");
        
        // 2. Prune database WhatsApp message logs
        $countMessages = WaMessage::where('created_at', '<', $thresholdDate)->delete();
        $this->info("Successfully deleted {$countMessages} database WhatsApp message logs older than {$days} days.");
        
        // 3. Clean file logs in storage/logs
        $logPath = storage_path('logs');
        $deletedFilesCount = 0;
        if (File::exists($logPath)) {
            foreach (File::files($logPath) as $file) {
                if ($file->getExtension() === 'log') {
                    if ($file->getFilename() === 'laravel.log') {
                        // Truncate the huge single laravel.log file
                        File::put($file->getPathname(), '');
                        $deletedFilesCount++;
                        $this->info("Truncated single log file: laravel.log");
                    } elseif ($file->getMTime() < $thresholdDate->getTimestamp()) {
                        File::delete($file->getPathname());
                        $deletedFilesCount++;
                        $this->info("Deleted old daily log file: " . $file->getFilename());
                    }
                }
            }
        }
        $this->info("Successfully cleaned {$deletedFilesCount} log files.");

        Log::info("Automated log pruning: Deleted {$countLogs} request logs, {$countMessages} message logs, and cleaned {$deletedFilesCount} file logs.");
        
        return Command::SUCCESS;
    }
}
