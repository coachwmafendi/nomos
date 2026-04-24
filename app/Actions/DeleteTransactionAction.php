<?php

namespace App\Actions;

use App\Models\Transaction;

class DeleteTransactionAction
{
    public function handle(int $id): void
    {
        $transaction = Transaction::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $transaction->delete();
    }
}