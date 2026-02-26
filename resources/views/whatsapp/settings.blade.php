@extends('layouts.app')
@section('title', 'Configuración WhatsApp')

@section('content')
<section class="content-header">
    <h1><i class="fab fa-whatsapp" style="color: #25d366;"></i> Configuración de WhatsApp Business</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            {{-- Estado de conexión --}}
            <div class="box box-default" id="box_ngrok_status">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-satellite-dish"></i> Estado de Conexión (ngrok)</h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-sm btn-success" onclick="detectNgrok()">
                            <i class="fas fa-sync"></i> Detectar Túnel
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div id="ngrok_status">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="text-muted">Buscando túnel ngrok activo...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Guía paso a paso --}}
            <div class="box box-success collapsed-box">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-book"></i> Guía de Configuración Completa</h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body" style="display:none;">
                    <div class="alert alert-info">
                        <strong>📱 ¿Cómo funciona?</strong>
                        <p>Meta (Facebook) ofrece la API de WhatsApp Business Cloud de forma gratuita para enviar hasta 1,000 conversaciones/mes. Los clientes pueden escribir a tu número de WhatsApp Business y el sistema responde automáticamente con IA.</p>
                    </div>

                    {{-- PASO 1: ngrok --}}
                    <div class="panel panel-info">
                        <div class="panel-heading"><strong>PASO 1:</strong> Configurar ngrok (túnel para acceso desde internet)</div>
                        <div class="panel-body">
                            <ol>
                                <li>Regístrate GRATIS en <a href="https://dashboard.ngrok.com/signup" target="_blank"><strong>ngrok.com</strong></a></li>
                                <li>Ve a <a href="https://dashboard.ngrok.com/get-started/your-authtoken" target="_blank"><strong>Your Authtoken</strong></a> y cópialo</li>
                                <li>Abre una terminal y ejecuta: <code>ngrok config add-authtoken TU_TOKEN_AQUI</code></li>
                                <li>Inicia el túnel: <code>ngrok http 8000</code> (o ejecuta <strong>configurar_whatsapp.bat</strong>)</li>
                                <li>Verás una URL pública como: <code>https://xxxx-xx-xx.ngrok-free.app</code></li>
                            </ol>
                            <div class="alert alert-warning" style="margin-bottom:0;">
                                <i class="fas fa-exclamation-triangle"></i> La URL de ngrok gratis cambia cada vez que reinicias. Para producción necesitas un servidor con IP fija o un dominio de ngrok pagado.
                            </div>
                        </div>
                    </div>

                    {{-- PASO 2: Meta Developer --}}
                    <div class="panel panel-success">
                        <div class="panel-heading"><strong>PASO 2:</strong> Crear App en Meta for Developers</div>
                        <div class="panel-body">
                            <ol>
                                <li>Ve a <a href="https://developers.facebook.com/" target="_blank"><strong>Meta for Developers</strong></a> e inicia sesión</li>
                                <li>Crea una nueva App → Tipo: <strong>"Other"</strong> → Tipo de app: <strong>"Business"</strong></li>
                                <li>Agrega el producto <strong>"WhatsApp"</strong> desde el dashboard de tu app</li>
                                <li>Ve a <strong>WhatsApp → API Setup</strong>:
                                    <ul>
                                        <li>Verás un <strong>Phone Number ID</strong> de prueba → cópialo</li>
                                        <li>Haz clic en "Generate" para crear un <strong>Temporary Access Token</strong> (dura 24h)</li>
                                        <li>Para token permanente: <strong>System Users</strong> → Genera token con permisos <code>whatsapp_business_messaging</code> y <code>whatsapp_business_management</code></li>
                                    </ul>
                                </li>
                            </ol>
                        </div>
                    </div>

                    {{-- PASO 3: Webhook --}}
                    <div class="panel panel-warning">
                        <div class="panel-heading"><strong>PASO 3:</strong> Configurar Webhook en Meta</div>
                        <div class="panel-body">
                            <ol>
                                <li>En tu app de Meta → <strong>WhatsApp → Configuration</strong></li>
                                <li>En la sección <strong>Webhook</strong>, haz clic en <strong>"Edit"</strong></li>
                                <li>Pega estos datos:
                                    <table class="table table-bordered" style="margin-top:10px;">
                                        <tr>
                                            <td><strong>Callback URL:</strong></td>
                                            <td>
                                                <code id="webhook_url_guide"><span class="ngrok_url_placeholder">https://TU-URL-NGROK.ngrok-free.app</span>/webhook/whatsapp</code>
                                                <button class="btn btn-xs btn-default" onclick="copyWebhookGuide()"><i class="fas fa-copy"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Verify Token:</strong></td>
                                            <td>
                                                <code id="verify_token_guide">{{ $verifyToken }}</code>
                                                <button class="btn btn-xs btn-default" onclick="copyVerify()"><i class="fas fa-copy"></i></button>
                                            </td>
                                        </tr>
                                    </table>
                                </li>
                                <li>Haz clic en <strong>"Verify and Save"</strong></li>
                                <li>Suscríbete al campo: <strong>messages</strong> ✅</li>
                            </ol>
                        </div>
                    </div>

                    {{-- PASO 4: Configurar aquí --}}
                    <div class="panel panel-primary">
                        <div class="panel-heading"><strong>PASO 4:</strong> Guardar credenciales abajo ↓</div>
                        <div class="panel-body">
                            <p>Pega el <strong>Access Token</strong> y el <strong>Phone Number ID</strong> en el formulario de abajo y haz clic en "Guardar".</p>
                        </div>
                    </div>

                    {{-- PASO 5: Probar --}}
                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>PASO 5:</strong> Probar la conexión</div>
                        <div class="panel-body">
                            <ol>
                                <li>En Meta → WhatsApp → API Setup, envía un mensaje de prueba a tu número</li>
                                <li>Respóndele desde tu WhatsApp personal</li>
                                <li>El mensaje debería aparecer en <a href="{{ url('/whatsapp') }}">la sección WhatsApp</a> del sistema</li>
                                <li>Si la IA está activada, responderá automáticamente</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario de configuración --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-cog"></i> Credenciales API</h3>
                </div>
                <div class="box-body">
                    <form id="form_wa_settings">
                        @csrf
                        <div class="form-group">
                            <label>Access Token (Permanent):</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="access_token" name="access_token"
                                    placeholder="{{ $maskedToken ?: 'EAAxxxxxxx...' }}">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" onclick="toggleField('access_token')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @if($maskedToken)
                                <small class="text-success"><i class="fas fa-check"></i> Token configurado ({{ $maskedToken }})</small>
                            @else
                                <small class="text-muted">Token permanente de la API de WhatsApp Business</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Phone Number ID:</label>
                            <input type="text" class="form-control" id="phone_number_id" name="phone_number_id"
                                value="{{ $phoneNumberId }}" placeholder="1234567890">
                            <small class="text-muted">Lo encuentras en WhatsApp → API Setup</small>
                        </div>

                        <div class="form-group">
                            <label>Verify Token (para webhook):</label>
                            <input type="text" class="form-control" id="verify_token" name="verify_token"
                                value="{{ $verifyToken }}">
                            <small class="text-muted">Token personalizado para verificar el webhook. Usa el mismo al configurar en Meta.</small>
                        </div>

                        <div class="form-group">
                            <label>Respuesta automática con IA:</label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" name="ai_enabled" value="1" {{ $aiEnabled ? 'checked' : '' }}> 
                                    <span style="color: #25d366;"><i class="fas fa-robot"></i> Activada</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="ai_enabled" value="0" {{ !$aiEnabled ? 'checked' : '' }}> 
                                    <span><i class="fas fa-ban"></i> Desactivada</span>
                                </label>
                            </div>
                            <small class="text-muted">Cuando está activada, el agente IA responde automáticamente a los mensajes de WhatsApp</small>
                        </div>

                        <hr>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                            <a href="{{ url('/whatsapp') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info del webhook --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-globe"></i> URL del Webhook</h3>
                </div>
                <div class="box-body">
                    <table class="table">
                        <tr>
                            <td><strong>Callback URL:</strong></td>
                            <td>
                                <code id="webhook_url"><span class="ngrok_url_display">{{ $webhookUrl }}</span></code>
                                <button class="btn btn-xs btn-default" onclick="copyWebhook()"><i class="fas fa-copy"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Verify Token:</strong></td>
                            <td>
                                <code id="verify_token_display">{{ $verifyToken }}</code>
                                <button class="btn btn-xs btn-default" onclick="copyVerify()"><i class="fas fa-copy"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Método verificación:</strong></td>
                            <td>GET</td>
                        </tr>
                        <tr>
                            <td><strong>Método mensajes:</strong></td>
                            <td>POST</td>
                        </tr>
                        <tr>
                            <td><strong>Suscripciones:</strong></td>
                            <td><span class="label label-success">messages</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Test de conexión --}}
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-vial"></i> Probar Conexión</h3>
                </div>
                <div class="box-body">
                    <button type="button" class="btn btn-info" id="btn_test_webhook" onclick="testWebhook()">
                        <i class="fas fa-satellite-dish"></i> Probar Webhook
                    </button>
                    <button type="button" class="btn btn-success" id="btn_test_send" onclick="testSendMessage()">
                        <i class="fab fa-whatsapp"></i> Enviar Mensaje de Prueba
                    </button>
                    <div id="test_result" style="margin-top:15px;"></div>

                    <hr>
                    <div class="form-group">
                        <label>Número de prueba (con código de país):</label>
                        <div class="input-group">
                            <span class="input-group-addon">+</span>
                            <input type="text" class="form-control" id="test_phone" placeholder="59899123456">
                        </div>
                        <small class="text-muted">Ingresa un número que esté registrado como destinatario de prueba en Meta</small>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
