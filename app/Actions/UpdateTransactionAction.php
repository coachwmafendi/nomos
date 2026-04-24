<?php

namespace App\Actions;

use App\Models\Transaction;

class UpdateTransactionAction
{
    public function handle(int $id, array $data): Transaction
    {
        $transaction = Transaction::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $data['user_id'] = $transaction->user_id;

        $transaction->update($data);

        return $transaction;
    }
}