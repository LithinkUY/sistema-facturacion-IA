@extends('layouts.app')
@section('title', 'Seguridad - Escáner del Sistema')

@section('content')
<section class="content-header">
    <h1>🛡️ Escáner de Seguridad
        <small>Análisis de virus y amenazas del sistema</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-header with-border bg-green">
                    <h3 class="box-title"><i class="fa fa-shield"></i> Panel de Seguridad</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>Este escáner analiza tu sistema en busca de:</p>
                            <ul>
                                <li>🔴 <strong>Malware y virus</strong> (shells, backdoors, código ofuscado)</li>
                                <li>🟡 <strong>Archivos sospechosos</strong> (PHP en carpetas de uploads)</li>
                                <li>🟡 <strong>Permisos peligrosos</strong> (777 en carpetas críticas)</li>
                                <li>🔵 <strong>Archivos modificados recientemente</strong> (últimos 7 días)</li>
                                <li>🟣 <strong>Integridad de archivos críticos</strong> (index.php, .htaccess, rutas)</li>
                                <li>⚙️ <strong>Configuración de seguridad</strong> (DEBUG, HTTPS, etc.)</li>
                            </ul>
                        </div>
                        <div class="col-md-6 text-center" style="padding-top: 20px;">
                            <button id="btn_scan" class="btn btn-success btn-lg">
                                <i class="fa fa-search"></i> Iniciar Escaneo Completo
                            </button>
                            <br><br>
                            <div id="scan_progress" style="display:none;">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <p class="text-muted">Escaneando archivos... esto puede tomar unos minutos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resultados --}}
    <div id="scan_results" style="display:none;">
        {{-- Resumen --}}
        <div class="row">
            <div class="col-md-3">
                <div class="info-box" id="box_status">
                    <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estado</span>
                        <span class="info-box-number" id="scan_status">-</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-bug"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Malware</span>
                        <span class="info-box-number" id="malware_count">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-warning"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sospechosos</span>
                        <span class="info-box-number" id="suspicious_count">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Modificados (7d)</span>
                        <span class="info-box-number" id="modified_count">0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Malware encontrado --}}
        <div class="row" id="malware_section" style="display:none;">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bug"></i> 🔴 Malware Detectado</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped" id="malware_table">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Amenaza</th>
                                    <th>Línea</th>
                                    <th>Severidad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Archivos sospechosos --}}
        <div class="row" id="suspicious_section" style="display:none;">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-warning"></i> 🟡 Archivos Sospechosos</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped" id="suspicious_table">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Mensaje</th>
                                    <th>Tamaño</th>
                                    <th>Modificado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Configuración --}}
        <div class="row" id="config_section" style="display:none;">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-cog"></i> ⚙️ Configuración de Seguridad</h3>
                    </div>
                    <div class="box-body">
                        <ul id="config_list"></ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Archivos modificados recientemente --}}
        <div class="row" id="modified_section" style="display:none;">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clock-o"></i> 🔵 Archivos Modificados (últimos 7 días)</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-condensed" id="modified_table">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Modificado</th>
                                    <th>Tamaño</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Integridad --}}
        <div class="row" id="integrity_section" style="display:none;">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> 🟣 Problemas de Integridad</h3>
                    </div>
                    <div class="box-body">
                        <ul id="integrity_list"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    $('#btn_scan').click(function() {
        var btn = $(this);
        btn.prop('disabled', true);
        $('#scan_progress').show();
        $('#scan_results').hide();

        $.ajax({
            url: '{{ action([\App\Http\Controllers\SecurityScanController::class, "scan"]) }}',
            method: 'GET',
            dataType: 'json',
            timeout: 300000, // 5 min
            success: function(data) {
                btn.prop('disabled', false);
                $('#scan_progress').hide();
                $('#scan_results').show();
                renderResults(data);
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                $('#scan_progress').hide();
                toastr.error('Error al ejecutar el escaneo. Intentá de nuevo.');
            }
        });
    });

    // Cuarentena
    $(document).on('click', '.btn-quarantine', function() {
        var file = $(this).data('file');
        var row = $(this).closest('tr');
        
        swal({
            title: '¿Mover a cuarentena?',
            text: 'El archivo ' + file + ' será movido a una carpeta segura.',
            icon: 'warning',
            buttons: ['Cancelar', 'Sí, mover'],
            dangerMode: true,
        }).then(function(willDelete) {
            if (willDelete) {
                $.ajax({
                    url: '{{ action([\App\Http\Controllers\SecurityScanController::class, "quarantine"]) }}',
                    method: 'POST',
                    data: {
                        file: file,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            row.fadeOut();
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }
        });
    });
});

function renderResults(data) {
    var summary = data.summary;

    // Status
    if (summary.status === 'clean') {
        $('#scan_status').html('<span class="text-success">✅ LIMPIO</span>');
        $('#box_status .info-box-icon').removeClass('bg-red').addClass('bg-green');
    } else {
        $('#scan_status').html('<span class="text-danger">⚠️ ' + summary.total_issues + ' problema(s)</span>');
        $('#box_status .info-box-icon').removeClass('bg-green').addClass('bg-red')
            .html('<i class="fa fa-times"></i>');
    }

    $('#malware_count').text(summary.malware_found);
    $('#suspicious_count').text(summary.suspicious_files);
    $('#modified_count').text(summary.recently_modified);

    // Malware
    if (data.malware.length > 0) {
        $('#malware_section').show();
        var tbody = $('#malware_table tbody').empty();
        data.malware.forEach(function(item) {
            var severityBadge = item.severity === 'critical' 
                ? '<span class="label label-danger">CRÍTICO</span>' 
                : '<span class="label label-warning">ALTO</span>';
            tbody.append(
                '<tr>' +
                '<td><code>' + item.file + '</code></td>' +
                '<td>' + item.threat + '</td>' +
                '<td>' + item.line + '</td>' +
                '<td>' + severityBadge + '</td>' +
                '<td><button class="btn btn-xs btn-danger btn-quarantine" data-file="' + item.file + '">' +
                '<i class="fa fa-shield"></i> Cuarentena</button></td>' +
                '</tr>'
            );
        });
    }

    // Suspicious
    if (data.suspicious_files.length > 0) {
        $('#suspicious_section').show();
        var tbody = $('#suspicious_table tbody').empty();
        data.suspicious_files.forEach(function(item) {
            tbody.append(
                '<tr>' +
                '<td><code>' + item.file + '</code></td>' +
                '<td>' + item.message + '</td>' +
                '<td>' + item.size + '</td>' +
                '<td>' + item.modified + '</td>' +
                '<td><button class="btn btn-xs btn-danger btn-quarantine" data-file="' + item.file + '">' +
                '<i class="fa fa-shield"></i> Cuarentena</button></td>' +
                '</tr>'
            );
        });
    }

    // Config issues
    if (data.config_issues.length > 0) {
        $('#config_section').show();
        var list = $('#config_list').empty();
        data.config_issues.forEach(function(item) {
            var icon = item.severity === 'critical' ? '🔴' : (item.severity === 'high' ? '🟡' : '🔵');
            list.append('<li>' + icon + ' ' + item.message + '</li>');
        });
    }

    // Modified files
    if (data.recently_modified.length > 0) {
        $('#modified_section').show();
        var tbody = $('#modified_table tbody').empty();
        data.recently_modified.forEach(function(item) {
            tbody.append(
                '<tr>' +
                '<td><code>' + item.file + '</code></td>' +
                '<td>' + item.modified + '</td>' +
                '<td>' + item.size + '</td>' +
                '</tr>'
            );
        });
    }

    // Integrity
    if (data.integrity.length > 0) {
        $('#integrity_section').show();
        var list = $('#integrity_list').empty();
        data.integrity.forEach(function(item) {
            list.append('<li><strong>' + item.file + '</strong>: ' + item.message + '</li>');
        });
    }
}
</script>
@endsection
