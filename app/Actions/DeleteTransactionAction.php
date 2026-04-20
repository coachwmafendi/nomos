<?php

namespace App\Actions;

use App\Models\Transaction;

class DeleteTransactionAction
{
    public function handle(int $id): void
    {
        Transaction::findOrFail($id)->delete();
    }
}