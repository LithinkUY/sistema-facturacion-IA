@extends('layouts.app')
@section('title', 'Gestión de API REST')

@section('content')
<section class="content-header">
    <h1>
        <i class="fas fa-plug"></i> Gestión de API REST
        <small>Conecta tu sistema con webs y aplicaciones externas</small>
    </h1>
</section>

<section class="content">
    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fas fa-key"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total API Keys</span>
                    <span class="info-box-number">{{ $stats['total_keys'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Keys Activas</span>
                    <span class="info-box-number">{{ $stats['active_keys'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fas fa-exchange-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Peticiones Hoy</span>
                    <span class="info-box-number">{{ $stats['total_requests_today'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-red">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Errores Hoy</span>
                    <span class="info-box-number">{{ $stats['errors_today'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- API Keys Table --}}
        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-key"></i> API Keys</h3>
                    <div class="box-tools">
                        <a href="{{ route('api.docs') }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-book"></i> Documentación API
                        </a>
                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createKeyModal">
                            <i class="fas fa-plus"></i> Nueva API Key
                        </button>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    @if($apiKeys->isEmpty())
                        <div class="text-center text-muted" style="padding: 40px;">
                            <i class="fas fa-plug" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <h4>No hay API Keys creadas</h4>
                            <p>Crea tu primera API Key para conectar sistemas externos.</p>
                            <button class="btn btn-success" data-toggle="modal" data-target="#createKeyModal">
                                <i class="fas fa-plus"></i> Crear API Key
                            </button>
                        </div>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>API Key</th>
                                    <th>Estado</th>
                                    <th>Último Uso</th>
                                    <th>Expira</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($apiKeys as $key)
                                <tr>
                                    <td>
                                        <strong>{{ $key->name }}</strong>
                                        <br><small class="text-muted">Creada por: {{ $key->creator->first_name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <code class="api-key-display" id="key-{{ $key->id }}">{{ substr($key->api_key, 0, 12) }}...{{ substr($key->api_key, -4) }}</code>
                                        <button class="btn btn-xs btn-default copy-key" data-key="{{ $key->api_key }}" title="Copiar">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </td>
                                    <td>
                                        @if($key->is_active)
                                            <span class="label label-success">Activa</span>
                                        @else
                                            <span class="label label-danger">Inactiva</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($key->last_used_at)
                                            <span title="{{ $key->last_used_at->format('d/m/Y H:i:s') }}">
                                                {{ $key->last_used_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="text-muted">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($key->expires_at)
                                            @if($key->expires_at->isPast())
                                                <span class="text-danger">Expirada</span>
                                            @else
                                                {{ $key->expires_at->format('d/m/Y') }}
                                            @endif
                                        @else
                                            <span class="text-muted">Sin expiración</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-xs btn-primary edit-key" 
                                                    data-id="{{ $key->id }}"
                                                    data-name="{{ $key->name }}"
                                                    data-permissions="{{ json_encode($key->permissions) }}"
                                                    data-ips="{{ $key->allowed_ips ? implode(', ', $key->allowed_ips) : '' }}"
                                                    data-expires="{{ $key->expires_at ? $key->expires_at->format('Y-m-d') : '' }}"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-xs {{ $key->is_active ? 'btn-warning' : 'btn-success' }} toggle-key"
                                                    data-id="{{ $key->id }}" 
                                                    title="{{ $key->is_active ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas {{ $key->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                            <button class="btn btn-xs btn-info view-logs" data-id="{{ $key->id }}" title="Ver Logs">
                                                <i class="fas fa-list"></i>
                                            </button>
                                            <button class="btn btn-xs btn-default regenerate-key" data-id="{{ $key->id }}" title="Regenerar claves">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <button class="btn btn-xs btn-danger delete-key" data-id="{{ $key->id }}" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Info Panel --}}
        <div class="col-md-4">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-info-circle"></i> Cómo Conectar</h3>
                </div>
                <div class="box-body">
                    <h5><strong>1. Crea una API Key</strong></h5>
                    <p class="text-muted">Asigna permisos según lo que necesites.</p>
                    
                    <h5><strong>2. Usa el header X-API-KEY</strong></h5>
                    <pre style="font-size: 11px; background: #2d2d2d; color: #f8f8f2; padding: 10px; border-radius: 4px;">GET /api/v1/products
X-API-KEY: sk_xxxxxxxxxx...</pre>

                    <h5><strong>3. URL Base</strong></h5>
                    <p><code id="base-url">{{ url('/api/v1') }}</code>
                        <button class="btn btn-xs btn-default copy-key" data-key="{{ url('/api/v1') }}">
                            <i class="fas fa-copy"></i>
                        </button>
                    </p>

                    <hr>

                    <h5><strong>Endpoints Disponibles</strong></h5>
                    <table class="table table-condensed" style="font-size: 12px;">
                        <tr><td><span class="label label-success">GET</span></td><td>/products</td></tr>
                        <tr><td><span class="label label-warning">POST</span></td><td>/products</td></tr>
                        <tr><td><span class="label label-success">GET</span></td><td>/contacts</td></tr>
                        <tr><td><span class="label label-warning">POST</span></td><td>/contacts</td></tr>
                        <tr><td><span class="label label-success">GET</span></td><td>/sells</td></tr>
                        <tr><td><span class="label label-success">GET</span></td><td>/purchases</td></tr>
                        <tr><td><span class="label label-success">GET</span></td><td>/categories</td></tr>
                        <tr><td><span class="label label-success">GET</span></td><td>/brands</td></tr>
                        <tr><td><span class="label label-success">GET</span></td><td>/locations</td></tr>
                        <tr><td><span class="label label-info">GET</span></td><td>/summary</td></tr>
                        <tr><td><span class="label label-default">GET</span></td><td>/status</td></tr>
                    </table>

                    <a href="{{ route('api.docs') }}" class="btn btn-block btn-info btn-sm" target="_blank">
                        <i class="fas fa-book"></i> Ver Documentación Completa
                    </a>
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-clock"></i> Actividad Reciente</h3>
                </div>
                <div class="box-body" style="max-height: 300px; overflow-y: auto;">
                    @if($recentLogs->isEmpty())
                        <p class="text-center text-muted">Sin actividad aún</p>
                    @else
                        @foreach($recentLogs->take(15) as $log)
                        <div style="border-bottom: 1px solid #eee; padding: 5px 0; font-size: 12px;">
                            <span class="label label-{{ $log->response_code < 400 ? 'success' : 'danger' }}" style="font-size: 10px;">
                                {{ $log->method }}
                            </span>
                            <code style="font-size: 11px;">{{ Str::limit($log->endpoint, 30) }}</code>
                            <span class="pull-right text-muted">
                                {{ $log->response_time_ms ? $log->response_time_ms . 'ms' : '' }}
                                {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                            </span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Create Key Modal --}}
<div class="modal fade" id="createKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fas fa-plus"></i> Nueva API Key</h4>
            </div>
            <form id="createKeyForm">
                <div class="modal-body">
                    @csrf
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Web stockba.es, App Móvil, ERP externo..." required>
                    </div>

                    <div class="form-group">
                        <label>Permisos <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach($permissions as $key => $label)
                            <div class="col-md-6">
                                <label style="font-weight: normal;">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"> {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <div style="margin-top: 5px;">
                            <button type="button" class="btn btn-xs btn-default" id="selectAllPerms">Seleccionar todos</button>
                            <button type="button" class="btn btn-xs btn-default" id="deselectAllPerms">Deseleccionar todos</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>IPs Permitidas <small class="text-muted">(opcional, separar con comas)</small></label>
                        <input type="text" name="allowed_ips" class="form-control" placeholder="Ej: 185.42.12.1, 192.168.1.100 (vacío = todas)">
                    </div>

                    <div class="form-group">
                        <label>Fecha de Expiración <small class="text-muted">(opcional)</small></label>
                        <input type="date" name="expires_at" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-key"></i> Crear API Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Key Modal --}}
<div class="modal fade" id="editKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fas fa-edit"></i> Editar API Key</h4>
            </div>
            <form id="editKeyForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Permisos <span class="text-danger">*</span></label>
                        <div class="row" id="edit_permissions_container">
                            @foreach($permissions as $key => $label)
                            <div class="col-md-6">
                                <label style="font-weight: normal;">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" class="edit-perm"> {{ $label }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label>IPs Permitidas</label>
                        <input type="text" name="allowed_ips" id="edit_ips" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Fecha de Expiración</label>
                        <input type="date" name="expires_at" id="edit_expires" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Show Keys Modal (after creation) --}}
<div class="modal fade" id="showKeysModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-yellow">
                <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> ¡Guarde estas claves!</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>¡Importante!</strong> El API Secret solo se muestra una vez. Guárdelo en un lugar seguro.
                </div>
                <div class="form-group">
                    <label>API Key:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="new_api_key" readonly>
                        <span class="input-group-btn">
                            <button class="btn btn-default copy-field" data-field="new_api_key"><i class="fas fa-copy"></i></button>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label>API Secret:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="new_api_secret" readonly>
                        <span class="input-group-btn">
                            <button class="btn btn-default copy-field" data-field="new_api_secret"><i class="fas fa-copy"></i></button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="location.reload();">
                    <i class="fas fa-check"></i> He guardado las claves
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Logs Modal --}}
<div class="modal fade" id="logsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fas fa-list"></i> Logs de API</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-condensed table-bordered" id="logsTable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th>Endpoint</th>
                                <th>IP</th>
                                <th>Código</th>
                                <th>Tiempo</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
$(document).ready(function() {

    // Select/Deselect all permissions
    $('#selectAllPerms').click(function() {
        $('#createKeyModal input[type="checkbox"]').prop('checked', true);
    });
    $('#deselectAllPerms').click(function() {
        $('#createKeyModal input[type="checkbox"]').prop('checked', false);
    });

    // Copy to clipboard
    $(document).on('click', '.copy-key', function() {
        var key = $(this).data('key');
        navigator.clipboard.writeText(key).then(function() {
            toastr.success('Copiado al portapapeles');
        });
    });
    $(document).on('click', '.copy-field', function() {
        var fieldId = $(this).data('field');
        var val = $('#' + fieldId).val();
        navigator.clipboard.writeText(val).then(function() {
            toastr.success('Copiado al portapapeles');
        });
    });

    // Create API Key
    $('#createKeyForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '{{ route("api.keys.store") }}',
            type: 'POST',
            data: formData,
            success: function(resp) {
                if (resp.success) {
                    $('#createKeyModal').modal('hide');
                    $('#new_api_key').val(resp.data.api_key);
                    $('#new_api_secret').val(resp.data.api_secret);
                    $('#showKeysModal').modal('show');
                    toastr.success(resp.message);
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON;
                if (errors && errors.errors) {
                    var msg = Object.values(errors.errors).flat().join('<br>');
                    toastr.error(msg);
                } else {
                    toastr.error('Error al crear la API Key');
                }
            }
        });
    });

    // Edit Key - open modal
    $(document).on('click', '.edit-key', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var perms = $(this).data('permissions');
        var ips = $(this).data('ips');
        var expires = $(this).data('expires');

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_ips').val(ips);
        $('#edit_expires').val(expires);

        // Check permissions
        $('.edit-perm').prop('checked', false);
        if (perms && Array.isArray(perms)) {
            perms.forEach(function(p) {
                $('.edit-perm[value="' + p + '"]').prop('checked', true);
            });
        }

        $('#editKeyModal').modal('show');
    });

    // Save edit
    $('#editKeyForm').submit(function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        var formData = $(this).serialize();
        $.ajax({
            url: '/api-management/keys/' + id,
            type: 'PUT',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(resp) {
                if (resp.success) {
                    toastr.success(resp.message);
                    location.reload();
                }
            },
            error: function() { toastr.error('Error al actualizar'); }
        });
    });

    // Toggle active/inactive
    $(document).on('click', '.toggle-key', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/api-management/keys/' + id + '/toggle',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(resp) {
                if (resp.success) {
                    toastr.success(resp.message);
                    location.reload();
                }
            }
        });
    });

    // Regenerate keys
    $(document).on('click', '.regenerate-key', function() {
        var id = $(this).data('id');
        if (!confirm('¿Regenerar las claves? Las claves anteriores dejarán de funcionar.')) return;
        $.ajax({
            url: '/api-management/keys/' + id + '/regenerate',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(resp) {
                if (resp.success) {
                    $('#new_api_key').val(resp.data.api_key);
                    $('#new_api_secret').val(resp.data.api_secret);
                    $('#showKeysModal').modal('show');
                    toastr.success(resp.message);
                }
            }
        });
    });

    // Delete key
    $(document).on('click', '.delete-key', function() {
        var id = $(this).data('id');
        if (!confirm('¿Eliminar esta API Key? Todos los sistemas que la usen perderán acceso.')) return;
        $.ajax({
            url: '/api-management/keys/' + id,
            type: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(resp) {
                if (resp.success) {
                    toastr.success(resp.message);
                    location.reload();
                }
            }
        });
    });

    // View logs
    $(document).on('click', '.view-logs', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '/api-management/keys/' + id + '/logs',
            type: 'GET',
            success: function(resp) {
                var html = '';
                if (resp.data && resp.data.length > 0) {
                    resp.data.forEach(function(log) {
                        var codeClass = log.response_code < 400 ? 'success' : 'danger';
                        html += '<tr>';
                        html += '<td>' + (log.created_at || '-') + '</td>';
                        html += '<td><span class="label label-default">' + log.method + '</span></td>';
                        html += '<td><code>' + log.endpoint + '</code></td>';
                        html += '<td>' + (log.ip_address || '-') + '</td>';
                        html += '<td><span class="label label-' + codeClass + '">' + log.response_code + '</span></td>';
                        html += '<td>' + (log.response_time_ms ? log.response_time_ms + 'ms' : '-') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>';
                }
                $('#logsTableBody').html(html);
                $('#logsModal').modal('show');
            }
        });
    });
});
</script>
@endsection
