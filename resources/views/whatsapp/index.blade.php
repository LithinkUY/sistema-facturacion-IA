@extends('layouts.app')
@section('title', 'WhatsApp')

@section('css')
<style>
    .wa-container { display: flex; height: calc(100vh - 120px); background: #f0f2f5; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.1); }

    /* Sidebar conversaciones */
    .wa-sidebar { width: 350px; background: #fff; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; flex-shrink: 0; }
    .wa-sidebar-header { padding: 12px 16px; background: #075e54; color: #fff; display: flex; align-items: center; justify-content: space-between; }
    .wa-sidebar-header h4 { margin: 0; font-weight: 600; }
    .wa-search { padding: 8px 12px; background: #f0f2f5; }
    .wa-search input { width: 100%; border: none; border-radius: 20px; padding: 8px 16px; background: #fff; font-size: 0.9em; outline: none; }
    .wa-conv-list { flex: 1; overflow-y: auto; }
    .wa-conv-item { display: flex; align-items: center; padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f0f2f5; transition: 0.1s; }
    .wa-conv-item:hover { background: #f0f2f5; }
    .wa-conv-item.active { background: #e8f5e9; }
    .wa-conv-avatar { width: 48px; height: 48px; border-radius: 50%; background: #25d366; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2em; flex-shrink: 0; margin-right: 12px; }
    .wa-conv-info { flex: 1; min-width: 0; }
    .wa-conv-name { font-weight: 600; font-size: 0.95em; margin-bottom: 2px; }
    .wa-conv-last { font-size: 0.85em; color: #667781; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-conv-meta { text-align: right; flex-shrink: 0; }
    .wa-conv-time { font-size: 0.75em; color: #667781; }
    .wa-conv-badge { background: #25d366; color: #fff; border-radius: 50%; width: 20px; height: 20px; font-size: 0.7em; display: flex; align-items: center; justify-content: center; margin-left: auto; margin-top: 4px; }

    /* Chat area */
    .wa-chat-area { flex: 1; display: flex; flex-direction: column; }
    .wa-chat-header { padding: 10px 16px; background: #075e54; color: #fff; display: flex; align-items: center; gap: 12px; }
    .wa-chat-header .wa-chat-avatar { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; }
    .wa-chat-header .wa-chat-name { font-weight: 600; }
    .wa-chat-header .wa-chat-status { font-size: 0.8em; opacity: 0.8; }

    /* Messages */
    .wa-messages { flex: 1; overflow-y: auto; padding: 20px; background: #e5ddd5 url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAHElEQVQYV2P8////fwYiABNIwahCilx5MAGDAgAIDQah/ssgOQAAAABJRU5ErkJggg==') repeat; display: flex; flex-direction: column; gap: 4px; }
    .wa-msg { max-width: 65%; padding: 6px 8px 2px; border-radius: 8px; position: relative; animation: fadeIn 0.2s ease; }
    .wa-msg.incoming { background: #fff; align-self: flex-start; border-top-left-radius: 0; }
    .wa-msg.outgoing { background: #d9fdd3; align-self: flex-end; border-top-right-radius: 0; }
    .wa-msg-text { font-size: 0.9em; line-height: 1.4; word-wrap: break-word; margin-bottom: 2px; }
    .wa-msg-meta { display: flex; justify-content: flex-end; align-items: center; gap: 4px; }
    .wa-msg-time { font-size: 0.7em; color: #667781; }
    .wa-msg-ai { font-size: 0.65em; color: #25d366; font-weight: 600; }
    .wa-msg-status { font-size: 0.7em; color: #53bdeb; }

    /* Input */
    .wa-input-area { padding: 10px 16px; background: #f0f2f5; display: flex; gap: 8px; align-items: center; }
    .wa-input-area input[type="text"] { flex: 1; border: none; border-radius: 20px; padding: 10px 16px; font-size: 0.9em; outline: none; }
    .wa-send-btn { width: 42px; height: 42px; border-radius: 50%; background: #075e54; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .wa-send-btn:hover { background: #128c7e; }
    .wa-send-btn:disabled { opacity: 0.5; }
    .wa-attach-btn { width: 42px; height: 42px; border-radius: 50%; background: #fff; color: #54656f; border: 1px solid #d1d7db; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1em; flex-shrink: 0; transition: 0.2s; }
    .wa-attach-btn:hover { background: #e0e0e0; }
    .wa-attach-btn:disabled { opacity: 0.5; }
    .wa-msg .file-msg { display: flex; align-items: center; gap: 8px; padding: 4px 0; }
    .wa-msg .file-msg .file-icon { font-size: 1.6em; color: #128c7e; }
    .wa-msg .file-msg .file-name { font-size: 0.85em; color: #111b21; font-weight: 500; }

    /* Empty state */
    .wa-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; color: #667781; }
    .wa-empty-icon { font-size: 4em; margin-bottom: 15px; opacity: 0.5; }
    .wa-empty h3 { color: #41525d; }

    /* Stats cards */
    .wa-stats { display: flex; gap: 10px; padding: 12px 16px; background: #fff; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; }
    .wa-stat { text-align: center; flex: 1; min-width: 80px; }
    .wa-stat-value { font-size: 1.3em; font-weight: 700; color: #075e54; }
    .wa-stat-label { font-size: 0.7em; color: #667781; }

    /* No config warning */
    .wa-no-config { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin: 20px; text-align: center; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    @media (max-width: 768px) {
        .wa-sidebar { width: 100%; }
        .wa-chat-area { display: none; }
    }
</style>
@endsection

@section('content')
<section class="content no-print" style="padding: 10px;">

    @if(!$isConfigured)
    <div class="wa-no-config">
        <h4>📱 WhatsApp no está configurado</h4>
        <p>Necesitas configurar tu cuenta de WhatsApp Business para recibir y enviar mensajes.</p>
        <a href="{{ url('/whatsapp/settings') }}" class="btn btn-success btn-sm">
            <i class="fab fa-whatsapp"></i> Configurar WhatsApp
        </a>
    </div>
    @endif

    @if(isset($tokenStatus) && !($tokenStatus['valid'] ?? true))
    <div class="alert alert-danger" style="margin: 15px 15px 0; border-left: 4px solid #c0392b;">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <h4 style="margin-top:0;"><i class="fas fa-exclamation-triangle"></i> ¡Token de WhatsApp Expirado!</h4>
        <p>El Access Token de la API de WhatsApp ha expirado o es inválido. Los mensajes de los clientes llegan pero <strong>las respuestas no se envían</strong>.</p>
        <p><strong>Error:</strong> <code>{{ $tokenStatus['error'] ?? 'Desconocido' }}</code></p>
        <p style="margin-bottom:0;">
            <a href="https://developers.facebook.com/apps/2540080753087921/whatsapp-business/wa-dev-console/" target="_blank" class="btn btn-warning btn-sm">
                <i class="fas fa-external-link-alt"></i> Generar Nuevo Token en Meta
            </a>
            <a href="{{ url('/whatsapp/settings') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-key"></i> Actualizar Token Aquí
            </a>
        </p>
    </div>
    @endif

    <div class="wa-container">
        <!-- Sidebar -->
        <div class="wa-sidebar">
            <div class="wa-sidebar-header">
                <h4><i class="fab fa-whatsapp"></i> WhatsApp</h4>
                <div>
                    <button class="btn btn-sm" id="btn_new_chat_header" title="Nuevo chat" style="background:rgba(255,255,255,0.2);color:#fff;border:none;border-radius:50%;width:34px;height:34px;padding:0;margin-right:6px;">
                        <i class="fas fa-plus"></i>
                    </button>
                    <a href="{{ url('/whatsapp/settings') }}" class="text-white" title="Configuración">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </div>

            <div class="wa-stats">
                <div class="wa-stat">
                    <div class="wa-stat-value">{{ $stats['total_messages'] ?? 0 }}</div>
                    <div class="wa-stat-label">Mensajes</div>
                </div>
                <div class="wa-stat">
                    <div class="wa-stat-value">{{ $stats['unique_contacts'] ?? 0 }}</div>
                    <div class="wa-stat-label">Contactos</div>
                </div>
                <div class="wa-stat">
                    <div class="wa-stat-value">{{ $stats['ai_responses'] ?? 0 }}</div>
                    <div class="wa-stat-label">IA 🤖</div>
                </div>
            </div>

            <div class="wa-search">
                <input type="text" id="search_conv" placeholder="🔍 Buscar conversación...">
            </div>

            <div class="wa-conv-list" id="conv_list">
                @forelse($conversations as $conv)
                <div class="wa-conv-item" data-phone="{{ $conv->phone_number }}" data-name="{{ $conv->contact_name }}">
                    <div class="wa-conv-avatar">
                        {{ mb_substr($conv->contact_name ?? '?', 0, 1) }}
                    </div>
                    <div class="wa-conv-info">
                        <div class="wa-conv-name">{{ $conv->contact_name ?? $conv->phone_number }}</div>
                        <div class="wa-conv-last">{{ Str::limit($conv->last_message, 40) }}</div>
                    </div>
                    <div class="wa-conv-meta">
                        <div class="wa-conv-time">{{ \Carbon\Carbon::parse($conv->last_message_at)->format('H:i') }}</div>
                        @if($conv->unread_count > 0)
                        <div class="wa-conv-badge">{{ $conv->unread_count }}</div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center tw-py-8" style="padding: 40px 20px; color: #667781;">
                    <i class="fab fa-whatsapp" style="font-size: 3em; opacity: 0.3;"></i>
                    <p style="margin-top: 10px;">No hay conversaciones aún</p>
                    <small>Los mensajes aparecerán aquí cuando los clientes te escriban</small>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Chat area -->
        <div class="wa-chat-area" id="chat_area">
            <div class="wa-chat-header" id="chat_header" style="display: none;">
                <div class="wa-chat-avatar"><i class="fas fa-user"></i></div>
                <div>
                    <div class="wa-chat-name" id="chat_contact_name">-</div>
                    <div class="wa-chat-status" id="chat_phone">-</div>
                </div>
            </div>

            <div class="wa-messages" id="chat_messages">
                <div class="wa-empty">
                    <div class="wa-empty-icon"><i class="fab fa-whatsapp"></i></div>
                    <h3>WhatsApp Business</h3>
                    <p>Selecciona una conversación o envía un mensaje nuevo</p>
                    <br>
                    <button class="btn btn-success btn-sm" id="btn_new_msg">
                        <i class="fas fa-plus"></i> Nuevo mensaje
                    </button>
                </div>
            </div>

            <div class="wa-input-area" id="input_area" style="display: none;">
                <button type="button" class="wa-attach-btn" id="btn_attach" title="Adjuntar documento o imagen">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="file" id="file_input" style="display:none;"
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt">
                <input type="text" id="wa_input" placeholder="Escribe un mensaje..." autocomplete="off">
                <button class="wa-send-btn" id="btn_send" {{ !$isConfigured ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Modal nuevo mensaje -->
<div class="modal fade" id="modal_new_msg" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: #075e54; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                <h4 class="modal-title"><i class="fab fa-whatsapp"></i> Nuevo mensaje</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre del contacto (opcional):</label>
                    <input type="text" class="form-control" id="new_contact_name" placeholder="Juan Pérez">
                </div>
                <div class="form-group">
                    <label>Número de teléfono:</label>
                    <input type="text" class="form-control" id="new_phone" placeholder="59899123456">
                    <small class="text-muted">Formato: código país + número (ej: 59899123456)</small>
                </div>
                <div class="form-group">
                    <label>Mensaje:</label>
                    <textarea class="form-control" id="new_message" rows="3" placeholder="Hola..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn_send_new">
                    <i class="fab fa-whatsapp"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var currentPhone = null;
    var currentName = null;
    var lastMsgId = 0;
    var pollTimer = null;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Seleccionar conversación
    $(document).on('click', '.wa-conv-item', function() {
        var phone = $(this).data('phone');
        var name = $(this).data('name') || phone;
        currentPhone = phone;
        currentName = name;

        $('.wa-conv-item').removeClass('active');
        $(this).addClass('active');

        loadChat(phone, name);
    });

    // Cargar chat
    function loadChat(phone, name) {
        $('#chat_header').show();
        $('#input_area').show();
        $('#chat_contact_name').text(name);
        $('#chat_phone').text('+' + phone);
        $('#chat_messages').html('<div class="text-center" style="padding:40px;"><i class="fas fa-spinner fa-spin"></i></div>');

        $.get('{{ url("/whatsapp/messages") }}', { phone: phone, last_id: 0 }, function(res) {
            if (res.success) {
                $('#chat_messages').html('');
                res.messages.forEach(function(msg) {
                    appendMessage(msg);
                });
                scrollToBottom();
                lastMsgId = res.messages.length ? res.messages[res.messages.length - 1].id : 0;
            }
        });

        // Iniciar polling
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(function() { pollNewMessages(); }, 5000);
        $('#wa_input').focus();
    }

    // Agregar mensaje al chat
    function appendMessage(msg) {
        var dirClass = msg.direction === 'incoming' ? 'incoming' : 'outgoing';
        var aiTag = msg.is_ai ? '<span class="wa-msg-ai">🤖 IA</span>' : '';
        var statusIcon = '';
        if (msg.direction === 'outgoing') {
            if (msg.status === 'read') statusIcon = '<span class="wa-msg-status">✓✓</span>';
            else if (msg.status === 'delivered') statusIcon = '<span class="wa-msg-status" style="color:#999;">✓✓</span>';
            else if (msg.status === 'sent') statusIcon = '<span class="wa-msg-status" style="color:#999;">✓</span>';
            else if (msg.status === 'failed') statusIcon = '<span style="color:red;font-size:0.7em;">⚠ Error</span>';
        }

        var html = '<div class="wa-msg ' + dirClass + '">' +
            '<div class="wa-msg-text">' + escapeHtml(msg.message || '') + '</div>' +
            '<div class="wa-msg-meta">' + aiTag + '<span class="wa-msg-time">' + msg.time + '</span>' + statusIcon + '</div>' +
            '</div>';
        $('#chat_messages').append(html);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML.replace(/\n/g, '<br>');
    }

    function scrollToBottom() {
        var el = document.getElementById('chat_messages');
        if (el) el.scrollTop = el.scrollHeight;
    }

    // Polling de nuevos mensajes
    function pollNewMessages() {
        if (!currentPhone) return;
        $.get('{{ url("/whatsapp/messages") }}', { phone: currentPhone, last_id: lastMsgId }, function(res) {
            if (res.success && res.messages.length) {
                res.messages.forEach(function(msg) {
                    appendMessage(msg);
                });
                lastMsgId = res.messages[res.messages.length - 1].id;
                scrollToBottom();
            }
        });
    }

    // Enviar mensaje
    function sendMsg(phone, text, contactName) {
        if (!text.trim()) return;

        $.ajax({
            url: '{{ url("/whatsapp/send") }}',
            type: 'POST',
            data: {
                phone: phone,
                message: text,
                contact_name: contactName,
                _token: csrfToken
            },
            success: function(res) {
                if (!res.success) {
                    if (res.token_expired) {
                        toastr.error('⚠️ Token de WhatsApp expirado. Actualízalo en Configuración.', 'Token Expirado', {timeOut: 10000});
                        // Mostrar alerta visual
                        if (!$('#token_expired_alert').length) {
                            var alertHtml = '<div id="token_expired_alert" class="alert alert-danger" style="margin:10px;border-left:4px solid #c0392b;">' +
                                '<strong><i class="fas fa-exclamation-triangle"></i> Token Expirado</strong> - ' +
                                'Las respuestas no se envían al cliente. <a href="{{ url("/whatsapp/settings") }}" class="btn btn-xs btn-warning">Actualizar Token</a></div>';
                            $('.wa-container').before(alertHtml);
                        }
                    } else {
                        toastr.error(res.error || 'Error al enviar');
                    }
                } else if (res.warning) {
                    toastr.warning(res.warning, 'Aviso', {timeOut: 8000});
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            }
        });
    }

    // Enviar desde input
    $('#btn_send').click(function() {
        var text = $('#wa_input').val();
        if (!text.trim() || !currentPhone) return;
        sendMsg(currentPhone, text, currentName);
        // Mostrar inmediatamente
        appendMessage({
            direction: 'outgoing', message: text, time: new Date().toLocaleTimeString('es-UY', {hour:'2-digit',minute:'2-digit'}),
            status: 'sent', is_ai: false
        });
        scrollToBottom();
        $('#wa_input').val('').focus();
    });

    $('#wa_input').keypress(function(e) {
        if (e.which === 13) $('#btn_send').click();
    });

    // Nuevo mensaje - desde botón del empty state o del header
    $('#btn_new_msg, #btn_new_chat_header').click(function() { $('#modal_new_msg').modal('show'); });
    $('#btn_send_new').click(function() {
        var phone = $('#new_phone').val().replace(/[^0-9]/g, '');
        var text = $('#new_message').val();
        var contactName = $('#new_contact_name').val().trim() || phone;
        if (!phone || !text.trim()) { toastr.warning('Completa teléfono y mensaje'); return; }
        sendMsg(phone, text, contactName);

        // Agregar conversación a la lista si no existe
        if (!$('.wa-conv-item[data-phone="' + phone + '"]').length) {
            var initial = contactName.charAt(0).toUpperCase();
            var newConv = '<div class="wa-conv-item" data-phone="' + phone + '" data-name="' + $('<div>').text(contactName).html() + '">' +
                '<div class="wa-conv-avatar">' + initial + '</div>' +
                '<div class="wa-conv-info"><div class="wa-conv-name">' + $('<div>').text(contactName).html() + '</div>' +
                '<div class="wa-conv-last">' + $('<div>').text(text).html().substring(0, 40) + '</div></div>' +
                '<div class="wa-conv-meta"><div class="wa-conv-time">Ahora</div></div></div>';
            $('#conv_list').prepend(newConv);
        }

        // Abrir el chat directamente
        currentPhone = phone;
        currentName = contactName;
        $('.wa-conv-item').removeClass('active');
        $('.wa-conv-item[data-phone="' + phone + '"]').addClass('active');
        loadChat(phone, contactName);

        // Preview del mensaje enviado
        appendMessage({
            direction: 'outgoing', message: text,
            time: new Date().toLocaleTimeString('es-UY', {hour:'2-digit',minute:'2-digit'}),
            status: 'sent', is_ai: false
        });
        scrollToBottom();

        toastr.success('Mensaje enviado a +' + phone);
        $('#modal_new_msg').modal('hide');
        $('#new_phone').val(''); $('#new_message').val(''); $('#new_contact_name').val('');
    });

    // ====== ADJUNTAR ARCHIVOS ======
    $('#btn_attach').click(function() {
        if ({{ $isConfigured ? 'false' : 'true' }}) {
            toastr.warning('WhatsApp no está configurado');
            return;
        }
        if (!currentPhone) {
            toastr.warning('Selecciona una conversación primero');
            return;
        }
        $('#file_input').click();
    });

    $('#file_input').change(function() {
        var file = this.files[0];
        if (!file) return;

        if (file.size > 20 * 1024 * 1024) {
            toastr.error('El archivo supera los 20 MB permitidos');
            $(this).val('');
            return;
        }

        var caption = prompt('Agregar descripción (opcional):', '') || '';
        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('phone', currentPhone);
        formData.append('file', file);
        formData.append('caption', caption);
        formData.append('contact_name', currentName || currentPhone);

        // Preview inmediato en el chat
        var time = new Date().toLocaleTimeString('es-UY', {hour:'2-digit', minute:'2-digit'});
        var isImage = file.type.startsWith('image/');
        var previewHtml = '<div class="wa-msg outgoing">';
        if (isImage) {
            var objUrl = URL.createObjectURL(file);
            previewHtml += '<div class="file-msg"><img src="' + objUrl + '" style="max-width:200px;max-height:160px;border-radius:6px;"></div>';
        } else {
            previewHtml += '<div class="file-msg"><span class="file-icon"><i class="fas fa-file-alt"></i></span>'
                         + '<span class="file-name">' + $('<div>').text(file.name).html() + '</span></div>';
        }
        if (caption) previewHtml += '<div style="font-size:0.85em;margin-top:2px;">' + $('<div>').text(caption).html() + '</div>';
        previewHtml += '<div class="wa-msg-meta"><span class="wa-msg-time">' + time + '</span> <span class="wa-msg-status" style="color:#999;">✓</span></div></div>';
        $('#chat_messages').append(previewHtml);
        scrollToBottom();

        // Deshabilitar mientras sube
        $('#btn_attach').prop('disabled', true);
        $('#btn_attach i').removeClass('fa-paperclip').addClass('fa-spinner fa-spin');

        $.ajax({
            url: '{{ url("/whatsapp/send-document") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    toastr.success('Archivo enviado: ' + res.filename);
                } else {
                    toastr.error(res.error || 'Error al enviar el archivo');
                }
            },
            error: function(xhr) {
                var err = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Error al enviar el archivo';
                toastr.error(err);
            },
            complete: function() {
                $('#btn_attach').prop('disabled', false);
                $('#btn_attach i').removeClass('fa-spinner fa-spin').addClass('fa-paperclip');
                $('#file_input').val('');
            }
        });
    });

    // Buscar conversación
    $('#search_conv').on('input', function() {
        var q = $(this).val().toLowerCase();
        $('.wa-conv-item').each(function() {
            var name = $(this).data('name') || '';
            var phone = $(this).data('phone') || '';
            $(this).toggle(name.toLowerCase().indexOf(q) > -1 || phone.indexOf(q) > -1);
        });
    });
});
</script>
@endsection
