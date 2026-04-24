<?php

namespace App\Actions;

use App\Models\Transaction;

class CreateTransactionAction
{
    public function handle(array $data): Transaction
    {
        $data['user_id'] = auth()->id();

        return Transaction::create($data);
    }
}