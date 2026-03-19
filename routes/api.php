<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\ProductApiController;
use App\Http\Controllers\Api\V1\ContactApiController;
use App\Http\Controllers\Api\V1\TransactionApiController;
use App\Http\Controllers\Api\V1\CatalogApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// ──────────────────────────────────────────────────────────
// API REST v1 - Autenticación por X-API-KEY header
// ──────────────────────────────────────────────────────────

Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {

    // Public endpoint - API status / health check
    Route::get('/status', function () {
        return response()->json([
            'success' => true,
            'message' => 'StockBA API v1 funcionando correctamente.',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    // Protected endpoints - require X-API-KEY
    Route::middleware('api.auth')->group(function () {

        // ─── Products ──────────────────────────────────
        Route::middleware('api.auth:products.read')->group(function () {
            Route::get('/products', [ProductApiController::class, 'index']);
            Route::get('/products/{id}', [ProductApiController::class, 'show']);
            Route::get('/products/{id}/stock', [ProductApiController::class, 'stock']);
        });

        Route::middleware('api.auth:products.write')->group(function () {
            Route::post('/products', [ProductApiController::class, 'store']);
            Route::put('/products/{id}', [ProductApiController::class, 'update']);
        });

        Route::middleware('api.auth:products.delete')->group(function () {
            Route::delete('/products/{id}', [ProductApiController::class, 'destroy']);
        });

        // ─── Contacts (Customers / Suppliers) ─────────
        Route::middleware('api.auth:contacts.read')->group(function () {
            Route::get('/contacts', [ContactApiController::class, 'index']);
            Route::get('/contacts/{id}', [ContactApiController::class, 'show']);
        });

        Route::middleware('api.auth:contacts.write')->group(function () {
            Route::post('/contacts', [ContactApiController::class, 'store']);
            Route::put('/contacts/{id}', [ContactApiController::class, 'update']);
        });

        Route::middleware('api.auth:contacts.delete')->group(function () {
            Route::delete('/contacts/{id}', [ContactApiController::class, 'destroy']);
        });

        // ─── Transactions (Sells / Purchases) ─────────
        Route::middleware('api.auth:transactions.read')->group(function () {
            Route::get('/sells', [TransactionApiController::class, 'indexSells']);
            Route::get('/sells/{id}', [TransactionApiController::class, 'showSell']);
            Route::get('/purchases', [TransactionApiController::class, 'indexPurchases']);
        });

        // ─── Reports / Summary ────────────────────────
        Route::middleware('api.auth:reports.read')->group(function () {
            Route::get('/summary', [TransactionApiController::class, 'summary']);
        });

        // ─── Catalog (Categories, Brands, Locations) ──
        Route::middleware('api.auth:categories.read')->group(function () {
            Route::get('/categories', [CatalogApiController::class, 'categories']);
        });

        Route::middleware('api.auth:categories.write')->group(function () {
            Route::post('/categories', [CatalogApiController::class, 'storeCategory']);
        });

        Route::middleware('api.auth:brands.read')->group(function () {
            Route::get('/brands', [CatalogApiController::class, 'brands']);
        });

        Route::middleware('api.auth:brands.write')->group(function () {
            Route::post('/brands', [CatalogApiController::class, 'storeBrand']);
        });

        // Locations (read-only, requires any read permission)
        Route::get('/locations', [CatalogApiController::class, 'locations']);

        // ─── Stock ────────────────────────────────────
        Route::middleware('api.auth:stock.read')->group(function () {
            Route::get('/stock', [ProductApiController::class, 'index']); // Same as products with stock info
        });
    });
});
