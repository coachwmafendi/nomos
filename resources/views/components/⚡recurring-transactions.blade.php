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
        $recurring = RecurringTransaction::where('user_id', auth()->id())->findOrFail($id);

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
            RecurringTransaction::where('user_id', auth()->id())->findOrFail($this->editingId)->update($data);
        } else {
            $recurring = new RecurringTransaction($data);
            $recurring->user_id = auth()->id();
            $recurring->save();
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function confirm(int $id): void
    {
        $recurring = RecurringTransaction::where('user_id', auth()->id())->findOrFail($id);

        $transaction = new Transaction([
            'category_id' => $recurring->category_id,
            'description' => $recurring->name,
            'amount'      => $recurring->amount,
            'type'        => $recurring->type,
            'date'        => now()->toDateString(),
        ]);
        $transaction->user_id = auth()->id();
        $transaction->save();

        $recurring->update([
            'next_due_date' => $recurring->calculateNextDueDate(),
        ]);
    }

    public function skip(int $id): void
    {
        $recurring = RecurringTransaction::where('user_id', auth()->id())->findOrFail($id);

        $recurring->update([
            'next_due_date' => $recurring->calculateNextDueDate(),
        ]);
    }

    public function toggleActive(int $id): void
    {
        $recurring = RecurringTransaction::where('user_id', auth()->id())->findOrFail($id);
        $recurring->update(['is_active' => !$recurring->is_active]);
    }

    public function delete(int $id): void
    {
        RecurringTransaction::where('user_id', auth()->id())->findOrFail($id)->delete();
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
    <div class="flex items-start justify-between flex-wrap gap-4 mb-7 animate-slide-up" style="animation-delay: 0s">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-400 mb-1">Finance</p>
            <h1 class="text-xl font-bold text-white">Recurring Transactions</h1>
        </div>
        <flux:button wire:click="openForm" variant="primary" icon="plus">
            Add Recurring
        </flux:button>
    </div>

    {{-- Form Modal --}}
    <flux:modal wire:model.self="showForm" class="md:w-[560px]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Edit' : 'New' }} Recurring Transaction</flux:heading>

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

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Update' : 'Save' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Pending Section --}}
    @php $pending = $this->recurringList->where('is_active', true)->filter(fn($r) => $r->next_due_date->lte(now())); @endphp
    @if($pending->count() > 0)
    <div class="relative flex items-center justify-between flex-wrap gap-3 bg-amber-500/8 border border-amber-500/25 rounded-2xl px-4 py-3.5 mb-5 overflow-hidden animate-slide-up" style="animation-delay: 0.04s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-400/40 to-transparent"></div>
        <div class="flex flex-col gap-2 w-full">
            <div class="flex items-center gap-3">
                <div class="relative w-2 h-2 shrink-0">
                    <span class="absolute inset-0 rounded-full bg-amber-400 animate-ping opacity-75"></span>
                    <span class="relative block w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <p class="text-sm font-semibold text-amber-400">
                    {{ $pending->count() }} Transaction{{ $pending->count() > 1 ? 's' : '' }} Pending Confirmation
                </p>
            </div>
            @foreach($pending as $item)
            <div class="flex items-center justify-between py-2 border-b border-amber-500/20 last:border-0 pl-5">
                <div>
                    <p class="text-sm font-medium text-white">{{ $item->name }}</p>
                    <p class="text-xs text-amber-400/70">Due: {{ $item->next_due_date->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold {{ $item->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
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
    </div>
    @endif

    {{-- List --}}
    <div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.08s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>
        <div class="flex items-center justify-between px-5 pt-5 pb-4">
            <h3 class="text-sm font-semibold text-white">Recurring</h3>
        </div>
        @forelse($this->recurringList as $item)
        <div class="flex items-center gap-4 px-5 py-3.5 border-t border-zinc-800/60 hover:bg-zinc-800/30 transition-colors duration-150">
            <div
                wire:click="toggleActive({{ $item->id }})"
                class="cursor-pointer shrink-0"
                title="{{ $item->is_active ? 'Active – click to pause' : 'Paused – click to activate' }}"
            >
                @if($item->is_active)
                    <span class="w-2 h-2 rounded-full bg-emerald-400 block"></span>
                @else
                    <span class="w-2 h-2 rounded-full bg-zinc-600 block"></span>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-sm text-white {{ $item->is_active ? '' : 'opacity-50' }} truncate">
                    {{ $item->name }}
                </p>
                <p class="text-xs text-zinc-500">
                    {{ ucfirst($item->frequency) }}
                    &bull; Next: {{ $item->next_due_date->format('d M Y') }}
                    @if($item->category) &bull; {{ $item->category->name }} @endif
                </p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
                <span class="font-semibold text-sm {{ $item->type === 'income' ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $item->type === 'income' ? '+' : '-' }}RM {{ number_format($item->amount, 2) }}
                </span>
                <div class="flex gap-2">
                    <flux:button wire:click="edit({{ $item->id }})" size="sm" variant="ghost" icon="pencil" />
                    <flux:button wire:click="delete({{ $item->id }})" size="sm" variant="ghost" icon="trash" />
                </div>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center py-16 gap-3 border-t border-zinc-800/60">
            <div class="w-12 h-12 rounded-xl bg-zinc-800 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            <p class="text-sm text-zinc-500">No recurring transactions yet</p>
            <p class="text-xs text-zinc-400">Add your first one to get started</p>
        </div>
        @endforelse
    </div>
</div>