var ngrokUrl = null;

function detectNgrok() {
    $('#ngrok_status').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i><p class="text-muted">Buscando túnel ngrok...</p></div>');
    
    // Consultar al servidor Laravel que detecta ngrok desde el backend (evita CORS)
    $.ajax({
        url: '{{ url("/whatsapp/detect-ngrok") }}',
        type: 'GET',
        timeout: 5000,
        success: function(data) {
            if (data.success && data.url) {
                ngrokUrl = data.url;
                var webhookFull = data.webhook_url;
                
                $('#ngrok_status').html(
                    '<div class="alert alert-success" style="margin-bottom:0;">' +
                    '<h4><i class="fas fa-check-circle"></i> ¡Túnel ngrok ACTIVO!</h4>' +
                    '<table class="table table-condensed" style="margin-bottom:5px;">' +
                    '<tr><td><strong>URL Pública:</strong></td><td><code>' + ngrokUrl + '</code></td></tr>' +
                    '<tr><td><strong>Webhook URL:</strong></td><td><code id="ngrok_webhook_url">' + webhookFull + '</code> ' +
                    '<button class="btn btn-xs btn-success" onclick="copyNgrokWebhook()"><i class="fas fa-copy"></i> Copiar</button></td></tr>' +
                    '<tr><td><strong>Verify Token:</strong></td><td><code>{{ $verifyToken }}</code> ' +
                    '<button class="btn btn-xs btn-default" onclick="copyVerify()"><i class="fas fa-copy"></i> Copiar</button></td></tr>' +
                    '</table>' +
                    '<p><small><i class="fas fa-info-circle"></i> Usa estos datos en Meta → WhatsApp → Configuration → Webhook</small></p>' +
                    '</div>'
                );
                
                // Actualizar URLs en toda la página
                $('.ngrok_url_display').text(webhookFull);
                $('.ngrok_url_placeholder').text(ngrokUrl);
                
                $('#box_ngrok_status').removeClass('box-default box-danger').addClass('box-success');
            } else {
                showNgrokNotFound();
            }
        },
        error: function() {
            showNgrokNotFound();
        }
    });
}

