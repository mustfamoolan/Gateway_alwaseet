<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display a listing of request logs.
     */
    public function index()
    {
        $logs = \App\Models\RequestLog::with('project')
            ->latest()
            ->paginate(25);
            
        return view('logs.index', compact('logs'));
    }
}
