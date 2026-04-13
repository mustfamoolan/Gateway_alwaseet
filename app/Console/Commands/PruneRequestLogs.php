<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Prune request logs older than 24 hours to prevent storage bloat.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting log pruning...');
        
        $count = RequestLog::where('created_at', '<', now()->subHours(24))->delete();
        
        $this->info("Successfully deleted {$count} logs older than 24 hours.");
        Log::info("Automated log pruning: Deleted {$count} logs.");
        
        return Command::SUCCESS;
    }
}
