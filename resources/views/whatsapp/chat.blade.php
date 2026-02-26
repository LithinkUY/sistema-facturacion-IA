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
    .wa-full-input input { flex: 1; border: none; border-radius: 20px; padding: 10px 16px; outline: none; }
    .wa-full-input .send { width: 42px; height: 42px; border-radius: 50%; background: #075e54; color: #fff; border: none; cursor: pointer; }
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
