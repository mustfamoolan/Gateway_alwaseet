<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    protected \App\Services\WaseetService $waseetService;

    public function __construct(\App\Services\WaseetService $waseetService)
    {
        $this->waseetService = $waseetService;
    }

    /**
     * Get the project attached by middleware.
     */
    protected function getProject(Request $request): \App\Models\Project
    {
        return $request->attributes->get('project');
    }

    /**
     * POST /api/gateway/connect-waseet
     * Allow client to update their Waseet credentials
     */
    public function connectWaseet(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $project = $this->getProject($request);

        // Update temporarily to test login
        $project->waseet_username = $request->username;
        $project->waseet_password = $request->password;

        $token = $this->waseetService->login($project);

        if ($token) {
            $project->save(); // Save permanently if login successful
            return response()->json([
                'status' => true,
                'msg' => 'Successfully connected to Al-Waseet account',
                'data' => ['merchant_username' => $project->waseet_username]
            ]);
        }

        return response()->json([
            'status' => false,
            'msg' => 'Failed to connect. Please check your Al-Waseet credentials.',
        ], 422);
    }

    /**
     * POST /api/gateway/create-order
     */
    public function createOrder(Request $request)
    {
        $project = $this->getProject($request);
        $response = $this->waseetService->createOrder($project, $request->all());
        
        return response()->json($response);
    }

    /**
     * POST /api/gateway/edit-order
     */
    public function editOrder(Request $request)
    {
        $project = $this->getProject($request);
        $response = $this->waseetService->editOrder($project, $request->all());
        
        return response()->json($response);
    }

    /**
     * GET /api/gateway/order-status/{id}
     */
    public function getOrderStatus(Request $request, $id)
    {
        $project = $this->getProject($request);
        $response = $this->waseetService->getOrderStatus($project, $id);
        
        return response()->json($response);
    }

    /**
     * GET /api/gateway/cities
     */
    public function getCities()
    {
        $response = $this->waseetService->fetchSupplementaryData('cities');
        return response()->json($response);
    }

    /**
     * GET /api/gateway/regions
     */
    public function getRegions(Request $request)
    {
        $response = $this->waseetService->fetchSupplementaryData('regions', $request->only('city_id'));
        return response()->json($response);
    }

    /**
     * GET /api/gateway/package-sizes
     */
    public function getPackageSizes()
    {
        $response = $this->waseetService->fetchSupplementaryData('package_sizes');
        return response()->json($response);
    }
}
