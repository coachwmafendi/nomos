<?php

namespace App\Actions;

use App\Models\Transaction;

class CreateTransactionAction
{
     public function handle(array $data): Transaction
{
    return Transaction::create([
        'description' => $data['description'],
        'amount'      => $data['amount'],
        'type'        => $data['type'],
        'category'    => $data['category'],
        'date'        => \Carbon\Carbon::parse($data['date'])
                            ->setTimeFrom(now()), // ← tarikh dari user, masa dari sekarang
    ]);
}
}