<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index()
    {
        $projects = \App\Models\Project::latest()->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Store a new project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'waseet_username' => 'required|string',
            'waseet_password' => 'required|string',
        ]);

        \App\Models\Project::create([
            'name' => $request->name,
            'api_key' => 'gw_' . \Illuminate\Support\Str::random(32),
            'waseet_username' => $request->waseet_username,
            'waseet_password' => $request->waseet_password, // For production, encryption is recommended
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    /**
     * Toggle project status.
     */
    public function toggle(\App\Models\Project $project)
    {
        $project->update(['is_active' => !$project->is_active]);
        return back()->with('success', 'Project status updated!');
    }

    /**
     * Remove the project.
     */
    public function destroy(\App\Models\Project $project)
    {
        $project->delete();
        return back()->with('success', 'Project removed!');
    }
}
