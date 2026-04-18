<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class SyncWaseetStatus extends Command
{
    protected $signature = 'waseet:sync-status';
    protected $description = 'Sync active order statuses from Al-Waseet and notify client projects';

    protected \App\Services\WaseetService $waseetService;

    public function __construct(\App\Services\WaseetService $waseetService)
    {
        parent::__construct();
        $this->waseetService = $waseetService;
    }

    public function handle()
    {
        $this->info("Starting Waseet Status Sync (Bulk Mode)...");

        $terminalStatuses = ['واصل', 'راجع', 'مباع', 'تم استلام الراجع', 'تم تسليم المبالغ', 'ملغي', 'إيداع راجع'];

        $activeOrdersByProject = \App\Models\WaseetOrder::where('is_terminal', false)
            ->with('project')
            ->get()
            ->groupBy('project_id');

        foreach ($activeOrdersByProject as $projectId => $orders) {
            $project = $orders->first()->project;
            if (!$project || !$project->is_active) continue;

            $this->info("Syncing " . count($orders) . " orders for project: {$project->name}");

            // Al-Waseet supports bulk by comma-separated IDs
            $ids = $orders->pluck('waseet_order_id')->join(',');
            
            try {
                $response = $this->waseetService->getOrderStatus($project, $ids);
                
                // Response should be an array of orders in 'data'
                $waseetOrders = $response['data'] ?? [];
                
                // If not an array but a single object, wrap it
                if (!is_array($waseetOrders) || (isset($waseetOrders['qr_id']) || isset($waseetOrders['order_id']))) {
                    $waseetOrders = [$waseetOrders];
                }

                foreach ($waseetOrders as $waseetData) {
                    $waseetId = $waseetData['qr_id'] ?? $waseetData['order_id'] ?? null;
                    if (!$waseetId) continue;

                    $order = $orders->firstWhere('waseet_order_id', $waseetId);
                    if (!$order) continue;

                    $newStatus = $waseetData['status_name'] ?? $waseetData['status'] ?? null;
                    
                    if ($newStatus && $newStatus !== $order->last_status) {
                        $this->info("Order {$waseetId} changed: {$order->last_status} -> {$newStatus}");
                        
                        $oldStatus = $order->last_status;
                        $isTerminal = in_array($newStatus, $terminalStatuses);

                        $order->update([
                            'last_status' => $newStatus,
                            'is_terminal' => $isTerminal
                        ]);

                        if ($project->callback_url) {
                            $this->notifyProject($project, $order, $oldStatus, $newStatus, $waseetData);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Bulk sync failed for project {$project->name}: " . $e->getMessage());
            }
        }

        $this->info("Bulk Sync Completed.");
    }

    protected function notifyProject($project, $order, $oldStatus, $newStatus, $waseetData)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'X-API-KEY' => $project->api_key, // Security
                    'Accept' => 'application/json',
                ])
                ->post($project->callback_url, [
                    'order_id' => $order->waseet_order_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'full_data' => $waseetData,
                    'timestamp' => now()->toIso8601String(),
                ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error("Webhook failed for order {$order->waseet_order_id} to {$project->callback_url}. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Webhook exception for order {$order->waseet_order_id}: " . $e->getMessage());
        }
    }
}
