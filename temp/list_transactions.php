<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Muestra todas las transacciones con su location_id y nombre de sucursal
$txns = \App\Transaction::with('location')
    ->orderBy('id', 'desc')
    ->limit(15)
    ->get(['id', 'location_id', 'type', 'sub_status', 'invoice_no', 'created_at']);

echo str_pad("ID", 6) . str_pad("location_id", 14) . str_pad("Sucursal", 22) . str_pad("invoice_no", 16) . "sub_status\n";
echo str_repeat("-", 80) . "\n";
foreach ($txns as $t) {
    echo str_pad($t->id, 6)
       . str_pad($t->location_id, 14)
       . str_pad($t->location?->name ?? '?', 22)
       . str_pad($t->invoice_no, 16)
       . $t->sub_status . "\n";
}
