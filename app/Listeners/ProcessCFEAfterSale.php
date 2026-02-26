<?php

namespace App\Listeners;

use App\Events\SellCreatedOrModified;
use App\Services\Cfe\CFEService;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessCFEAfterSale
{
    public function __construct(private CFEService $cfeService)
    {
    }

    public function handle(SellCreatedOrModified $event): void
    {
        if (! config('cfe.enabled')) {
            return;
        }

        $transaction = $event->transaction;

        if ($transaction->type !== 'sell' || $transaction->status !== 'final') {
            return;
        }

        try {
            $result = $this->cfeService->processTransaction($transaction);

            if (! $result['success']) {
                Log::warning('CFE no se pudo procesar', [
                    'transaction_id' => $transaction->id,
                    'errors' => $result['errors'] ?? [],
                ]);
            }
        } catch (Throwable $throwable) {
            Log::error('Error inesperado procesando CFE', [
                'transaction_id' => $transaction->id,
                'message' => $throwable->getMessage(),
            ]);
        }
    }
}
