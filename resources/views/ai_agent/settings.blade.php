@extends('layouts.app')
@section('title', 'Configuración Asistente IA')

@section('content')

<section class="content-header">
    <h1>⚙️ Configuración del Asistente IA</h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            {{-- API Key Card --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-key"></i> Google Gemini API Key</h3>
                </div>
                <div class="box-body">
                    <div class="alert alert-info">
                        <strong>ℹ️ ¿Cómo obtener tu API Key gratis?</strong>
                        <ol style="margin-top: 10px; padding-left: 20px;">
                            <li>Ve a <a href="https://aistudio.google.com/apikey" target="_blank"><strong>Google AI Studio</strong></a></li>
                            <li>Inicia sesión con tu cuenta de Google</li>
                            <li>Haz clic en <strong>"Create API Key"</strong></li>
                            <li>Copia la clave y pégala abajo</li>
                        </ol>
                        <small class="text-muted">
                            Plan gratuito: 15 solicitudes/minuto · 1 millón de tokens/día · Sin tarjeta de crédito
                        </small>
                    </div>

                    <form id="form_api_key">
                        @csrf
                        <div class="form-group">
                            <label for="gemini_api_key">API Key:</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="gemini_api_key" name="gemini_api_key"
                                    value="" placeholder="{{ $maskedKey ? $maskedKey : 'AIza...' }}">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" id="btn_toggle_key">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @if($maskedKey)
                                <small class="text-success"><i class="fas fa-check"></i> Ya hay una API Key configurada ({{ $maskedKey }}). Deja en blanco si no quieres cambiarla.</small>
                            @else
                                <small class="text-muted">Pega aquí tu API Key de Google Gemini (empieza con AIza...).</small>
                            @endif
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                            <button type="button" class="btn btn-info" id="btn_test_api">
                                <i class="fas fa-flask"></i> Probar Conexión
                            </button>
                        </div>
                    </form>

                    <div id="test_result" style="display: none; margin-top: 15px;"></div>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-info-circle"></i> Información del Modelo</h3>
                </div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <td><strong>Modelo:</strong></td>
                            <td>Gemini 2.5 Flash Lite (fallback: 2.5 Flash, 2.0 Flash)</td>
                        </tr>
                        <tr>
                            <td><strong>Proveedor:</strong></td>
                            <td>Google AI</td>
                        </tr>
                        <tr>
                            <td><strong>Límite gratuito:</strong></td>
                            <td>15 solicitudes/minuto, 1M tokens/día</td>
                        </tr>
                        <tr>
                            <td><strong>Capacidades:</strong></td>
                            <td>
                                <span class="label label-success">Consultar ventas</span>
                                <span class="label label-success">Buscar productos</span>
                                <span class="label label-success">Revisar órdenes</span>
                                <span class="label label-success">Resumen financiero</span>
                                <span class="label label-info">Responder preguntas</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ url('/ai-agent') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Volver al Chat
                </a>
            </div>
        </div>
    </div>
</section>

@endsection

@section('javascript')
<script>
$(document).ready(function() {
    // Toggle key visibility
    $('#btn_toggle_key').click(function() {
        var $input = $('#gemini_api_key');
        var $icon = $(this).find('i');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Save
    $('#form_api_key').submit(function(e) {
        e.preventDefault();
        var key = $('#gemini_api_key').val().trim();
        if (!key) {
            toastr.warning('Ingresa tu API Key');
            return;
        }
        if (key.indexOf('•') !== -1 || key.indexOf('***') !== -1) {
            toastr.warning('Ingresa la API Key real, no la versión enmascarada');
            return;
        }
        if (!key.startsWith('AIza')) {
            toastr.warning('La API Key de Gemini normalmente empieza con "AIza..."');
        }

        $.ajax({
            url: '{{ url("/ai-agent/save-settings") }}',
            type: 'POST',
            data: { gemini_api_key: key, _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error('Error al guardar');
            }
        });
    });

    // Test connection
    $('#btn_test_api').click(function() {
        var key = $('#gemini_api_key').val();
        if (!key || key.indexOf('***') !== -1) {
            toastr.warning('Primero ingresa y guarda tu API Key');
            return;
        }

        var $btn = $(this);
        var $result = $('#test_result');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Probando...');

        // Primero guardar, luego probar enviando mensaje de prueba
        $.ajax({
            url: '{{ url("/ai-agent/save-settings") }}',
            type: 'POST',
            data: { gemini_api_key: key, _token: '{{ csrf_token() }}' },
            success: function() {
                // Ahora probar la conexión
                $.ajax({
                    url: '{{ url("/ai-agent/send") }}',
                    type: 'POST',
                    data: {
                        message: 'Hola, ¿estás funcionando? Responde solo "Sí, estoy funcionando correctamente".',
                        session_id: 'test-' + Date.now(),
                        _token: '{{ csrf_token() }}'
                    },
                    timeout: 30000,
                    success: function(res) {
                        $btn.prop('disabled', false).html('<i class="fas fa-flask"></i> Probar Conexión');
                        if (res.success) {
                            $result.show().html(
                                '<div class="alert alert-success">' +
                                '<strong>✅ Conexión exitosa!</strong><br>' +
                                'Respuesta: ' + res.message +
                                (res.tokens_used ? '<br><small>Tokens usados: ' + res.tokens_used + '</small>' : '') +
                                '</div>'
                            );
                        } else {
                            $result.show().html(
                                '<div class="alert alert-danger">' +
                                '<strong>❌ Error:</strong> ' + res.message +
                                '</div>'
                            );
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html('<i class="fas fa-flask"></i> Probar Conexión');
                        $result.show().html(
                            '<div class="alert alert-danger">' +
                            '<strong>❌ Error de conexión.</strong> Verifica tu API Key.' +
                            '</div>'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endsection
