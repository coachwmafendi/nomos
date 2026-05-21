<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;

new class extends Component {

public function confirm(int $id): void
{
    $recurring = RecurringTransaction::where('user_id', auth()->id())->findOrFail($id);

    // Cipta transaction baru
    $transaction = new Transaction([
        'category_id' => $recurring->category_id,
        'description' => $recurring->name,
        'amount'      => $recurring->amount,
        'type'        => $recurring->type,
        'date'        => now()->toDateString(),
    ]);
    $transaction->user_id = auth()->id();
    $transaction->save();

    // Update next due date
    $recurring->update([
        'next_due_date' => $recurring->calculateNextDueDate(),
    ]);
}

public function skip(int $id): void
{
    $recurring = RecurringTransaction::where('user_id', auth()->id())->findOrFail($id);

    // Skip — just update next due date
    $recurring->update([
        'next_due_date' => $recurring->calculateNextDueDate(),
    ]);
}

}