<?php
try {
    $dbh = new PDO("mysql:host=127.0.0.1;port=3306", "root", "");
    $dbh->exec("CREATE DATABASE IF NOT EXISTS sistema_facturacion_2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "OK\n";
} catch (Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
