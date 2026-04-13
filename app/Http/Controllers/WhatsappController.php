<?php

namespace App\Http\Controllers;

use App\Models\WaProject;
use App\Models\WaMessage;
use App\Services\WhatsappApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WhatsappController extends Controller
{
    protected WhatsappApiService $waService;

    public function __construct(WhatsappApiService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Dashboard: List all WhatsApp projects.
     */
    public function index()
    {
        $projects = WaProject::latest()->get();
        return view('whatsapp.index', compact('projects'));
    }

    /**
     * Create a new project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
        ]);

        $project = WaProject::create([
            'user_id' => null,
            'name' => $request->name,
            'owner_name' => $request->owner_name,
            'status' => 'pending',
        ]);

        return redirect()->route('whatsapp.show', $project->id);
    }

    /**
     * Display the QR code and link status.
     */
    public function show(WaProject $project)
    {
        $sessionStatus = $this->waService->getSessionStatus("project_{$project->id}");
        
        // Update local status if it changed in the engine
        if (isset($sessionStatus['status']) && $sessionStatus['status'] !== $project->status) {
            $project->update(['status' => $sessionStatus['status']]);
        }

        // Diagnostic: If engine returns error, let the view know why
        $error = $sessionStatus['status'] === 'error' ? ($sessionStatus['message'] ?? 'Unknown Error') : null;

        return view('whatsapp.show', [
            'project' => $project,
            'qr' => $sessionStatus['qr'] ?? null,
            'status' => $sessionStatus['status'] ?? 'disconnected',
            'error' => $error
        ]);
    }

    /**
     * API Endpoint for external apps to send messages.
     * POST /api/v1/whatsapp/send
     */
    public function apiSendMessage(Request $request)
    {
        $apiKey = $request->header('X-WA-API-KEY');
        if (!$apiKey) return response()->json(['error' => 'API Key required'], 401);

        $project = WaProject::where('api_key', $apiKey)->first();
        if (!$project) return response()->json(['error' => 'Invalid API Key'], 401);
        if ($project->status !== 'connected') return response()->json(['error' => 'WhatsApp not connected'], 400);

        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->waService->sendMessage("project_{$project->id}", $request->to, $request->message);

        // Log the message
        WaMessage::create([
            'wa_project_id' => $project->id,
            'to_number' => $request->to,
            'message_body' => $request->message,
            'direction' => 'outbound',
            'status' => isset($result['success']) ? 'sent' : 'failed',
            'error_message' => $result['error'] ?? null,
            'response_metadata' => $result
        ]);

        return response()->json($result);
    }

    /**
     * Remove / Logout session.
     */
    public function destroy(WaProject $project)
    {
        $this->waService->deleteSession("project_{$project->id}");
        $project->delete();

        return redirect()->route('whatsapp.index')->with('success', 'Project deleted');
    }

    /**
     * Webhook for incoming messages from the engine.
     */
    public function webhook(Request $request)
    {
        $sessionId = $request->sessionId; // e.g., project_1
        $projectId = str_replace('project_', '', $sessionId);
        
        $project = WaProject::find($projectId);
        if (!$project) return response()->json(['error' => 'Project not found'], 404);

        WaMessage::create([
            'wa_project_id' => $project->id,
            'from_number' => $request->from,
            'to_number' => 'me', // The project number
            'message_body' => $request->text,
            'direction' => 'inbound',
            'status' => 'read',
            'response_metadata' => $request->metadata
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Fetch last messages for the chat UI.
     */
    public function getMessages(WaProject $project)
    {
        $messages = WaMessage::where('wa_project_id', $project->id)
            ->latest()
            ->limit(30)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Handle test send from the UI.
     */
    public function testSend(Request $request, WaProject $project)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->waService->sendMessage("project_{$project->id}", $request->to, $request->message);

        // Log the message
        WaMessage::create([
            'wa_project_id' => $project->id,
            'to_number' => $request->to,
            'message_body' => $request->message,
            'direction' => 'outbound',
            'status' => isset($result['success']) ? 'sent' : 'failed',
            'error_message' => $result['error'] ?? null,
            'response_metadata' => $result
        ]);

        return response()->json($result);
    }
}
