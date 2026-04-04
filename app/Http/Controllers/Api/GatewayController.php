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
