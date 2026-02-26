<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Verificación del webhook (Meta envía GET al configurar)
     * URL: GET /webhook/whatsapp
     */
    public function verify(Request $request)
    {
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        Log::info('WhatsApp webhook verification attempt', [
            'mode' => $mode,
            'token' => $token ? substr($token, 0, 10) . '...' : null,
        ]);

        if ($mode === 'subscribe' && $token === $this->whatsAppService->getVerifyToken()) {
            Log::info('WhatsApp webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed');
        return response('Forbidden', 403);
    }

    /**
     * Recibir notificaciones del webhook (Meta envía POST con mensajes)
     * URL: POST /webhook/whatsapp
     */
    public function receive(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp webhook received', [
            'has_entry' => !empty($payload['entry']),
        ]);

        // Procesar en background para responder rápido a Meta
        $this->whatsAppService->processWebhook($payload);

        // Meta espera un 200 OK rápido
        return response('OK', 200);
    }
}
