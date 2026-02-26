<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "COLUMNS:\n";
print_r(Illuminate\Support\Facades\Schema::getColumnListing('products'));
echo "\nPRAGMA:\n";
print_r(DB::select("PRAGMA table_info('products')"));
