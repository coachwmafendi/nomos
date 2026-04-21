<?php

namespace App\Actions;

use App\Models\Transaction;

class UpdateTransactionAction
{
     public function handle(int $id, array $data): Transaction
    {
    $transaction = Transaction::findOrFail($id);

    $transaction->update([
        'description' => $data['description'],
        'amount'      => $data['amount'],
        'type'        => $data['type'],
        'category_id'   => $data['category_id'],
        'date'        => \Carbon\Carbon::parse($data['date'])
                            ->setTimeFrom(now()), // ← get format ie:2026-04-20 08:00:10
    ]);

    return $transaction->fresh();
    }
}