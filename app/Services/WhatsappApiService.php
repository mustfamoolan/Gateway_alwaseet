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
            $url = "{$this->baseUrl}/session/{$sessionId}";
            $response = Http::timeout(10)->get($url);
            return $response->json();
        } catch (\Exception $e) {
            Log::error("WhatsApp Engine Error (getSessionStatus) on URL [{$url}]: " . $e->getMessage());
            return ['status' => 'error', 'message' => "Engine unreachable at {$this->baseUrl}"];
        }
    }

    /**
     * Send a text message through the engine.
     */
    public function sendMessage(string $sessionId, string $to, string $text)
    {
        try {
            $url = "{$this->baseUrl}/message/send";
            $response = Http::timeout(15)->post($url, [
                'sessionId' => $sessionId,
                'to' => $to,
                'text' => $text
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error("WhatsApp Engine Error (sendMessage) on URL [{$url}]: " . $e->getMessage());
            return ['error' => "Engine unreachable at {$this->baseUrl}"];
        }
    }

    /**
     * Delete a session (Logout).
     */
    public function deleteSession(string $sessionId)
    {
        try {
            $url = "{$this->baseUrl}/session/{$sessionId}";
            $response = Http::timeout(10)->delete($url);
            return $response->json();
        } catch (\Exception $e) {
            Log::error("WhatsApp Engine Error (deleteSession) on URL [{$url}]: " . $e->getMessage());
            return ['error' => "Engine unreachable at {$this->baseUrl}"];
        }
    }
}