function showNgrokNotFound() {
    $('#ngrok_status').html(
        '<div class="alert alert-danger" style="margin-bottom:0;">' +
        '<h4><i class="fas fa-times-circle"></i> Túnel ngrok NO detectado</h4>' +
        '<p>Para conectar tu servidor local con WhatsApp, necesitas iniciar ngrok:</p>' +
        '<ol>' +
        '<li>Abre una terminal/CMD nueva</li>' +
        '<li>Ejecuta: <code>ngrok http 8000</code></li>' +
        '<li>O ejecuta el archivo <strong>configurar_whatsapp.bat</strong></li>' +
        '<li>Luego haz clic en "Detectar Túnel" arriba</li>' +
        '</ol>' +
        '</div>'
    );
    $('#box_ngrok_status').removeClass('box-default box-success').addClass('box-danger');
}

function copyNgrokWebhook() {
    var url = $('#ngrok_webhook_url').text();
    navigator.clipboard.writeText(url);
    toastr.success('Webhook URL copiada: ' + url);
}

function toggleField(id) {
    var $f = $('#' + id);
    $f.attr('type', $f.attr('type') === 'password' ? 'text' : 'password');
}

function copyWebhook() {
    var url = $('.ngrok_url_display').first().text();
    navigator.clipboard.writeText(url);
    toastr.success('URL del webhook copiada');
}

