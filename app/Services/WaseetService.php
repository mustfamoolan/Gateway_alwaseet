<?php

namespace App\Services;

use App\Models\Project;
use App\Models\RequestLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaseetService
{
    protected string $baseUrl = 'https://api.alwaseet-iq.net';

    /**
     * Get or refresh token for a project.
     */
    public function getToken(Project $project): ?string
    {
        // Check if token exists and is not too old (e.g., 24 hours)
        if ($project->waseet_token && $project->waseet_token_refresh_at && $project->waseet_token_refresh_at->gt(now()->subHours(24))) {
            return $project->waseet_token;
        }

        return $this->login($project);
    }

    /**
     * Login to Waseet and store token.
     */
    public function login(Project $project): ?string
    {
        try {
            $response = Http::asMultipart()->post("{$this->baseUrl}/v1/merchant/login", [
                'username' => $project->waseet_username,
                'password' => $project->waseet_password,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? false)) {
                $token = $data['data']['token'] ?? null;
                if ($token) {
                    $project->update([
                        'waseet_token' => $token,
                        'waseet_token_refresh_at' => now(),
                    ]);
                    return $token;
                }
            }

            Log::error("Waseet Login Response Failed for project {$project->name}: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Waseet Login Exception for project {$project->name}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Send order creation request.
     */
    public function createOrder(Project $project, array $params)
    {
        return $this->authenticatedRequest($project, 'POST', '/v1/merchant/create-order', $params);
    }

    /**
     * Send order edit request.
     */
    public function editOrder(Project $project, array $params)
    {
        return $this->authenticatedRequest($project, 'POST', '/v1/merchant/edit-order', $params);
    }

    /**
     * Get order status.
     */
    public function getOrderStatus(Project $project, string $orderId)
    {
        return $this->authenticatedRequest($project, 'GET', "/v1/merchant/get-orders-by-ids-bulk", ['ids' => $orderId]);
    }

    /**
     * Fetch all auxiliary data (cities, regions, package sizes).
     */
    public function fetchSupplementaryData(string $type, array $params = [])
    {
        $endpoint = match($type) {
            'cities' => '/v1/merchant/citys',
            'regions' => '/v1/merchant/regions',
            'package_sizes' => '/v1/merchant/package-sizes',
            default => null
        };

        if (!$endpoint) return null;

        $response = Http::asMultipart()->get("{$this->baseUrl}{$endpoint}", $params);
        return $response->json();
    }

    /**
     * Helper for authenticated requests with logging and auto-retry on 401.
     */
    protected function authenticatedRequest(Project $project, string $method, string $endpoint, array $params = [], bool $isRetry = false)
    {
        $token = $this->getToken($project);
        if (!$token) {
            return ['status' => false, 'msg' => 'Authentication failed to Waseet API'];
        }

        $url = "{$this->baseUrl}{$endpoint}?token={$token}";
        $request = Http::asMultipart();
        
        try {
            if ($method === 'POST') {
                $response = $request->post($url, $params);
            } else {
                $response = $request->get($url, $params);
            }

            $data = $response->json();

            // Handle "Unauthorized" or specific error code 21 from Al-Waseet
            // msg: "ليس لديك صلاحية الوصول"
            if (!$isRetry && ( ($data['errNum'] ?? 0) == 21 || ($data['status'] ?? true) === false && ($data['msg'] ?? '') == 'ليس لديك صلاحية الوصول') ) {
                Log::warning("Waseet Token (ID: {$project->id}) might be invalid. Retrying with fresh login...");
                
                // Force a fresh login
                $newToken = $this->login($project);
                if ($newToken) {
                    return $this->authenticatedRequest($project, $method, $endpoint, $params, true);
                }
            }

            $this->logRequest($project, $endpoint, $params, $response);

            return $data;
        } catch (\Exception $e) {
            Log::error("Waseet Request Exception for project {$project->name} [{$endpoint}]: " . $e->getMessage());
            return ['status' => false, 'msg' => 'Exception occurred during Waseet request: ' . $e->getMessage()];
        }
    }

    /**
     * Log request and response to database.
     */
    protected function logRequest(Project $project, string $endpoint, array $request, $response)
    {
        try {
            $data = $response->json();
            RequestLog::create([
                'project_id' => $project->id,
                'endpoint' => $endpoint,
                'request_payload' => $request,
                'response_payload' => $data,
                'status' => $response->successful() && ($data['status'] ?? false) ? 'success' : 'failed',
                'http_status_code' => $response->status(),
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log Waseet request: " . $e->getMessage());
        }
    }
}
