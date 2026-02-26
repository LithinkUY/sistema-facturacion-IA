<?php

namespace App\Http\Controllers;

use App\AiConversation;
use App\Services\AiAgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiAgentController extends Controller
{
    protected $aiService;

    public function __construct(AiAgentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Página principal del chat IA
     */
    public function index()
    {
        $business_id = session('business.id');
        $hasApiKey = !empty(DB::table('system')->where('key', 'gemini_api_key')->first());

        // Obtener sesiones anteriores
        $sessions = AiConversation::where('business_id', $business_id)
            ->where('user_id', auth()->id())
            ->where('role', 'user')
            ->select('session_id')
            ->selectRaw('MAX(created_at) as last_activity')
            ->selectRaw('SUBSTRING_INDEX(GROUP_CONCAT(message ORDER BY created_at ASC SEPARATOR "|||"), "|||", 1) as first_message')
            ->groupBy('session_id')
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get();

        return view('ai_agent.index', compact('hasApiKey', 'sessions'));
    }

    /**
     * Enviar mensaje al agente (AJAX)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $business_id = session('business.id');
        $user_id = auth()->id();
        $sessionId = $request->session_id ?: Str::uuid()->toString();

        $response = $this->aiService
            ->setContext($business_id, $user_id)
            ->chat($request->message, $sessionId);

        $response['session_id'] = $sessionId;

        return response()->json($response);
    }

    /**
     * Obtener historial de una sesión (AJAX)
     */
    public function getHistory(Request $request)
    {
        $sessionId = $request->input('session_id');
        $business_id = session('business.id');

        $messages = AiConversation::where('business_id', $business_id)
            ->where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->whereIn('role', ['user', 'model'])
            ->orderBy('created_at', 'asc')
            ->get(['id', 'role', 'message', 'action_type', 'action_result', 'created_at'])
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'message' => $msg->message,
                    'action' => $msg->action_type,
                    'time' => $msg->created_at->format('H:i'),
                    'date' => $msg->created_at->format('d/m/Y'),
                ];
            });

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * Crear nueva sesión
     */
    public function newSession()
    {
        return response()->json([
            'success' => true,
            'session_id' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Eliminar sesión
     */
    public function deleteSession(Request $request)
    {
        $sessionId = $request->input('session_id');
        $business_id = session('business.id');

        AiConversation::where('business_id', $business_id)
            ->where('user_id', auth()->id())
            ->where('session_id', $sessionId)
            ->delete();

        return response()->json(['success' => true, 'msg' => 'Conversación eliminada']);
    }

    /**
     * Guardar/actualizar API key (settings)
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'gemini_api_key' => 'required|string|min:10',
        ]);

        $key = trim($request->gemini_api_key);

        // Rechazar si es la versión enmascarada
        if (str_contains($key, '•') || str_contains($key, '***')) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresa la API Key real, no la versión enmascarada.',
            ]);
        }

        DB::table('system')->updateOrInsert(
            ['key' => 'gemini_api_key'],
            ['value' => $key]
        );

        return response()->json([
            'success' => true,
            'msg' => 'API Key guardada correctamente',
            'message' => 'API Key guardada correctamente',
        ]);
    }

    /**
     * Página de configuración del agente
     */
    public function settings()
    {
        $maskedKey = '';
        $setting = DB::table('system')->where('key', 'gemini_api_key')->first();
        if ($setting) {
            // Mostrar solo últimos 8 caracteres
            $maskedKey = str_repeat('•', max(0, strlen($setting->value) - 8)) . substr($setting->value, -8);
        }

        return view('ai_agent.settings', compact('maskedKey'));
    }
}
