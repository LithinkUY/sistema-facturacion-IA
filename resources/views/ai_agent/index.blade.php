@extends('layouts.app')
@section('title', 'Asistente IA')

@section('css')
<style>
    .ai-container { display: flex; height: calc(100vh - 120px); background: #f4f6f9; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }

    /* Sidebar */
    .ai-sidebar { width: 280px; background: #1a1a2e; color: #e0e0e0; display: flex; flex-direction: column; flex-shrink: 0; }
    .ai-sidebar-header { padding: 20px; border-bottom: 1px solid #333; text-align: center; }
    .ai-sidebar-header h4 { color: #00d4ff; margin: 5px 0; font-weight: 700; }
    .ai-sidebar-header small { color: #888; }
    .ai-new-chat { margin: 12px 15px; padding: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s; }
    .ai-new-chat:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
    .ai-sessions { flex: 1; overflow-y: auto; padding: 8px; }
    .ai-session-item { padding: 10px 12px; border-radius: 8px; cursor: pointer; margin-bottom: 4px; transition: 0.15s; display: flex; justify-content: space-between; align-items: center; }
    .ai-session-item:hover, .ai-session-item.active { background: rgba(255,255,255,0.1); }
    .ai-session-item.active { background: rgba(102,126,234,0.3); }
    .ai-session-item .session-text { font-size: 0.85em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
    .ai-session-item .session-delete { opacity: 0; color: #ff6b6b; cursor: pointer; font-size: 0.8em; }
    .ai-session-item:hover .session-delete { opacity: 1; }
    .ai-sidebar-footer { padding: 12px 15px; border-top: 1px solid #333; text-align: center; }
    .ai-sidebar-footer a { color: #888; font-size: 0.85em; text-decoration: none; }
    .ai-sidebar-footer a:hover { color: #00d4ff; }

    /* Chat area */
    .ai-chat-area { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .ai-chat-header { padding: 15px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; display: flex; align-items: center; gap: 12px; }
    .ai-chat-header .ai-avatar { width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3em; }
    .ai-chat-header .ai-status { width: 8px; height: 8px; background: #4caf50; border-radius: 50%; display: inline-block; }

    /* Messages */
    .ai-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 16px; }
    .ai-msg { display: flex; gap: 10px; max-width: 85%; animation: fadeInUp 0.3s ease; }
    .ai-msg.user { align-self: flex-end; flex-direction: row-reverse; }
    .ai-msg.model { align-self: flex-start; }
    .ai-msg-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9em; flex-shrink: 0; }
    .ai-msg.user .ai-msg-avatar { background: #667eea; color: #fff; }
    .ai-msg.model .ai-msg-avatar { background: #e8e8e8; color: #333; }
    .ai-msg-bubble { padding: 12px 16px; border-radius: 16px; font-size: 0.95em; line-height: 1.5; }
    .ai-msg.user .ai-msg-bubble { background: #667eea; color: #fff; border-bottom-right-radius: 4px; }
    .ai-msg.model .ai-msg-bubble { background: #f0f2f5; color: #333; border-bottom-left-radius: 4px; }
    .ai-msg-bubble p { margin: 0 0 8px 0; }
    .ai-msg-bubble p:last-child { margin-bottom: 0; }
    .ai-msg-bubble code { background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
    .ai-msg-bubble pre { background: #1e1e1e; color: #d4d4d4; padding: 12px; border-radius: 8px; overflow-x: auto; margin: 8px 0; }
    .ai-msg-bubble table { width: 100%; border-collapse: collapse; margin: 8px 0; }
    .ai-msg-bubble th, .ai-msg-bubble td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 0.9em; }
    .ai-msg-bubble th { background: #f5f5f5; font-weight: 600; }
    .ai-msg-time { font-size: 0.75em; color: #999; margin-top: 4px; text-align: right; }
    .ai-msg.model .ai-msg-time { text-align: left; }

    /* Typing indicator */
    .ai-typing { display: none; align-self: flex-start; padding: 12px 16px; background: #f0f2f5; border-radius: 16px; margin-left: 42px; }
    .ai-typing span { width: 8px; height: 8px; background: #999; border-radius: 50%; display: inline-block; animation: typing 1s infinite; margin: 0 2px; }
    .ai-typing span:nth-child(2) { animation-delay: 0.15s; }
    .ai-typing span:nth-child(3) { animation-delay: 0.3s; }

    /* Input area */
    .ai-input-area { padding: 15px 20px; border-top: 1px solid #e5e7eb; background: #fafafa; }
    .ai-input-wrapper { display: flex; gap: 10px; align-items: center; }
    .ai-input-wrapper input { flex: 1; border: 2px solid #e5e7eb; border-radius: 25px; padding: 12px 20px; font-size: 0.95em; outline: none; transition: 0.2s; }
    .ai-input-wrapper input:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
    .ai-send-btn { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2em; transition: 0.2s; }
    .ai-send-btn:hover { transform: scale(1.05); }
    .ai-send-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .ai-input-suggestions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .ai-suggestion { background: #f0f2f5; border: 1px solid #e5e7eb; border-radius: 20px; padding: 6px 14px; font-size: 0.8em; cursor: pointer; transition: 0.15s; white-space: nowrap; }
    .ai-suggestion:hover { background: #667eea; color: #fff; border-color: #667eea; }

    /* Welcome screen */
    .ai-welcome { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; padding: 40px; text-align: center; }
    .ai-welcome-icon { font-size: 4em; margin-bottom: 15px; }
    .ai-welcome h2 { color: #333; margin-bottom: 8px; }
    .ai-welcome p { color: #888; max-width: 500px; }

    /* API Key warning */
    .ai-no-key { background: #fff3cd; border: 1px solid #ffc107; border-radius: 10px; padding: 20px; margin: 20px; text-align: center; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes typing { 0%, 60%, 100% { transform: translateY(0); } 30% { transform: translateY(-8px); } }

    /* Responsive */
    @media (max-width: 768px) {
        .ai-sidebar { display: none; }
        .ai-container { border-radius: 0; height: calc(100vh - 60px); }
    }
</style>
@endsection

@section('content')

<section class="content no-print" style="padding: 10px;">
    <div class="ai-container">
        <!-- Sidebar -->
        <div class="ai-sidebar">
            <div class="ai-sidebar-header">
                <h4>🤖 Asistente IA</h4>
                <small>Powered by Gemini</small>
            </div>
            <button class="ai-new-chat" id="btn_new_chat">
                <i class="fas fa-plus"></i> Nueva Conversación
            </button>
            <div class="ai-sessions" id="sessions_list">
                @foreach($sessions as $session)
                <div class="ai-session-item" data-session="{{ $session->session_id }}">
                    <span class="session-text">
                        <i class="fas fa-comment-dots"></i>
                        {{ Str::limit($session->first_message, 30) }}
                    </span>
                    <span class="session-delete" data-session="{{ $session->session_id }}" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </span>
                </div>
                @endforeach
            </div>
            <div class="ai-sidebar-footer">
                <a href="{{ url('/ai-agent/settings') }}"><i class="fas fa-cog"></i> Configuración</a>
            </div>
        </div>

        <!-- Chat area -->
        <div class="ai-chat-area">
            <div class="ai-chat-header">
                <div class="ai-avatar">🤖</div>
                <div>
                    <strong>Asistente IA</strong>
                    <br><small><span class="ai-status"></span> En línea · Gemini 2.5 Flash</small>
                </div>
            </div>

            <div class="ai-messages" id="chat_messages">
                @if(!$hasApiKey)
                <div class="ai-no-key">
                    <h4>⚠️ API Key no configurada</h4>
                    <p>Para usar el agente IA, necesitas configurar una API Key de Google Gemini.</p>
                    <p><strong>Es gratis:</strong> Obtén tu clave en <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a></p>
                    <a href="{{ url('/ai-agent/settings') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-key"></i> Configurar API Key
                    </a>
                </div>
                @endif

                <!-- Welcome -->
                <div class="ai-welcome" id="welcome_screen">
                    <div class="ai-welcome-icon">🤖</div>
                    <h2>¡Hola! Soy tu asistente</h2>
                    <p>Puedo ayudarte a consultar ventas, buscar productos, revisar órdenes de pedido y mucho más. ¿En qué te puedo ayudar?</p>
                </div>
            </div>

            <div class="ai-typing" id="typing_indicator">
                <span></span><span></span><span></span>
            </div>

            <div class="ai-input-area">
                <div class="ai-input-wrapper">
                    <input type="text" id="ai_input" placeholder="Escribe tu pregunta..." autocomplete="off" {{ !$hasApiKey ? 'disabled' : '' }}>
                    <button class="ai-send-btn" id="btn_send" {{ !$hasApiKey ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <div class="ai-input-suggestions" id="suggestions">
                    <span class="ai-suggestion" data-msg="¿Cómo van las ventas de hoy?">📊 Ventas de hoy</span>
                    <span class="ai-suggestion" data-msg="Dame un resumen general del negocio">📋 Resumen general</span>
                    <span class="ai-suggestion" data-msg="¿Cuántos productos tengo activos?">📦 Productos</span>
                    <span class="ai-suggestion" data-msg="¿Hay tareas pendientes?">✅ Tareas pendientes</span>
                    <span class="ai-suggestion" data-msg="¿Cuáles son las órdenes de pedido en proceso?">🛒 Órdenes activas</span>
                    <span class="ai-suggestion" data-msg="¿Cómo funciona la facturación electrónica en Uruguay?">🇺🇾 CFE Uruguay</span>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
$(document).ready(function() {
    var sessionId = null;
    var isProcessing = false;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Enviar mensaje
    function sendMessage(message) {
        if (!message.trim() || isProcessing) return;

        isProcessing = true;
        $('#welcome_screen').hide();
        $('#suggestions').hide();

        // Mostrar mensaje del usuario
        appendMessage('user', message);
        $('#ai_input').val('').focus();
        $('#typing_indicator').show();
        scrollToBottom();

        $.ajax({
            url: '{{ url("/ai-agent/send") }}',
            type: 'POST',
            data: {
                message: message,
                session_id: sessionId,
                _token: csrfToken
            },
            timeout: 45000,
            success: function(res) {
                $('#typing_indicator').hide();
                isProcessing = false;

                if (res.success) {
                    sessionId = res.session_id;
                    appendMessage('model', res.message);
                } else {
                    appendMessage('model', '❌ ' + res.message);
                }
                scrollToBottom();
            },
            error: function(xhr, status, error) {
                $('#typing_indicator').hide();
                isProcessing = false;
                var msg = '❌ Error de conexión. ';
                if (status === 'timeout') {
                    msg += 'La solicitud tardó demasiado.';
                } else if (xhr.status === 419) {
                    msg += 'Sesión expirada. Recarga la página.';
                } else {
                    msg += 'Intenta de nuevo.';
                }
                appendMessage('model', msg);
                scrollToBottom();
            }
        });
    }

    function appendMessage(role, text) {
        var time = new Date().toLocaleTimeString('es-UY', { hour: '2-digit', minute: '2-digit' });
        var avatarIcon = role === 'user' ? '👤' : '🤖';

        // Convertir Markdown a HTML para respuestas del modelo
        var htmlContent = role === 'model' ? marked.parse(text) : escapeHtml(text);

        var html = `
            <div class="ai-msg ${role}">
                <div class="ai-msg-avatar">${avatarIcon}</div>
                <div>
                    <div class="ai-msg-bubble">${htmlContent}</div>
                    <div class="ai-msg-time">${time}</div>
                </div>
            </div>
        `;
        $('#chat_messages').append(html);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function scrollToBottom() {
        var $msgs = $('#chat_messages');
        $msgs.scrollTop($msgs[0].scrollHeight);
    }

    // Eventos
    $('#btn_send').click(function() {
        sendMessage($('#ai_input').val());
    });

    $('#ai_input').keypress(function(e) {
        if (e.which === 13) {
            sendMessage($(this).val());
        }
    });

    // Sugerencias
    $(document).on('click', '.ai-suggestion', function() {
        var msg = $(this).data('msg');
        sendMessage(msg);
    });

    // Nueva conversación
    $('#btn_new_chat').click(function() {
        sessionId = null;
        $('#chat_messages').html(`
            <div class="ai-welcome" id="welcome_screen">
                <div class="ai-welcome-icon">🤖</div>
                <h2>¡Hola! Soy tu asistente</h2>
                <p>Puedo ayudarte a consultar ventas, buscar productos, revisar órdenes de pedido y mucho más.</p>
            </div>
        `);
        $('#suggestions').show();
        $('#ai_input').val('').focus();
        $('.ai-session-item').removeClass('active');
    });

    // Cargar sesión anterior
    $(document).on('click', '.ai-session-item', function(e) {
        if ($(e.target).hasClass('session-delete') || $(e.target).closest('.session-delete').length) return;

        var sid = $(this).data('session');
        sessionId = sid;
        $('.ai-session-item').removeClass('active');
        $(this).addClass('active');

        // Cargar historial
        $('#chat_messages').html('<div class="text-center tw-py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        $('#welcome_screen').hide();
        $('#suggestions').hide();

        $.get('{{ url("/ai-agent/history") }}', { session_id: sid }, function(res) {
            $('#chat_messages').html('');
            if (res.success && res.messages.length) {
                res.messages.forEach(function(msg) {
                    appendMessage(msg.role, msg.message);
                });
                scrollToBottom();
            }
        });
    });

    // Eliminar sesión
    $(document).on('click', '.session-delete', function(e) {
        e.stopPropagation();
        var sid = $(this).data('session');
        var $item = $(this).closest('.ai-session-item');

        swal({
            title: '¿Eliminar conversación?',
            icon: 'warning',
            buttons: ['Cancelar', 'Eliminar'],
            dangerMode: true,
        }).then(function(ok) {
            if (ok) {
                $.ajax({
                    url: '{{ url("/ai-agent/delete-session") }}',
                    type: 'POST',
                    data: { session_id: sid, _token: csrfToken },
                    success: function(res) {
                        if (res.success) {
                            $item.slideUp(200, function() { $(this).remove(); });
                            if (sessionId === sid) {
                                sessionId = null;
                                $('#btn_new_chat').click();
                            }
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
