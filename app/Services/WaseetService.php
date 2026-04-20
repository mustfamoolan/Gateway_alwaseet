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
            // Some API versions prefer standard Form-Params for login
            $response = Http::asForm()->post("{$this->baseUrl}/v1/merchant/login", [
                'username' => $project->waseet_username,
                'password' => $project->waseet_password,
            ]);

            $data = $response->json();

            // S000 is success in Al-Waseet
            if ($response->successful() && (($data['status'] ?? false) === true || ($data['errNum'] ?? '') === 'S000')) {
                $token = $data['data']['token'] ?? null;
                if ($token) {
                    Log::info("Waseet Login Successful for project {$project->name}.");
                    $project->update([
                        'waseet_token' => $token,
                        'waseet_token_refresh_at' => now(),
                    ]);
                    return $token;
                }
            }

            Log::error("Waseet Login Failed for project {$project->name}. Response: " . $response->body());
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
        $response = $this->authenticatedRequest($project, 'POST', '/v1/merchant/create-order', $params);
        
        // Track the order if successful
        if (($response['status'] ?? false) === true) {
            $orderId = $response['data']['qr_id'] ?? $response['data']['order_id'] ?? null;
            if ($orderId) {
                \App\Models\WaseetOrder::updateOrCreate(
                    ['waseet_order_id' => $orderId, 'project_id' => $project->id],
                    ['last_status' => 'قيد المعالجة', 'is_terminal' => false]
                );
            }
        }

        return $response;
    }

    /**
     * Send order edit request.
     */
    public function editOrder(Project $project, array $params)
    {
        return $this->authenticatedRequest($project, 'POST', '/v1/merchant/edit-order', $params);
    }

    /**
     * Send order cancellation/delete request.
     * Note: This endpoint is unlisted in docs, attempting standard pattern.
     */
    public function cancelOrder(Project $project, string $orderId)
    {
        return $this->authenticatedRequest($project, 'POST', '/v1/merchant/delete-order', ['qr_id' => $orderId]);
    }

    /**
     * Get order status.
     */
    public function getOrderStatus(Project $project, string $orderId)
    {
        $response = $this->authenticatedRequest($project, 'GET', "/v1/merchant/get-orders-by-ids-bulk", ['ids' => $orderId]);
        
        // Auto-Discovery: If Al-Waseet returns status for an untracked order, start tracking it
        if (($response['status'] ?? false) === true) {
            $waseetOrders = $response['data'] ?? [];
            if (!is_array($waseetOrders) || (isset($waseetOrders['qr_id']) || isset($waseetOrders['order_id']))) {
                $waseetOrders = [$waseetOrders];
            }

            foreach ($waseetOrders as $waseetData) {
                $qrId = $waseetData['qr_id'] ?? $waseetData['order_id'] ?? null;
                if ($qrId) {
                    \App\Models\WaseetOrder::updateOrCreate(
                        ['waseet_order_id' => $qrId, 'project_id' => $project->id],
                        [
                            'last_status' => $waseetData['status_name'] ?? $waseetData['status'] ?? 'قيد المعالجة',
                            'is_terminal' => false // Will be updated by sync command if terminal
                        ]
                    );
                }
            }
        }

        return $response;
    }

    /**
     * Fetch all available statuses from Al-Waseet API.
     */
    public function fetchStatuses(Project $project)
    {
        return $this->authenticatedRequest($project, 'GET', '/v1/merchant/statuses');
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

        // Official AlWaseet Documentation V2.3: token MUST be a Query Parameter
        // NOTE: We also add it to $params just in case the server expects it in the body too
        $url = "{$this->baseUrl}{$endpoint}?token={$token}";
        $params['token'] = $token; 
        
        // Minimize headers to avoid 400 errors from strict AlWaseet servers
        $request = Http::asMultipart()->withHeaders([
            'Accept' => 'application/json',
        ]);
        
        try {
            if ($method === 'POST') {
                // Ensure strict integer types for Waseet compatibility
                foreach (['city_id', 'region_id', 'items_number', 'price', 'package_size'] as $key) {
                    if (isset($params[$key])) {
                        $params[$key] = (int) $params[$key];
                    }
                }

                // Log payload for debugging (sanitized)
                Log::debug("Waseet Request Payload [{$endpoint}]: " . json_encode($params));
                $response = $request->post($url, $params);
            } else {
                $response = $request->get($url, $params);
            }

            $data = $response->json();

            if (!$response->successful()) {
                Log::error("Waseet API Error Response [{$endpoint}]: Status Code {$response->status()}, Body: " . $response->body());
            }

            // Handle "Unauthorized" or specific error code 21
            if (!$isRetry && ( ($data['errNum'] ?? 0) == 21 || ($data['status'] ?? true) === false && ($data['msg'] ?? '') == 'ليس لديك صلاحية الوصول') ) {
                Log::warning("Waseet Token (ID: {$project->id}) invalid. Retrying with fresh login...");
                
                $newToken = $this->login($project);
                if ($newToken) {
                    return $this->authenticatedRequest($project, $method, $endpoint, $params, true);
                }
            }

            $this->logRequest($project, $endpoint, $params, $response);

            return $data;
        } catch (\Exception $e) {
            Log::error("Waseet Request Exception for project {$project->name} [{$endpoint}]: " . $e->getMessage(), [
                'params' => $params,
                'trace' => $e->getTraceAsString()
            ]);
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
            $isSuccess = $response->successful() && ($data['status'] ?? false);
            
            // Check if we should skip logging this successful request
            if ($isSuccess && !config('services.waseet.log_successful_requests', env('WASEET_LOG_SUCCESSFUL_REQUESTS', false))) {
                // Skip read-only/frequent endpoints if successful
                $skipEndpoints = [
                    '/v1/merchant/get-orders-by-ids-bulk',
                    '/v1/merchant/statuses',
                    '/v1/merchant/citys',
                    '/v1/merchant/regions',
                    '/v1/merchant/package-sizes'
                ];
                
                if (in_array($endpoint, $skipEndpoints)) {
                    return;
                }
            }

            // Sanitize Payloads (Remove Tokens)
            if (isset($request['token'])) {
                 $request['token'] = '********';
            }
            if (isset($data['data']['token'])) {
                 $data['data']['token'] = '********';
            }

            RequestLog::create([
                'project_id' => $project->id,
                'endpoint' => $endpoint,
                'request_payload' => $request,
                'response_payload' => $data,
                'status' => $isSuccess ? 'success' : 'failed',
                'http_status_code' => $response->status(),
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log Waseet request: " . $e->getMessage());
        }
    }
}
