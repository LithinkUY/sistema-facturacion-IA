@extends('layouts.app')
@section('title', "Chat WhatsApp - {$contactName}")

@section('css')
<style>
    .wa-full-chat { height: calc(100vh - 120px); display: flex; flex-direction: column; background: #e5ddd5; border-radius: 8px; overflow: hidden; }
    .wa-full-header { padding: 12px 20px; background: #075e54; color: #fff; display: flex; align-items: center; gap: 12px; }
    .wa-full-header .avatar { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.1em; }
    .wa-full-header .info h4 { margin: 0; font-size: 1em; }
    .wa-full-header .info small { opacity: 0.8; }
    .wa-full-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 4px; }
    .wa-bubble { max-width: 70%; padding: 8px 12px 4px; border-radius: 8px; font-size: 0.9em; line-height: 1.4; word-wrap: break-word; }
    .wa-bubble.in { background: #fff; align-self: flex-start; border-top-left-radius: 0; }
    .wa-bubble.out { background: #d9fdd3; align-self: flex-end; border-top-right-radius: 0; }
    .wa-bubble .meta { font-size: 0.7em; color: #667781; text-align: right; margin-top: 2px; }
    .wa-bubble .ai-badge { font-size: 0.65em; color: #25d366; font-weight: bold; }
    .wa-full-input { padding: 10px 16px; background: #f0f2f5; display: flex; gap: 8px; align-items: center; }
    .wa-full-input input[type="text"] { flex: 1; border: none; border-radius: 20px; padding: 10px 16px; outline: none; }
    .wa-full-input .send { width: 42px; height: 42px; border-radius: 50%; background: #075e54; color: #fff; border: none; cursor: pointer; }
    .wa-full-input .attach { width: 42px; height: 42px; border-radius: 50%; background: #f0f2f5; color: #54656f; border: 1px solid #d1d7db; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1em; }
    .wa-full-input .attach:hover { background: #e0e0e0; }
    .wa-bubble .file-msg { display: flex; align-items: center; gap: 8px; padding: 4px 0; }
    .wa-bubble .file-msg .file-icon { font-size: 1.5em; color: #128c7e; }
    .wa-bubble .file-msg .file-name { font-size: 0.85em; color: #111b21; font-weight: 500; }
    .wa-full-input .btn-attach { width: 38px; height: 38px; border-radius: 50%; background: #fff; border: 1px solid #ccc; color: #555; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .wa-full-input .btn-attach:hover { background: #e9ecef; }
    #wa_attach_input { display: none; }
    .wa-bubble.media-bubble .media-preview { margin-top: 4px; }
    .wa-bubble.media-bubble .media-preview img { max-width: 220px; border-radius: 6px; }
    .wa-bubble.media-bubble .media-preview .doc-icon { font-size: 2em; color: #075e54; }
</style>
@endsection

@section('content')
<section class="content no-print" style="padding: 10px;">
    <div class="wa-full-chat">
        <div class="wa-full-header">
            <a href="{{ url('/whatsapp') }}" class="text-white"><i class="fas fa-arrow-left"></i></a>
            <div class="avatar"><i class="fas fa-user"></i></div>
            <div class="info">
                <h4>{{ $contactName }}</h4>
                <small>+{{ $phone }}</small>
            </div>
        </div>

        <div class="wa-full-messages" id="messages">
            @foreach($messages as $msg)
            <div class="wa-bubble {{ $msg->direction === 'incoming' ? 'in' : 'out' }}">
                <div>{!! nl2br(e($msg->message)) !!}</div>
                <div class="meta">
                    @if($msg->is_ai_response)<span class="ai-badge">🤖 IA</span>@endif
                    {{ $msg->created_at->format('H:i') }}
                    @if($msg->direction === 'outgoing')
                        @if($msg->status === 'read') <span style="color:#53bdeb;">✓✓</span>
                        @elseif($msg->status === 'delivered') <span style="color:#999;">✓✓</span>
                        @elseif($msg->status === 'sent') <span style="color:#999;">✓</span>
                        @elseif($msg->status === 'failed') <span style="color:red;">⚠</span>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="wa-full-input">
            {{-- Botón adjuntar --}}
            <button class="btn-attach" id="btn_attach" title="Adjuntar documento o imagen" {{ !$isConfigured ? 'disabled' : '' }}>
                <i class="fas fa-paperclip"></i>
            </button>
            <input type="file" id="wa_attach_input" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip">

            <input type="text" id="msg_input" placeholder="Escribe un mensaje..." autocomplete="off" {{ !$isConfigured ? 'disabled' : '' }}>
            <button class="send" id="btn_send" {{ !$isConfigured ? 'disabled' : '' }}><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    var phone = '{{ $phone }}';
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var lastId = {{ $messages->count() > 0 ? $messages->last()->id : 0 }};

    // Scroll al final
    var el = document.getElementById('messages');
    el.scrollTop = el.scrollHeight;

    // Botón adjuntar → abrir selector de archivo
    $('#btn_attach').click(function() {
        $('#wa_attach_input').trigger('click');
    });

    // Cuando se selecciona un archivo
    $('#wa_attach_input').on('change', function() {
        var file = this.files[0];
        if (!file) return;

        var maxMB = 16;
        if (file.size > maxMB * 1024 * 1024) {
            toastr.error('El archivo es muy grande. Máximo ' + maxMB + 'MB.');
            $(this).val('');
            return;
        }

        // Pedir caption opcional
        var caption = window.prompt('Descripción / mensaje para el archivo (opcional):', '');
        if (caption === null) { $(this).val(''); return; } // canceló

        var isImage = file.type.startsWith('image/');
        var previewName = file.name;

        // Mostrar burbuja pendiente
        var time = new Date().toLocaleTimeString('es-UY', {hour:'2-digit', minute:'2-digit'});
        var previewHtml;
        if (isImage) {
            var objectUrl = URL.createObjectURL(file);
            previewHtml = '<div class="wa-bubble out media-bubble">' +
                '<div class="media-preview"><img src="' + objectUrl + '" alt="' + previewName + '"></div>' +
                (caption ? '<div>' + $('<div>').text(caption).html() + '</div>' : '') +
                '<div class="meta">' + time + ' <span style="color:#999;">✓</span></div></div>';
        } else {
            previewHtml = '<div class="wa-bubble out media-bubble">' +
                '<div class="media-preview"><span class="doc-icon"><i class="fas fa-file-alt"></i></span> ' +
                $('<div>').text(previewName).html() + '</div>' +
                (caption ? '<div>' + $('<div>').text(caption).html() + '</div>' : '') +
                '<div class="meta">' + time + ' <span style="color:#999;">⌛</span></div></div>';
        }
        $('#messages').append(previewHtml);
        el.scrollTop = el.scrollHeight;

        // Enviar via AJAX multipart
        var formData = new FormData();
        formData.append('phone', phone);
        formData.append('file', file);
        formData.append('caption', caption);
        formData.append('contact_name', '{{ $contactName }}');
        formData.append('_token', csrfToken);

        $.ajax({
            url: '{{ url("/whatsapp/send-document") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    toastr.success('Archivo enviado correctamente');
                } else {
                    toastr.error(res.error || 'Error al enviar el archivo');
                    if (res.token_expired) {
                        toastr.warning('Actualizá el Access Token en Configuración → WhatsApp');
                    }
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? (xhr.responseJSON.message || JSON.stringify(xhr.responseJSON.errors || {})) : 'Error al enviar';
                toastr.error(msg);
            },
            complete: function() {
                // Limpiar input file para permitir re-selección del mismo archivo
                $('#wa_attach_input').val('');
            }
        });
    });

    // Enviar
    function sendMsg() {
        var text = $('#msg_input').val().trim();
        if (!text) return;

        // Mostrar inmediatamente
        var time = new Date().toLocaleTimeString('es-UY', {hour:'2-digit', minute:'2-digit'});
        $('#messages').append('<div class="wa-bubble out"><div>' + $('<div>').text(text).html() + '</div><div class="meta">' + time + ' <span style="color:#999;">✓</span></div></div>');
        el.scrollTop = el.scrollHeight;
        $('#msg_input').val('').focus();

        $.post('{{ url("/whatsapp/send") }}', {
            phone: phone, message: text, contact_name: '{{ $contactName }}', _token: csrfToken
        }, function(res) {
            if (!res.success) toastr.error(res.error || 'Error al enviar');
        });
    }

    $('#btn_send').click(sendMsg);
    $('#msg_input').keypress(function(e) { if (e.which === 13) sendMsg(); });

    // Botón adjuntar — abre el selector de archivos
    $('#btn_attach').click(function() {
        $('#file_input').click();
    });

    // Al seleccionar un archivo, enviarlo
    $('#file_input').change(function() {
        var file = this.files[0];
        if (!file) return;

        var maxMB = 20;
        if (file.size > maxMB * 1024 * 1024) {
            toastr.error('El archivo supera los ' + maxMB + ' MB permitidos');
            $(this).val('');
            return;
        }

        var caption = prompt('Agregar un mensaje/descripcion (opcional):', '') || '';

        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('phone', phone);
        formData.append('file', file);
        formData.append('caption', caption);
        formData.append('contact_name', '{{ $contactName }}');

        // Mostrar preview en el chat
        var time = new Date().toLocaleTimeString('es-UY', {hour:'2-digit', minute:'2-digit'});
        var isImage = file.type.startsWith('image/');
        var previewHtml;
        if (isImage) {
            var objectUrl = URL.createObjectURL(file);
            previewHtml = '<div class="wa-bubble out"><div class="file-msg"><img src="' + objectUrl + '" style="max-width:200px;max-height:160px;border-radius:6px;"></div>';
        } else {
            previewHtml = '<div class="wa-bubble out"><div class="file-msg"><span class="file-icon"><i class="fas fa-file-alt"></i></span><span class="file-name">' + $("<div>").text(file.name).html() + '</span></div>';
        }
        if (caption) previewHtml += '<div style="font-size:0.85em;margin-top:2px;">' + $("<div>").text(caption).html() + '</div>';
        previewHtml += '<div class="meta">' + time + ' <span style="color:#999;">✓</span></div></div>';
        $('#messages').append(previewHtml);
        el.scrollTop = el.scrollHeight;

        // Deshabilitar botones mientras sube
        $('#btn_attach, #btn_send').prop('disabled', true);
        $('#btn_attach i').removeClass('fa-paperclip').addClass('fa-spinner fa-spin');

        $.ajax({
            url: '{{ url("/whatsapp/send-document") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (!res.success) {
                    toastr.error(res.error || 'Error al enviar el archivo');
                } else {
                    toastr.success('Archivo enviado: ' + res.filename);
                }
            },
            error: function(xhr) {
                var err = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Error al enviar el archivo';
                toastr.error(err);
            },
            complete: function() {
                $('#btn_attach, #btn_send').prop('disabled', false);
                $('#btn_attach i').removeClass('fa-spinner fa-spin').addClass('fa-paperclip');
                $('#file_input').val('');
            }
        });
    });

    // Poll cada 5 segundos
    setInterval(function() {
        $.get('{{ url("/whatsapp/messages") }}', { phone: phone, last_id: lastId }, function(res) {
            if (res.success && res.messages.length) {
                res.messages.forEach(function(msg) {
                    var dirClass = msg.direction === 'incoming' ? 'in' : 'out';
                    var html = '<div class="wa-bubble ' + dirClass + '"><div>' + $('<div>').text(msg.message || '').html().replace(/\n/g,'<br>') + '</div>';
                    html += '<div class="meta">';
                    if (msg.is_ai) html += '<span class="ai-badge">🤖 IA</span> ';
                    html += msg.time;
                    if (msg.direction === 'outgoing') {
                        if (msg.status === 'read') html += ' <span style="color:#53bdeb;">✓✓</span>';
                        else if (msg.status === 'delivered') html += ' <span style="color:#999;">✓✓</span>';
                        else if (msg.status === 'sent') html += ' <span style="color:#999;">✓</span>';
                        else if (msg.status === 'failed') html += ' <span style="color:red;">⚠</span>';
                    }
                    html += '</div></div>';
                    $('#messages').append(html);
                    lastId = msg.id;
                });
                el.scrollTop = el.scrollHeight;
            }
        });
    }, 5000);
});
</script>
@endsection
