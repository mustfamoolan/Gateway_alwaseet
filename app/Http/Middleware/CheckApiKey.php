<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json(['status' => false, 'msg' => 'API Key is missing'], 401);
        }

        $project = \App\Models\Project::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$project) {
            return response()->json(['status' => false, 'msg' => 'Invalid or inactive API Key'], 401);
        }

        // Attach project to request
        $request->attributes->add(['project' => $project]);

        return $next($request);
    }
}
