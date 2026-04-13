<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display a listing of request logs.
     */
    public function index(Request $request)
    {
        $this->checkSecret($request);

        $logs = \App\Models\RequestLog::with('project')
            ->latest()
            ->paginate(25);
            
        return view('logs.index', compact('logs'));
    }

    /**
     * Clear all request logs.
     */
    public function clear(Request $request)
    {
        $this->checkSecret($request);

        \App\Models\RequestLog::truncate();

        return redirect()->route('logs.index', ['secret' => $request->query('secret')])
            ->with('success', 'All logs have been cleared.');
    }

    /**
     * Primitive check for a secret key to protect logs.
     */
    protected function checkSecret(Request $request)
    {
        $secret = env('WASEET_LOG_SECRET');
        
        // If secret is set in .env, require it in the request
        if ($secret && $request->query('secret') !== $secret) {
            abort(403, 'Unauthorized. Please provide a valid secret key.');
        }
    }
}
