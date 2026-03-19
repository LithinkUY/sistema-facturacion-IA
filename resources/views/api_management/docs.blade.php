@extends('layouts.app')
@section('title', 'Documentación API REST')

@section('content')
<section class="content-header">
    <h1>
        <i class="fas fa-book"></i> Documentación API REST v1
        <small>Referencia completa de endpoints</small>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-3">
            {{-- Navigation --}}
            <div class="box box-solid">
                <div class="box-header with-border bg-green">
                    <h3 class="box-title">Índice</h3>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="#auth"><i class="fas fa-lock"></i> Autenticación</a></li>
                        <li><a href="#products"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="#contacts"><i class="fas fa-users"></i> Contactos</a></li>
                        <li><a href="#sells"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
                        <li><a href="#purchases"><i class="fas fa-truck"></i> Compras</a></li>
                        <li><a href="#categories"><i class="fas fa-tags"></i> Categorías</a></li>
                        <li><a href="#brands"><i class="fas fa-copyright"></i> Marcas</a></li>
                        <li><a href="#locations"><i class="fas fa-map-marker"></i> Ubicaciones</a></li>
                        <li><a href="#summary"><i class="fas fa-chart-bar"></i> Resumen</a></li>
                        <li><a href="#errors"><i class="fas fa-exclamation-circle"></i> Errores</a></li>
                        <li><a href="#examples"><i class="fas fa-code"></i> Ejemplos</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            {{-- Base URL --}}
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-globe"></i> URL Base</h3>
                </div>
                <div class="box-body">
                    <pre class="bg-black" style="color: #0f0; padding: 15px; border-radius: 4px;">{{ url('/api/v1') }}</pre>
                    <p>Todas las peticiones deben incluir el header <code>X-API-KEY</code> (excepto <code>/status</code>).</p>
                </div>
            </div>

            {{-- Authentication --}}
            <div class="box box-success" id="auth">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-lock"></i> Autenticación</h3>
                </div>
                <div class="box-body">
                    <p>La API utiliza autenticación por <strong>API Key</strong> enviada en el header HTTP:</p>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">X-API-KEY: sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</pre>
                    
                    <h4>Permisos</h4>
                    <p>Cada API Key tiene permisos específicos. Si intentas acceder a un endpoint sin el permiso requerido, recibirás un error <code>403</code>.</p>
                    
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr><th>Permiso</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>products.read</code></td><td>Leer productos y stock</td></tr>
                            <tr><td><code>products.write</code></td><td>Crear y editar productos</td></tr>
                            <tr><td><code>products.delete</code></td><td>Eliminar productos</td></tr>
                            <tr><td><code>contacts.read</code></td><td>Leer clientes y proveedores</td></tr>
                            <tr><td><code>contacts.write</code></td><td>Crear y editar contactos</td></tr>
                            <tr><td><code>contacts.delete</code></td><td>Eliminar contactos</td></tr>
                            <tr><td><code>transactions.read</code></td><td>Leer ventas y compras</td></tr>
                            <tr><td><code>categories.read</code></td><td>Leer categorías</td></tr>
                            <tr><td><code>categories.write</code></td><td>Crear categorías</td></tr>
                            <tr><td><code>brands.read</code></td><td>Leer marcas</td></tr>
                            <tr><td><code>brands.write</code></td><td>Crear marcas</td></tr>
                            <tr><td><code>stock.read</code></td><td>Leer inventario</td></tr>
                            <tr><td><code>reports.read</code></td><td>Leer reportes y resúmenes</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Products --}}
            <div class="box box-info" id="products">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-box"></i> Productos</h3>
                </div>
                <div class="box-body">
                    {{-- List Products --}}
                    <h4><span class="label label-success">GET</span> /products</h4>
                    <p>Lista productos con paginación y filtros.</p>
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Parámetro</th><th>Tipo</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>name</code></td><td>string</td><td>Filtrar por nombre (búsqueda parcial)</td></tr>
                            <tr><td><code>sku</code></td><td>string</td><td>Filtrar por SKU</td></tr>
                            <tr><td><code>category_id</code></td><td>integer</td><td>Filtrar por categoría</td></tr>
                            <tr><td><code>brand_id</code></td><td>integer</td><td>Filtrar por marca</td></tr>
                            <tr><td><code>type</code></td><td>string</td><td>single, variable, combo</td></tr>
                            <tr><td><code>active</code></td><td>boolean</td><td>1 = activos, 0 = inactivos</td></tr>
                            <tr><td><code>updated_since</code></td><td>datetime</td><td>Productos modificados desde esta fecha</td></tr>
                            <tr><td><code>per_page</code></td><td>integer</td><td>Resultados por página (max 100, default 25)</td></tr>
                            <tr><td><code>page</code></td><td>integer</td><td>Número de página</td></tr>
                        </tbody>
                    </table>

                    <hr>

                    {{-- Show Product --}}
                    <h4><span class="label label-success">GET</span> /products/{lbrace}id{rbrace}</h4>
                    <p>Obtener detalle de un producto incluyendo variaciones.</p>

                    <hr>

                    {{-- Create Product --}}
                    <h4><span class="label label-warning">POST</span> /products</h4>
                    <p>Crear un nuevo producto.</p>
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Requerido</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>name</code></td><td>string</td><td>Sí</td><td>Nombre del producto</td></tr>
                            <tr><td><code>selling_price</code></td><td>number</td><td>Sí</td><td>Precio de venta</td></tr>
                            <tr><td><code>purchase_price</code></td><td>number</td><td>No</td><td>Precio de compra</td></tr>
                            <tr><td><code>sku</code></td><td>string</td><td>No</td><td>SKU (se genera automáticamente)</td></tr>
                            <tr><td><code>category_id</code></td><td>integer</td><td>No</td><td>ID de categoría</td></tr>
                            <tr><td><code>brand_id</code></td><td>integer</td><td>No</td><td>ID de marca</td></tr>
                            <tr><td><code>unit_id</code></td><td>integer</td><td>No</td><td>ID de unidad</td></tr>
                            <tr><td><code>description</code></td><td>string</td><td>No</td><td>Descripción</td></tr>
                            <tr><td><code>alert_quantity</code></td><td>number</td><td>No</td><td>Cantidad de alerta de stock</td></tr>
                        </tbody>
                    </table>

                    <hr>

                    {{-- Update Product --}}
                    <h4><span class="label label-primary">PUT</span> /products/{lbrace}id{rbrace}</h4>
                    <p>Actualizar un producto. Solo envía los campos que quieres cambiar.</p>

                    <hr>

                    {{-- Delete Product --}}
                    <h4><span class="label label-danger">DELETE</span> /products/{lbrace}id{rbrace}</h4>
                    <p>Eliminar un producto.</p>

                    <hr>

                    {{-- Stock --}}
                    <h4><span class="label label-success">GET</span> /products/{lbrace}id{rbrace}/stock</h4>
                    <p>Obtener stock por ubicación de un producto.</p>
                </div>
            </div>

            {{-- Contacts --}}
            <div class="box box-info" id="contacts">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-users"></i> Contactos</h3>
                </div>
                <div class="box-body">
                    <h4><span class="label label-success">GET</span> /contacts</h4>
                    <p>Lista contactos (clientes, proveedores o ambos).</p>
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Parámetro</th><th>Tipo</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>type</code></td><td>string</td><td>customer, supplier, both</td></tr>
                            <tr><td><code>name</code></td><td>string</td><td>Buscar por nombre</td></tr>
                            <tr><td><code>email</code></td><td>string</td><td>Buscar por email</td></tr>
                            <tr><td><code>mobile</code></td><td>string</td><td>Buscar por móvil</td></tr>
                            <tr><td><code>tax_number</code></td><td>string</td><td>Buscar por RUT/RUC</td></tr>
                            <tr><td><code>active</code></td><td>boolean</td><td>1 = activos, 0 = inactivos</td></tr>
                        </tbody>
                    </table>

                    <hr>
                    <h4><span class="label label-success">GET</span> /contacts/{lbrace}id{rbrace}</h4>
                    <p>Detalle de un contacto con campos personalizados y totales.</p>

                    <hr>
                    <h4><span class="label label-warning">POST</span> /contacts</h4>
                    <p>Crear contacto.</p>
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Requerido</th></tr></thead>
                        <tbody>
                            <tr><td><code>type</code></td><td>string</td><td>Sí (customer/supplier/both)</td></tr>
                            <tr><td><code>name</code></td><td>string</td><td>Sí</td></tr>
                            <tr><td><code>email</code></td><td>string</td><td>No</td></tr>
                            <tr><td><code>mobile</code></td><td>string</td><td>No</td></tr>
                            <tr><td><code>tax_number</code></td><td>string</td><td>No</td></tr>
                            <tr><td><code>address</code></td><td>string</td><td>No</td></tr>
                            <tr><td><code>city</code></td><td>string</td><td>No</td></tr>
                            <tr><td><code>state</code></td><td>string</td><td>No</td></tr>
                            <tr><td><code>country</code></td><td>string</td><td>No</td></tr>
                        </tbody>
                    </table>

                    <hr>
                    <h4><span class="label label-primary">PUT</span> /contacts/{lbrace}id{rbrace}</h4>
                    <p>Actualizar contacto.</p>

                    <hr>
                    <h4><span class="label label-danger">DELETE</span> /contacts/{lbrace}id{rbrace}</h4>
                    <p>Eliminar contacto (solo si no tiene transacciones).</p>
                </div>
            </div>

            {{-- Sells --}}
            <div class="box box-info" id="sells">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-shopping-cart"></i> Ventas</h3>
                </div>
                <div class="box-body">
                    <h4><span class="label label-success">GET</span> /sells</h4>
                    <p>Lista ventas con filtros.</p>
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Parámetro</th><th>Tipo</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>status</code></td><td>string</td><td>final, draft, quotation</td></tr>
                            <tr><td><code>payment_status</code></td><td>string</td><td>paid, due, partial</td></tr>
                            <tr><td><code>contact_id</code></td><td>integer</td><td>ID del cliente</td></tr>
                            <tr><td><code>date_from</code></td><td>date</td><td>Desde fecha (YYYY-MM-DD)</td></tr>
                            <tr><td><code>date_to</code></td><td>date</td><td>Hasta fecha</td></tr>
                            <tr><td><code>invoice_no</code></td><td>string</td><td>Buscar por nº factura</td></tr>
                        </tbody>
                    </table>

                    <hr>
                    <h4><span class="label label-success">GET</span> /sells/{lbrace}id{rbrace}</h4>
                    <p>Detalle de venta con líneas de producto y pagos.</p>
                </div>
            </div>

            {{-- Purchases --}}
            <div class="box box-info" id="purchases">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-truck"></i> Compras</h3>
                </div>
                <div class="box-body">
                    <h4><span class="label label-success">GET</span> /purchases</h4>
                    <p>Lista compras con filtros similares a ventas.</p>
                </div>
            </div>

            {{-- Categories & Brands --}}
            <div class="box box-info" id="categories">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-tags"></i> Categorías y Marcas</h3>
                </div>
                <div class="box-body">
                    <h4><span class="label label-success">GET</span> /categories</h4>
                    <p>Lista categorías con subcategorías.</p>
                    
                    <h4><span class="label label-warning">POST</span> /categories</h4>
                    <p>Crear categoría. Campos: <code>name</code> (requerido), <code>short_code</code>, <code>description</code>, <code>parent_id</code>.</p>

                    <hr>

                    <h4 id="brands"><span class="label label-success">GET</span> /brands</h4>
                    <p>Lista marcas.</p>
                    
                    <h4><span class="label label-warning">POST</span> /brands</h4>
                    <p>Crear marca. Campos: <code>name</code> (requerido), <code>description</code>.</p>
                </div>
            </div>

            {{-- Locations --}}
            <div class="box box-info" id="locations">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-map-marker"></i> Ubicaciones</h3>
                </div>
                <div class="box-body">
                    <h4><span class="label label-success">GET</span> /locations</h4>
                    <p>Lista las ubicaciones/sucursales del negocio.</p>
                </div>
            </div>

            {{-- Summary --}}
            <div class="box box-info" id="summary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-chart-bar"></i> Resumen</h3>
                </div>
                <div class="box-body">
                    <h4><span class="label label-info">GET</span> /summary</h4>
                    <p>Resumen de ventas, compras, gastos y ganancias para un período.</p>
                    <table class="table table-bordered table-condensed">
                        <thead><tr><th>Parámetro</th><th>Tipo</th><th>Default</th></tr></thead>
                        <tbody>
                            <tr><td><code>date_from</code></td><td>date</td><td>Inicio del mes actual</td></tr>
                            <tr><td><code>date_to</code></td><td>date</td><td>Hoy</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Errors --}}
            <div class="box box-danger" id="errors">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-exclamation-circle"></i> Códigos de Error</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead><tr><th>Código HTTP</th><th>Código</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td>401</td><td><code>MISSING_API_KEY</code></td><td>No se envió el header X-API-KEY</td></tr>
                            <tr><td>401</td><td><code>INVALID_API_KEY</code></td><td>API Key no encontrada</td></tr>
                            <tr><td>403</td><td><code>API_KEY_INACTIVE</code></td><td>Key desactivada, expirada o IP no permitida</td></tr>
                            <tr><td>403</td><td><code>INSUFFICIENT_PERMISSIONS</code></td><td>La key no tiene el permiso requerido</td></tr>
                            <tr><td>404</td><td><code>NOT_FOUND</code></td><td>Recurso no encontrado</td></tr>
                            <tr><td>409</td><td><code>HAS_DEPENDENCIES</code></td><td>No se puede eliminar por dependencias</td></tr>
                            <tr><td>422</td><td><code>VALIDATION_ERROR</code></td><td>Error de validación en los datos enviados</td></tr>
                            <tr><td>429</td><td>-</td><td>Demasiadas peticiones (rate limit: 60/min)</td></tr>
                            <tr><td>500</td><td><code>CREATE_ERROR</code></td><td>Error interno al crear recurso</td></tr>
                        </tbody>
                    </table>

                    <h4>Formato de respuesta de error:</h4>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">{
    "success": false,
    "error": "Mensaje descriptivo del error",
    "code": "ERROR_CODE"
}</pre>
                </div>
            </div>

            {{-- Examples --}}
            <div class="box box-success" id="examples">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fas fa-code"></i> Ejemplos de Uso</h3>
                </div>
                <div class="box-body">
                    <h4>cURL - Listar productos</h4>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">curl -X GET "{{ url('/api/v1/products') }}?per_page=10" \
  -H "X-API-KEY: sk_tu_api_key_aqui"</pre>

                    <h4>cURL - Crear producto</h4>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">curl -X POST "{{ url('/api/v1/products') }}" \
  -H "X-API-KEY: sk_tu_api_key_aqui" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Producto desde API",
    "selling_price": 150.00,
    "purchase_price": 80.00,
    "category_id": 1
  }'</pre>

                    <h4>JavaScript (fetch)</h4>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">const response = await fetch('{{ url('/api/v1/products') }}', {
    headers: {
        'X-API-KEY': 'sk_tu_api_key_aqui',
        'Accept': 'application/json'
    }
});
const data = await response.json();
console.log(data);</pre>

                    <h4>PHP</h4>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">$ch = curl_init('{{ url('/api/v1/products') }}');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: sk_tu_api_key_aqui',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch));
curl_close($ch);</pre>

                    <h4>Python</h4>
                    <pre class="bg-black" style="color: #f8f8f2; padding: 15px;">import requests

headers = {'X-API-KEY': 'sk_tu_api_key_aqui'}
response = requests.get('{{ url('/api/v1/products') }}', headers=headers)
data = response.json()
print(data)</pre>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
// Smooth scrolling for navigation
$(document).ready(function() {
    $('a[href^="#"]').click(function(e) {
        e.preventDefault();
        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({ scrollTop: target.offset().top - 60 }, 500);
        }
    });
});
</script>
@endsection
