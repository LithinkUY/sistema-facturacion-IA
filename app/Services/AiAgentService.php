<?php

namespace App\Services;

use App\AiConversation;
use App\Business;
use App\Contact;
use App\Product;
use App\Transaction;
use App\TransactionPayment;
use App\OrderPedido;
use App\OrderTask;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    protected $apiKey;
    protected $model = 'gemini-2.5-flash';
    protected $fallbackModels = ['gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-2.0-flash-lite'];
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    protected $businessId;
    protected $userId;

    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
    }

    /**
     * Obtener API key de configuración del sistema
     */
    protected function getApiKey()
    {
        $setting = DB::table('system')->where('key', 'gemini_api_key')->first();
        return $setting ? $setting->value : null;
    }

    /**
     * Configurar contexto de negocio
     */
    public function setContext($businessId, $userId)
    {
        $this->businessId = $businessId;
        $this->userId = $userId;
        return $this;
    }

    /**
     * Enviar mensaje al agente y obtener respuesta
     */
    public function chat($message, $sessionId)
    {
        if (empty($this->apiKey)) {
            $this->apiKey = $this->getApiKey();
        }

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'No se ha configurado la API Key de Gemini. Ve a Configuración > Agente IA para configurarla.',
                'action' => null,
            ];
        }

        // 1. Detectar intención y recopilar datos del sistema
        $systemContext = $this->gatherSystemContext($message);

        // 2. Guardar mensaje del usuario
        AiConversation::create([
            'business_id' => $this->businessId,
            'user_id' => $this->userId,
            'session_id' => $sessionId,
            'role' => 'user',
            'message' => $message,
        ]);

        // 3. Obtener historial
        $history = AiConversation::getSessionHistory($sessionId, 16);

        // 4. Construir prompt
        $systemPrompt = $this->buildSystemPrompt($systemContext);
        $contents = $this->buildContents($history, $message, $systemContext);

        // 5. Llamar a Gemini API
        $response = $this->callGeminiApi($systemPrompt, $contents);

        if (!$response['success']) {
            return $response;
        }

        $aiMessage = $response['text'];
        $tokensUsed = $response['tokens'] ?? 0;

        // 6. Detectar y ejecutar acciones si el agente las solicita
        $actionResult = $this->detectAndExecuteAction($aiMessage, $message);

        // 7. Guardar respuesta del agente
        AiConversation::create([
            'business_id' => $this->businessId,
            'user_id' => $this->userId,
            'session_id' => $sessionId,
            'role' => 'model',
            'message' => $aiMessage,
            'context_data' => $systemContext,
            'action_type' => $actionResult['type'] ?? null,
            'action_result' => $actionResult['result'] ?? null,
            'tokens_used' => $tokensUsed,
        ]);

        return [
            'success' => true,
            'message' => $aiMessage,
            'action' => $actionResult,
            'tokens_used' => $tokensUsed,
        ];
    }

    /**
     * Construir el system prompt con contexto del negocio
     */
    protected function buildSystemPrompt($context)
    {
        $business = Business::find($this->businessId);
        $businessName = $business ? $business->name : 'Negocio';
        $user = User::find($this->userId);
        $userName = $user ? ($user->first_name . ' ' . ($user->last_name ?? '')) : 'Usuario';

        $prompt = "Eres un asistente de IA inteligente integrado en el sistema de facturación de \"{$businessName}\". ";
        $prompt .= "Tu nombre es \"Asistente IA\". El usuario que te habla se llama \"{$userName}\". ";
        $prompt .= "Responde siempre en español. Sé conciso, profesional y útil.\n\n";

        $prompt .= "=== CAPACIDADES ===\n";
        $prompt .= "Puedes ayudar con:\n";
        $prompt .= "- Consultar información de ventas, compras, productos, contactos, órdenes de pedido\n";
        $prompt .= "- Dar resúmenes financieros (ventas del día, semana, mes)\n";
        $prompt .= "- Buscar productos por nombre o SKU\n";
        $prompt .= "- Buscar clientes/proveedores\n";
        $prompt .= "- Ver estado de órdenes de pedido y tareas\n";
        $prompt .= "- Dar recomendaciones sobre el negocio\n";
        $prompt .= "- Responder preguntas generales sobre facturación en Uruguay (IVA, CFE, DGI)\n\n";

        $prompt .= "=== REGLAS ===\n";
        $prompt .= "1. Si no tienes datos suficientes, dilo honestamente\n";
        $prompt .= "2. Formatea los montos en pesos uruguayos con $ y separador de miles\n";
        $prompt .= "3. Usa emojis moderadamente para hacer la conversación agradable\n";
        $prompt .= "4. Si te piden crear/modificar algo, confirma antes de proceder\n";
        $prompt .= "5. Para datos en tabla, usa formato Markdown\n\n";

        // Agregar datos contextuales reales
        if (!empty($context)) {
            $prompt .= "=== DATOS ACTUALES DEL SISTEMA ===\n";
            foreach ($context as $key => $value) {
                if (is_array($value)) {
                    $prompt .= "{$key}:\n" . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
                } else {
                    $prompt .= "{$key}: {$value}\n";
                }
            }
        }

        return $prompt;
    }

    /**
     * Recopilar contexto relevante del sistema según el mensaje
     */
    protected function gatherSystemContext($message)
    {
        $context = [];
        $msg = mb_strtolower($message);

        // Siempre incluir un resumen básico
        $context['fecha_actual'] = now()->format('d/m/Y H:i');

        try {
            // Ventas
            if (preg_match('/(venta|vendido|factur|ingreso|cobr|ganancia|revenue)/i', $msg)) {
                $context['ventas_hoy'] = $this->getSalesToday();
                $context['ventas_semana'] = $this->getSalesThisWeek();
                $context['ventas_mes'] = $this->getSalesThisMonth();
            }

            // Productos
            if (preg_match('/(producto|stock|inventario|item|artículo|sku)/i', $msg)) {
                $context['resumen_productos'] = $this->getProductsSummary();

                // Si busca un producto específico
                if (preg_match('/(?:busca|encuentra|dime|cual|cuál|info|información)\s+(?:(?:del?|sobre|el)\s+)?(?:producto\s+)?["\']?(.+?)["\']?\s*$/i', $msg, $matches)) {
                    $context['busqueda_producto'] = $this->searchProducts($matches[1]);
                }
            }

            // Clientes/Proveedores
            if (preg_match('/(cliente|proveedor|contacto|comprador)/i', $msg)) {
                $context['resumen_contactos'] = $this->getContactsSummary();
            }

            // Órdenes de pedido
            if (preg_match('/(orden|pedido|order|tarea|task)/i', $msg)) {
                $context['resumen_ordenes'] = $this->getOrdersSummary();
                $context['tareas_pendientes'] = $this->getPendingTasks();
            }

            // Compras
            if (preg_match('/(compra|proveedor|gasto|expense|costo)/i', $msg)) {
                $context['compras_mes'] = $this->getPurchasesThisMonth();
            }

            // Resumen general
            if (preg_match('/(resumen|resum|dashboard|estado|general|cómo va|como va|how)/i', $msg)) {
                $context['ventas_hoy'] = $this->getSalesToday();
                $context['ventas_mes'] = $this->getSalesThisMonth();
                $context['resumen_productos'] = $this->getProductsSummary();
                $context['resumen_contactos'] = $this->getContactsSummary();
                $context['resumen_ordenes'] = $this->getOrdersSummary();
            }
        } catch (\Exception $e) {
            Log::warning('AI Agent context gathering error: ' . $e->getMessage());
            $context['error_contexto'] = 'No se pudieron cargar todos los datos del sistema.';
        }

        return $context;
    }

    /**
     * Obtener ventas de hoy
     */
    protected function getSalesToday()
    {
        $today = now()->toDateString();
        $sales = Transaction::where('business_id', $this->businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', $today)
            ->selectRaw('COUNT(*) as total_ventas, COALESCE(SUM(final_total), 0) as total_monto')
            ->first();

        return [
            'cantidad' => $sales->total_ventas ?? 0,
            'monto_total' => number_format($sales->total_monto ?? 0, 2),
        ];
    }

    /**
     * Obtener ventas de la semana
     */
    protected function getSalesThisWeek()
    {
        $startOfWeek = now()->startOfWeek()->toDateString();
        $today = now()->toDateString();

        $sales = Transaction::where('business_id', $this->businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', $startOfWeek)
            ->whereDate('transaction_date', '<=', $today)
            ->selectRaw('COUNT(*) as total_ventas, COALESCE(SUM(final_total), 0) as total_monto')
            ->first();

        return [
            'cantidad' => $sales->total_ventas ?? 0,
            'monto_total' => number_format($sales->total_monto ?? 0, 2),
        ];
    }

    /**
     * Obtener ventas del mes
     */
    protected function getSalesThisMonth()
    {
        $sales = Transaction::where('business_id', $this->businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->selectRaw('COUNT(*) as total_ventas, COALESCE(SUM(final_total), 0) as total_monto')
            ->first();

        return [
            'cantidad' => $sales->total_ventas ?? 0,
            'monto_total' => number_format($sales->total_monto ?? 0, 2),
        ];
    }

    /**
     * Resumen de productos
     */
    protected function getProductsSummary()
    {
        $total = Product::where('business_id', $this->businessId)->count();
        $active = Product::where('business_id', $this->businessId)->where('is_inactive', 0)->count();

        return [
            'total_productos' => $total,
            'activos' => $active,
            'inactivos' => $total - $active,
        ];
    }

    /**
     * Buscar productos específicos
     */
    protected function searchProducts($term)
    {
        $products = Product::where('business_id', $this->businessId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'sku', 'type', 'is_inactive'])
            ->toArray();

        return $products;
    }

    /**
     * Resumen de contactos
     */
    protected function getContactsSummary()
    {
        $customers = Contact::where('business_id', $this->businessId)
            ->where('type', 'customer')->count();
        $suppliers = Contact::where('business_id', $this->businessId)
            ->where('type', 'supplier')->count();
        $both = Contact::where('business_id', $this->businessId)
            ->where('type', 'both')->count();

        return [
            'clientes' => $customers,
            'proveedores' => $suppliers,
            'ambos' => $both,
            'total' => $customers + $suppliers + $both,
        ];
    }

    /**
     * Resumen de órdenes de pedido
     */
    protected function getOrdersSummary()
    {
        $orders = OrderPedido::where('business_id', $this->businessId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completadas,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as borradores,
                COALESCE(SUM(total), 0) as valor_total
            ")
            ->first();

        return [
            'total' => $orders->total ?? 0,
            'pendientes' => $orders->pendientes ?? 0,
            'en_proceso' => $orders->en_proceso ?? 0,
            'completadas' => $orders->completadas ?? 0,
            'borradores' => $orders->borradores ?? 0,
            'valor_total' => number_format($orders->valor_total ?? 0, 2),
        ];
    }

    /**
     * Tareas pendientes
     */
    protected function getPendingTasks()
    {
        $tasks = OrderTask::where('business_id', $this->businessId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->with('orderPedido:id,order_number')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get(['id', 'title', 'status', 'priority', 'due_date', 'order_pedido_id'])
            ->map(function ($task) {
                return [
                    'titulo' => $task->title,
                    'estado' => $task->status,
                    'prioridad' => $task->priority,
                    'fecha_limite' => $task->due_date ? $task->due_date->format('d/m/Y') : 'Sin fecha',
                    'orden' => $task->orderPedido->order_number ?? 'N/A',
                ];
            })
            ->toArray();

        return $tasks;
    }

    /**
     * Compras del mes
     */
    protected function getPurchasesThisMonth()
    {
        $purchases = Transaction::where('business_id', $this->businessId)
            ->where('type', 'purchase')
            ->where('status', 'received')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->selectRaw('COUNT(*) as total_compras, COALESCE(SUM(final_total), 0) as total_monto')
            ->first();

        return [
            'cantidad' => $purchases->total_compras ?? 0,
            'monto_total' => number_format($purchases->total_monto ?? 0, 2),
        ];
    }

    /**
     * Construir el array de contenidos para la API de Gemini
     */
    protected function buildContents($history, $currentMessage, $context)
    {
        $contents = [];

        // Agregar historial
        foreach ($history as $msg) {
            if ($msg->role === 'user' && $msg->message === $currentMessage && $msg->id === $history->last()->id) {
                continue; // Skip current message (we'll add it with context)
            }
            $contents[] = [
                'role' => $msg->role === 'model' ? 'model' : 'user',
                'parts' => [['text' => $msg->message]],
            ];
        }

        // Agregar mensaje actual con contexto
        $userMessage = $currentMessage;
        if (!empty($context) && count($context) > 1) {
            $userMessage .= "\n\n[Datos del sistema adjuntos en el system prompt para tu referencia]";
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]],
        ];

        return $contents;
    }

    /**
     * Llamar a la API de Gemini con fallback automático de modelos
     */
    protected function callGeminiApi($systemPrompt, $contents)
    {
        $modelsToTry = array_merge([$this->model], $this->fallbackModels);

        foreach ($modelsToTry as $model) {
            $result = $this->callGeminiModel($model, $systemPrompt, $contents);

            if ($result['success']) {
                // Si funcionó con un modelo diferente al principal, loguearlo
                if ($model !== $this->model) {
                    Log::info("Gemini: Usando modelo fallback '{$model}' (principal '{$this->model}' no disponible)");
                }
                return $result;
            }

            // Solo intentar fallback si es error de cuota (429) o recurso agotado
            if (!isset($result['_retryable']) || !$result['_retryable']) {
                return $result; // Error no retryable, devolver directamente
            }

            Log::warning("Gemini: Modelo '{$model}' sin cuota, intentando siguiente...");
            usleep(300000); // 300ms entre intentos
        }

        // Todos los modelos agotados
        return [
            'success' => false,
            'message' => '⚠️ Todos los modelos de Gemini tienen la cuota agotada. Espera unos minutos e intenta de nuevo. La cuota gratuita se renueva cada minuto (15 req/min).',
        ];
    }

    /**
     * Hacer la llamada HTTP a un modelo específico de Gemini
     */
    protected function callGeminiModel($model, $systemPrompt, $contents)
    {
        $url = $this->apiUrl . $model . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2048,
                'topP' => 0.95,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => base_path('cacert.pem'),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error("Gemini API cURL error ({$model}): " . $curlError);
            return [
                'success' => false,
                'message' => 'Error de conexión con la API de Gemini: ' . $curlError,
                '_retryable' => false,
            ];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $data['error']['message'] ?? 'Error desconocido (HTTP ' . $httpCode . ')';
            $isQuotaError = $httpCode === 429
                || stripos($errorMsg, 'quota') !== false
                || stripos($errorMsg, 'rate') !== false
                || stripos($errorMsg, 'Resource has been exhausted') !== false;

            Log::warning("Gemini API error ({$model}, HTTP {$httpCode}): " . substr($errorMsg, 0, 200));

            return [
                'success' => false,
                'message' => 'Error de la API de Gemini: ' . $errorMsg,
                '_retryable' => $isQuotaError,
            ];
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        $tokens = $data['usageMetadata']['totalTokenCount'] ?? 0;

        if (empty($text)) {
            return [
                'success' => false,
                'message' => 'La API no devolvió una respuesta válida.',
                '_retryable' => false,
            ];
        }

        return [
            'success' => true,
            'text' => $text,
            'tokens' => $tokens,
            'model' => $model,
        ];
    }

    /**
     * Detectar si la respuesta del agente contiene acciones ejecutables
     */
    protected function detectAndExecuteAction($aiMessage, $userMessage)
    {
        // Por ahora, las acciones son informativas
        // Se puede expandir para crear productos, contactos, etc.
        return [
            'type' => null,
            'result' => null,
        ];
    }

    /**
     * Top 5 productos más vendidos del mes
     */
    public function getTopProducts()
    {
        return DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 'tsl.transaction_id', '=', 't.id')
            ->join('products as p', 'tsl.product_id', '=', 'p.id')
            ->where('t.business_id', $this->businessId)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereYear('t.transaction_date', now()->year)
            ->whereMonth('t.transaction_date', now()->month)
            ->select('p.name', DB::raw('SUM(tsl.quantity) as qty'), DB::raw('SUM(tsl.unit_price_before_discount * tsl.quantity) as revenue'))
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get()
            ->toArray();
    }
}
