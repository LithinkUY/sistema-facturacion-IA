@extends('layouts.app')
@section('title', 'Configuración CFE - DGI Uruguay')

@section('content')
<section class="content-header no-print">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        <i class="fas fa-cog"></i> Configuración CFE
        <small>Comprobantes Fiscales Electrónicos - DGI Uruguay</small>
    </h1>
</section>

<section class="content no-print">
    @if(session('status'))
        <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('status.msg') }}
        </div>
    @endif

    {!! Form::open(['route' => 'cfe.save-settings', 'method' => 'POST', 'id' => 'cfe_settings_form']) !!}
    
    <div class="row">
        <div class="col-md-6">
            {{-- Configuración General --}}
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Configuración General'])
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="cfe_enabled" value="1" 
                               {{ ($cfe_settings['cfe_enabled'] ?? false) ? 'checked' : '' }}>
                        <strong>Habilitar Facturación Electrónica</strong>
                    </label>
                    <p class="help-block">Activa el módulo de CFE para emitir comprobantes fiscales electrónicos.</p>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="cfe_auto_submit" value="1"
                               {{ ($cfe_settings['cfe_auto_submit'] ?? false) ? 'checked' : '' }}>
                        <strong>Envío Automático a DGI</strong>
                    </label>
                    <p class="help-block">Enviar automáticamente los CFE a DGI al momento de generarlos.</p>
                </div>

                <div class="form-group">
                    <label for="cfe_environment">Ambiente:</label>
                    <select name="cfe_environment" id="cfe_environment" class="form-control">
                        <option value="testing" {{ ($cfe_settings['cfe_environment'] ?? 'testing') === 'testing' ? 'selected' : '' }}>
                            🧪 Testing (Pruebas)
                        </option>
                        <option value="production" {{ ($cfe_settings['cfe_environment'] ?? '') === 'production' ? 'selected' : '' }}>
                            🏭 Producción
                        </option>
                    </select>
                    <p class="help-block text-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Importante:</strong> Use "Testing" para pruebas y "Producción" solo cuando esté listo para emitir CFE válidos.
                    </p>
                </div>
            @endcomponent

            {{-- Valores por Defecto --}}
            @component('components.widget', ['class' => 'box-info', 'title' => 'Valores por Defecto'])
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cfe_default_type">Tipo de CFE por defecto:</label>
                            <select name="cfe_default_type" id="cfe_default_type" class="form-control">
                                @foreach($cfe_types as $code => $name)
                                    <option value="{{ $code }}" {{ ($cfe_settings['cfe_default_type'] ?? 111) == $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cfe_default_series">Serie por defecto:</label>
                            <input type="text" name="cfe_default_series" id="cfe_default_series" class="form-control"
                                   value="{{ $cfe_settings['cfe_default_series'] ?? 'A' }}" maxlength="2">
                        </div>
                    </div>
                </div>
            @endcomponent
        </div>

        <div class="col-md-6">
            {{-- Datos del Emisor --}}
            @component('components.widget', ['class' => 'box-success', 'title' => 'Datos del Emisor (Empresa)'])
                <div class="form-group">
                    <label for="cfe_emitter_rut">RUT Emisor: <span class="text-danger">*</span></label>
                    <input type="text" name="cfe_emitter_rut" id="cfe_emitter_rut" class="form-control"
                           value="{{ $cfe_settings['cfe_emitter_rut'] ?? $business->tax_number_1 }}"
                           placeholder="Ej: 212345678901" maxlength="12">
                    <p class="help-block">RUT de 12 dígitos sin guiones ni puntos.</p>
                </div>

                <div class="form-group">
                    <label for="cfe_dgi_user_rut">RUT Usuario DGI:</label>
                    <input type="text" name="cfe_dgi_user_rut" id="cfe_dgi_user_rut" class="form-control"
                           value="{{ $cfe_settings['cfe_dgi_user_rut'] ?? $business->tax_number_1 ?? '' }}"
                           placeholder="RUT del usuario autorizado en DGI">
                    <p class="help-block">RUT del usuario que autenticará con DGI (puede ser el mismo del emisor).</p>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Empresa actual:</strong> {{ $business->name }}<br>
                    <strong>RUT registrado:</strong> {{ $business->tax_number_1 ?? 'No configurado' }}
                </div>
            @endcomponent

            {{-- Certificado Digital --}}
            @component('components.widget', ['class' => 'box-warning', 'title' => 'Certificado Digital'])
                <div class="form-group">
                    <label for="cfe_cert_path">Ruta del Certificado (.p12 / .pfx):</label>
                    <input type="text" name="cfe_cert_path" id="cfe_cert_path" class="form-control"
                           value="{{ $cfe_settings['cfe_cert_path'] ?? '' }}"
                           placeholder="Ej: /storage/cfe/certificado.p12">
                    <p class="help-block">Ruta absoluta al archivo del certificado digital.</p>
                </div>

                <div class="form-group">
                    <label for="cfe_cert_password">Contraseña del Certificado:</label>
                    <input type="password" name="cfe_cert_password" id="cfe_cert_password" class="form-control"
                           placeholder="Contraseña del certificado">
                    <p class="help-block">Dejar vacío para mantener la contraseña actual.</p>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-certificate"></i>
                    <strong>Certificado Digital:</strong> Requerido para firmar y enviar CFE a DGI.
                    Debe obtener su certificado en una entidad autorizada por AGESIC.
                </div>
            @endcomponent
        </div>
    </div>

    {{-- Información sobre CFE --}}
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-default', 'title' => 'Información sobre CFE'])
                <div class="row">
                    <div class="col-md-4">
                        <h4><i class="fas fa-file-invoice text-primary"></i> Tipos de CFE</h4>
                        <ul class="list-unstyled">
                            <li><strong>101</strong> - e-Ticket (consumidor final)</li>
                            <li><strong>111</strong> - e-Factura (empresas con RUT)</li>
                            <li><strong>102/112</strong> - Notas de Crédito</li>
                            <li><strong>103/113</strong> - Notas de Débito</li>
                            <li><strong>181</strong> - e-Remito</li>
                            <li><strong>201</strong> - e-Resguardo</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h4><i class="fas fa-percentage text-success"></i> Tasas de IVA</h4>
                        <ul class="list-unstyled">
                            <li><strong>22%</strong> - Tasa Básica (por defecto)</li>
                            <li><strong>10%</strong> - Tasa Mínima</li>
                            <li><strong>0%</strong> - Exento</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h4><i class="fas fa-link text-info"></i> Enlaces Útiles</h4>
                        <ul class="list-unstyled">
                            <li><a href="https://www.dgi.gub.uy" target="_blank">DGI Uruguay</a></li>
                            <li><a href="https://www.efactura.dgi.gub.uy" target="_blank">Portal e-Factura</a></li>
                            <li><a href="https://www.agesic.gub.uy" target="_blank">AGESIC (Certificados)</a></li>
                        </ul>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    {{-- Botones --}}
    <div class="row">
        <div class="col-md-12">
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
                <a href="{{ route('cfe.index') }}" class="btn btn-default btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </div>

    {!! Form::close() !!}
</section>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    // Validar RUT al salir del campo
    $('#cfe_emitter_rut, #cfe_dgi_user_rut').blur(function() {
        var rut = $(this).val().replace(/[^0-9]/g, '');
        $(this).val(rut);
        
        if (rut.length > 0 && rut.length !== 12) {
            toastr.warning('El RUT debe tener exactamente 12 dígitos');
        }
    });

    // Confirmar antes de cambiar a producción
    $('#cfe_environment').change(function() {
        if ($(this).val() === 'production') {
            swal({
                title: '¿Cambiar a Producción?',
                text: 'En modo Producción, los CFE emitidos serán válidos fiscalmente. Asegúrese de tener todo configurado correctamente.',
                icon: 'warning',
                buttons: ['Cancelar', 'Confirmar'],
                dangerMode: true,
            }).then((confirm) => {
                if (!confirm) {
                    $(this).val('testing');
                }
            });
        }
    });
});
</script>
@endsection
