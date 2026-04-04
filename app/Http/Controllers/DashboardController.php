<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            'projects_count' => \App\Models\Project::count(),
            'active_projects' => \App\Models\Project::where('is_active', true)->count(),
            'total_requests' => \App\Models\RequestLog::count(),
            'requests_today' => \App\Models\RequestLog::whereDate('created_at', today())->count(),
        ];

        // Fetch Server Public IP
        $serverIp = cache()->remember('server_public_ip', now()->addDay(), function () {
            try {
                $response = \Illuminate\Support\Facades\Http::get('https://ifconfig.me/ip');
                return $response->successful() ? trim($response->body()) : 'Unknown';
            } catch (\Exception $e) {
                return 'Error';
            }
        });

        // Get recent logs
        $recentLogs = \App\Models\RequestLog::with('project')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'serverIp' => $serverIp,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Test connection to Al-Waseet API
     */
    public function testConnection()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get('https://api.alwaseet-iq.net/v1/merchant/citys');
            
            if ($response->successful()) {
                $data = $response->json();
                $count = count($data['data'] ?? []);
                return response()->json([
                    'success' => true,
                    'message' => "Connection successful! Verified by fetching {$count} cities."
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Connected, but Al-Waseet returned status: " . $response->status()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Connection failed: " . $e->getMessage()
            ]);
        }
    }
}
