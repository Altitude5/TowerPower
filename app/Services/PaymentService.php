<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Handle payment gateway callback/webhook.
     *
     * @param  array{gateway: string, ref: string, order_id: int}  $payload
     */
    public static function handleGatewayCallback(array $payload): Transaction
    {
        return DB::transaction(function () use ($payload) {
            // Idempotency check
            $existing = Transaction::where('gateway', $payload['gateway'])
                ->where('transaction_reference', $payload['ref'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            // Update the pending transaction
            $transaction = Transaction::where('order_id', $payload['order_id'])
                ->where('status', TransactionStatus::Pending)
                ->lockForUpdate()
                ->firstOrFail();

            $transaction->update([
                'status' => TransactionStatus::Completed,
                'transaction_reference' => $payload['ref'],
            ]);

            return $transaction;
        });
    }
}
