<?php

namespace App\Actions;

use App\Models\Transaction;

class CreateTransactionAction
{
    public function handle(array $data): Transaction
    {
        $transaction = new Transaction($data);
        $transaction->user_id = auth()->id();
        $transaction->save();

        return $transaction;
    }
}
