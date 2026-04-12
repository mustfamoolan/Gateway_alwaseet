<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.engine_url', env('WHATSAPP_ENGINE_URL', 'http://whatsapp-engine:3000'));
    }

    /**
     * Get the session status and QR code if pending.
     */
    public function getSessionStatus(string $sessionId)
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/session/{$sessionId}");
            return $response->json();
        } catch (\Exception $e) {
            Log::error("WhatsApp Engine Error (getSessionStatus): " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Engine unavailable'];
        }
    }

    /**
     * Send a text message through the engine.
     */
    public function sendMessage(string $sessionId, string $to, string $text)
    {
        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/message/send", [
                'sessionId' => $sessionId,
                'to' => $to,
                'text' => $text
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("WhatsApp Engine Error (sendMessage): " . $e->getMessage());
            return ['error' => 'Engine unavailable'];
        }
    }

    /**
     * Delete a session (Logout).
     */
    public function deleteSession(string $sessionId)
    {
        try {
            $response = Http::timeout(10)->delete("{$this->baseUrl}/session/{$sessionId}");
            return $response->json();
        } catch (\Exception $e) {
            Log::error("WhatsApp Engine Error (deleteSession): " . $e->getMessage());
            return ['error' => 'Engine unavailable'];
        }
    }
}
