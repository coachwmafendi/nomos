<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;

new class extends Component {

public function confirm(int $id): void
{
    $recurring = RecurringTransaction::findOrFail($id);

    // Cipta transaction baru
    \App\Models\Transaction::create([
        'user_id'     => auth()->id(),
        'category_id' => $recurring->category_id,
        'name'        => $recurring->name,
        'amount'      => $recurring->amount,
        'type'        => $recurring->type,
        'date'        => now()->toDateString(),
        'note'        => 'Auto from recurring',
    ]);

    // Update next due date
    $recurring->update([
        'next_due_date' => $recurring->calculateNextDueDate(),
    ]);
}

public function skip(int $id): void
{
    $recurring = RecurringTransaction::findOrFail($id);

    // Skip — just update next due date
    $recurring->update([
        'next_due_date' => $recurring->calculateNextDueDate(),
    ]);
}

}