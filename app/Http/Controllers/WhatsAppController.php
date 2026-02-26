<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\WhatsappMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Panel principal - Lista de conversaciones
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $conversations = WhatsappMessage::getConversations($business_id);
        $stats = $this->whatsAppService->getStats($business_id);
        $isConfigured = $this->whatsAppService->isConfigured();

        // Verificar si el token está vencido
        $tokenStatus = $this->whatsAppService->validateToken();

        return view('whatsapp.index', compact('conversations', 'stats', 'isConfigured', 'tokenStatus'));
    }

    /**
     * Ver chat con un número específico
     */
    public function chat($phone)
    {
        $business_id = request()->session()->get('user.business_id');
        $messages = WhatsappMessage::getChatHistory($business_id, $phone, 100);
        $isConfigured = $this->whatsAppService->isConfigured();

        // Obtener info del contacto
        $contactInfo = WhatsappMessage::where('business_id', $business_id)
            ->where('phone_number', $phone)
            ->orderBy('created_at', 'desc')
            ->first();

        $contactName = $contactInfo->contact_name ?? $phone;

        return view('whatsapp.chat', compact('messages', 'phone', 'contactName', 'isConfigured'));
    }

    /**
     * Obtener mensajes nuevos (polling AJAX)
     */
    public function getMessages(Request $request)
    {
        $phone = $request->input('phone');
        $lastId = $request->input('last_id', 0);
        $business_id = request()->session()->get('user.business_id');

        $messages = WhatsappMessage::where('business_id', $business_id)
            ->where('phone_number', $phone)
            ->where('id', '>', $lastId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'direction' => $msg->direction,
                    'message' => $msg->message,
                    'message_type' => $msg->message_type,
                    'status' => $msg->status,
                    'is_ai' => $msg->is_ai_response,
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d/m/Y'),
                ];
            });

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * Enviar mensaje manual desde el panel
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
        ]);

        $phone = WhatsAppService::formatPhone($request->phone);
        $text = $request->message;
        $business_id = request()->session()->get('user.business_id');

        $result = $this->whatsAppService->sendTextMessage($phone, $text);

        // Guardar en BD
        WhatsappMessage::create([
            'business_id' => $business_id,
            'wa_message_id' => $result['message_id'] ?? null,
            'phone_number' => $phone,
            'contact_name' => $request->input('contact_name', null),
            'direction' => 'outgoing',
            'message_type' => 'text',
            'message' => $text,
            'status' => $result['success'] ? 'sent' : 'failed',
            'is_ai_response' => false,
            'error_message' => $result['error'] ?? null,
        ]);

        // Si el error es de token, dar mensaje claro al frontend
        if (!$result['success'] && isset($result['error'])) {
            $error = strtolower($result['error']);
            if (str_contains($error, 'expired') || str_contains($error, 'access token') || str_contains($error, 'oauthexception')) {
                $result['error'] = '⚠️ Token de WhatsApp expirado. Ve a Configuración → Actualiza el Access Token desde Meta Developer Console.';
                $result['token_expired'] = true;
            }
        }

        return response()->json($result);
    }

    /**
     * Página de configuración de WhatsApp
     */
    public function settings()
    {
        $configs = DB::table('system')
            ->whereIn('key', [
                'whatsapp_access_token',
                'whatsapp_phone_number_id',
                'whatsapp_verify_token',
                'whatsapp_ai_enabled',
            ])
            ->pluck('value', 'key');

        $maskedToken = '';
        if (!empty($configs['whatsapp_access_token'])) {
            $token = $configs['whatsapp_access_token'];
            $maskedToken = substr($token, 0, 10) . str_repeat('•', max(0, strlen($token) - 18)) . substr($token, -8);
        }

        return view('whatsapp.settings', [
            'maskedToken' => $maskedToken,
            'phoneNumberId' => $configs['whatsapp_phone_number_id'] ?? '',
            'verifyToken' => $configs['whatsapp_verify_token'] ?? 'facturacion_wa_verify_' . md5('publideas'),
            'aiEnabled' => ($configs['whatsapp_ai_enabled'] ?? '1') === '1',
            'webhookUrl' => url('/webhook/whatsapp'),
        ]);
    }

    /**
     * Guardar configuración de WhatsApp
     */
    public function saveSettings(Request $request)
    {
        $fields = [
            'whatsapp_phone_number_id' => $request->input('phone_number_id'),
            'whatsapp_verify_token' => $request->input('verify_token'),
            'whatsapp_ai_enabled' => $request->input('ai_enabled', '0'),
        ];

        // Solo guardar token si se ingresó uno nuevo (no enmascarado)
        $token = $request->input('access_token');
        if ($token && !str_contains($token, '•')) {
            $fields['whatsapp_access_token'] = $token;
        }

        foreach ($fields as $key => $value) {
            if ($value !== null) {
                WhatsAppService::saveConfig($key, $value);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración de WhatsApp guardada correctamente.',
        ]);
    }

    /**
     * Toggle IA on/off
     */
    public function toggleAI(Request $request)
    {
        $enabled = $request->input('enabled', '0');
        WhatsAppService::saveConfig('whatsapp_ai_enabled', $enabled);

        return response()->json([
            'success' => true,
            'message' => $enabled === '1' ? 'IA activada para WhatsApp' : 'IA desactivada para WhatsApp',
        ]);
    }

    /**
     * Obtener conversaciones actualizadas (AJAX)
     */
    public function getConversations()
    {
        $business_id = request()->session()->get('user.business_id');
        $conversations = WhatsappMessage::getConversations($business_id);

        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    /**
     * Detectar túnel ngrok desde el servidor (evita problemas CORS del navegador)
     */
    public function detectNgrok()
    {
        try {
            $ch = curl_init('http://127.0.0.1:4040/api/tunnels');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                $tunnels = $data['tunnels'] ?? [];
                
                // Buscar túnel HTTPS
                $httpsUrl = null;
                foreach ($tunnels as $tunnel) {
                    if (($tunnel['proto'] ?? '') === 'https') {
                        $httpsUrl = $tunnel['public_url'];
                        break;
                    }
                }

                if ($httpsUrl) {
                    return response()->json([
                        'success' => true,
                        'url' => $httpsUrl,
                        'webhook_url' => $httpsUrl . '/webhook/whatsapp',
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'No hay túneles activos']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'ngrok no está corriendo']);
        }
    }
}
