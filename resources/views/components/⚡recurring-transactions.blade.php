<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\RecurringTransaction;
use App\Models\Category;
use Illuminate\Support\Carbon;

new class extends Component {

    public string $name        = '';
    public string $amount      = '';
    public string $type        = 'expense';
    public string $frequency   = 'monthly';
    public string $start_date  = '';
    public ?int   $category_id = null;
    public string $end_date    = '';
    public bool   $showForm    = false;
    public ?int   $editingId   = null;

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
    }

    #[Computed]
    public function recurringList()
    {
        return RecurringTransaction::with('category')
            ->where('user_id', auth()->id())
            ->orderBy('next_due_date')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $recurring = RecurringTransaction::findOrFail($id);

        $this->editingId   = $id;
        $this->name        = $recurring->name;
        $this->amount      = $recurring->amount;
        $this->type        = $recurring->type;
        $this->frequency   = $recurring->frequency;
        $this->start_date  = $recurring->start_date->toDateString();
        $this->category_id = $recurring->category_id;
        $this->end_date    = $recurring->end_date?->toDateString() ?? '';
        $this->showForm    = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'       => 'required|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'type'       => 'required|in:income,expense',
            'frequency'  => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after:start_date',
        ]);

        $data = [
            'user_id'       => auth()->id(),
            'name'          => $this->name,
            'amount'        => $this->amount,
            'type'          => $this->type,
            'frequency'     => $this->frequency,
            'category_id'   => $this->category_id ?: null,
            'start_date'    => $this->start_date,
            'next_due_date' => $this->editingId ? $this->start_date : $this->start_date,
            'end_date'      => $this->end_date ?: null,
            'is_active'     => true,
        ];

        if ($this->editingId) {
            RecurringTransaction::findOrFail($this->editingId)->update($data);
        } else {
            RecurringTransaction::create($data);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function confirm(int $id): void
    {
        $recurring = RecurringTransaction::findOrFail($id);

        \App\Models\Transaction::create([
            'user_id'     => auth()->id(),
            'category_id' => $recurring->category_id,
            'description' => $recurring->name,  // <-- tukar 'name' kepada 'description'
            'amount'      => $recurring->amount,
            'type'        => $recurring->type,
            'date'        => now()->toDateString(),
        ]);

        $recurring->update([
            'next_due_date' => $recurring->calculateNextDueDate(),
        ]);
    }

    public function skip(int $id): void
    {
        $recurring = RecurringTransaction::findOrFail($id);

        $recurring->update([
            'next_due_date' => $recurring->calculateNextDueDate(),
        ]);
    }

    public function toggleActive(int $id): void
    {
        $recurring = RecurringTransaction::findOrFail($id);
        $recurring->update(['is_active' => !$recurring->is_active]);
    }

    public function delete(int $id): void
    {
        RecurringTransaction::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
        $this->amount      = '';
        $this->type        = 'expense';
        $this->frequency   = 'monthly';
        $this->start_date  = now()->toDateString();
        $this->category_id = null;
        $this->end_date    = '';
    }
};
?>

<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold">Recurring Transactions</h2>
        <flux:button wire:click="openForm" variant="primary" icon="plus">
            Add Recurring
        </flux:button>
    </div>

    {{-- Form --}}
    @if($showForm)
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm mb-6">
        <h3 class="font-semibold mb-4">{{ $editingId ? 'Edit' : 'New' }} Recurring Transaction</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input
                wire:model="name"
                label="Name"
                placeholder="e.g. Gaji, Sewa, Netflix"
            />
            <flux:input
                wire:model="amount"
                label="Amount (RM)"
                type="number"
                step="0.01"
                placeholder="0.00"
            />
            <flux:select wire:model="type" label="Type">
                <flux:select.option value="income">Income</flux:select.option>
                <flux:select.option value="expense">Expense</flux:select.option>
            </flux:select>
            <flux:select wire:model="frequency" label="Frequency">
                <flux:select.option value="daily">Daily</flux:select.option>
                <flux:select.option value="weekly">Weekly</flux:select.option>
                <flux:select.option value="monthly">Monthly</flux:select.option>
                <flux:select.option value="yearly">Yearly</flux:select.option>
            </flux:select>
            <flux:select wire:model="category_id" label="Category (Optional)">
                <flux:select.option value="">-- No Category --</flux:select.option>
                @foreach($this->categories as $cat)
                    <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input
                wire:model="start_date"
                label="Start Date"
                type="date"
            />
            <flux:input
                wire:model="end_date"
                label="End Date (Optional)"
                type="date"
            />
        </div>

        <div class="flex gap-3 mt-6">
            <flux:button wire:click="save" variant="primary">
                {{ $editingId ? 'Update' : 'Save' }}
            </flux:button>
            <flux:button wire:click="$set('showForm', false)" variant="ghost">
                Cancel
            </flux:button>
        </div>
    </div>
    @endif

    {{-- Pending Section --}}
    @php $pending = $this->recurringList->where('is_active', true)->filter(fn($r) => $r->next_due_date->lte(now())); @endphp
    @if($pending->count() > 0)
    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-6">
        <p class="text-sm font-semibold text-amber-400 mb-3">
            {{ $pending->count() }} Transaction Pending Confirmation
        </p>
        @foreach($pending as $item)
        <div class="flex items-center justify-between py-2 border-b border-amber-500/20 last:border-0">
            <div>
                <p class="text-sm font-medium">{{ $item->name }}</p>
                <p class="text-xs text-amber-400/70">Due: {{ $item->next_due_date->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold {{ $item->type === 'income' ? 'text-green-500' : 'text-red-400' }}">
                    {{ $item->type === 'income' ? '+' : '-' }}RM {{ number_format($item->amount, 2) }}
                </span>
                <flux:button wire:click="confirm({{ $item->id }})" size="sm" variant="primary">
                    Confirm
                </flux:button>
                <flux:button wire:click="skip({{ $item->id }})" size="sm" variant="ghost">
                    Skip
                </flux:button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- List --}}
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm overflow-hidden">
        @forelse($this->recurringList as $item)
        <div class="flex items-center justify-between p-4 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
            <div class="flex items-center gap-3">
                <div
                    wire:click="toggleActive({{ $item->id }})"
                    class="w-2 h-2 rounded-full cursor-pointer {{ $item->is_active ? 'bg-green-400' : 'bg-zinc-400' }}"
                    title="{{ $item->is_active ? 'Active' : 'Paused' }}"
                ></div>
                <div>
                    <p class="font-medium text-sm {{ $item->is_active ? '' : 'opacity-50' }}">
                        {{ $item->name }}
                    </p>
                    <p class="text-xs text-zinc-400">
                        {{ ucfirst($item->frequency) }}
                        • Next: {{ $item->next_due_date->format('d M Y') }}
                        @if($item->category) • {{ $item->category->name }} @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="font-semibold text-sm {{ $item->type === 'income' ? 'text-green-500' : 'text-red-400' }}">
                    {{ $item->type === 'income' ? '+' : '-' }}RM {{ number_format($item->amount, 2) }}
                </span>
                <div class="flex gap-2">
                    <flux:button wire:click="edit({{ $item->id }})" size="sm" variant="ghost" icon="pencil" />
                    <flux:button wire:click="delete({{ $item->id }})" size="sm" variant="ghost" icon="trash" />
                </div>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
            <p class="text-sm">No recurring transactions yet.</p>
            <p class="text-xs mt-1">Add your first one to get started.</p>
        </div>
        @endforelse
    </div>
</div>