function copyVerify() {
    navigator.clipboard.writeText($('#verify_token_display').text() || $('#verify_token_guide').text());
    toastr.success('Verify token copiado');
}

function copyWebhookGuide() {
    var url = ngrokUrl ? (ngrokUrl + '/webhook/whatsapp') : $('#webhook_url_guide').text();
    navigator.clipboard.writeText(url);
    toastr.success('Webhook URL copiada');
}

function testWebhook() {
    if (!ngrokUrl) {
        toastr.warning('Primero inicia ngrok y haz clic en "Detectar Túnel"');
        return;
    }
    var testUrl = ngrokUrl + '/webhook/whatsapp?hub.mode=subscribe&hub.verify_token={{ $verifyToken }}&hub.challenge=test123';
    
    $('#btn_test_webhook').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Probando...');
    
    $.ajax({
        url: testUrl,
        type: 'GET',
        timeout: 10000,
        success: function(data) {
            $('#test_result').html(
                '<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>¡Webhook funciona!</strong> Respuesta: ' + data + '</div>'
            );
        },
        error: function(xhr) {
            $('#test_result').html(
                '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <strong>Error:</strong> ' + (xhr.statusText || 'No se pudo conectar') + '</div>'
            );
        },
        complete: function() {
            $('#btn_test_webhook').prop('disabled', false).html('<i class="fas fa-satellite-dish"></i> Probar Webhook');
        }
    });
}

function testSendMessage() {
    var phone = $('#test_phone').val();
    if (!phone) {
        toastr.warning('Ingresa un número de teléfono de prueba');
        return;
    }
    
    $('#btn_test_send').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
    
    $.ajax({
        url: '{{ url("/whatsapp/send") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            phone: phone,
            message: '✅ Mensaje de prueba desde el Sistema de Facturación - Publideas UY. ¡La conexión funciona!'
        },
        success: function(res) {
            if (res.success) {
                $('#test_result').html(
                    '<div class="alert alert-success"><i class="fas fa-check-circle"></i> <strong>¡Mensaje enviado!</strong> Revisa WhatsApp en el número +' + phone + '</div>'
                );
            } else {
                $('#test_result').html(
                    '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <strong>Error:</strong> ' + (res.message || 'No se pudo enviar') + '</div>'
                );
            }
        },
        error: function(xhr) {
            var msg = 'Error de conexión';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e) {}
            $('#test_result').html(
                '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <strong>Error:</strong> ' + msg + '</div>'
            );
        },
        complete: function() {
            $('#btn_test_send').prop('disabled', false).html('<i class="fab fa-whatsapp"></i> Enviar Mensaje de Prueba');
        }
    });
}

$(document).ready(function() {
    // Detectar ngrok automáticamente al cargar la página
    detectNgrok();
    
    $('#form_wa_settings').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ url("/whatsapp/save-settings") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message || 'Error al guardar');
                }
            },
            error: function() {
                toastr.error('Error de conexión');
            }
        });
    });
});
</script>
@endsection
