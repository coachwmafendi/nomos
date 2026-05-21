<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Carbon;

new #[Title('Manage Your Budget')] class extends Component {

    public int $month;
    public int $year;
    public array $budgetList = [];

    public ?int $editingId  = null;
    public int $category_id = 0;
    public string $amount   = '';
    public bool $showForm   = false;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year  = now()->year;
        $this->refreshBudgetList();
    }

    private function refreshBudgetList(): void
    {
        $budgets = Budget::with('category')
            ->where('user_id', auth()->id())
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get();

        $result = [];

        foreach ($budgets as $budget) {
            $spent = Transaction::where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereRaw(\App\Support\DateExpression::format('%Y-%m', 'date') . ' = ?', [
                    sprintf('%04d-%02d', $this->year, $this->month)
                ])
                ->sum('amount');

            $budgetAmount = (float) $budget->amount;
            $spentAmount  = (float) $spent;
            $percent      = $budgetAmount > 0
                ? min(100, round(($spentAmount / $budgetAmount) * 100))
                : 0;

            $result[] = [
                'id'       => $budget->id,
                'category' => $budget->category->name,
                'budget'   => $budgetAmount,
                'spent'    => $spentAmount,
                'percent'  => $percent,
                'status'   => $percent >= 100 ? 'exceeded'
                           : ($percent >= 80  ? 'warning' : 'safe'),
            ];
        }

        $this->budgetList = $result;
    }

    #[Computed]
    public function categories()
    {
        return Category::where('type', 'expense')
            ->orderBy('name')
            ->get();
    }

    public function openForm(?int $id = null): void
    {
        $this->reset(['category_id', 'amount', 'editingId']);
        $this->showForm = true;

        if ($id) {
            $budget            = Budget::where('user_id', auth()->id())->findOrFail($id);
            $this->editingId   = $budget->id;
            $this->category_id = $budget->category_id;
            $this->amount      = $budget->amount;
        }
    }

    public function save(): void
    {
        $this->validate([
            'category_id' => 'required|exists:categories,id',
            'amount'      => 'required|numeric|min:1',
        ]);

        $budget = Budget::firstOrNew([
            'category_id' => $this->category_id,
            'month'       => $this->month,
            'year'        => $this->year,
        ]);
        $budget->user_id = auth()->id();
        $budget->amount = $this->amount;
        $budget->save();

        $this->reset(['category_id', 'amount', 'editingId', 'showForm']);
        $this->refreshBudgetList();
    }

    public function delete(int $id): void
    {
        Budget::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        $this->refreshBudgetList();
    }

    public function previousMonth(): void
    {
        $date        = Carbon::create($this->year, $this->month)->subMonth();
        $this->month = $date->month;
        $this->year  = $date->year;
        $this->refreshBudgetList();
    }

    public function nextMonth(): void
    {
        $date        = Carbon::create($this->year, $this->month)->addMonth();
        $this->month = $date->month;
        $this->year  = $date->year;
        $this->refreshBudgetList();
    }
};
?>

<div>

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-4 mb-7 animate-slide-up" style="animation-delay: 0s">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-400 mb-1">Finance</p>
            <h1 class="text-xl font-bold text-white">Monthly Budget</h1>
        </div>
        <flux:button wire:click="openForm()" icon="plus" variant="primary">
            Set Budget
        </flux:button>
    </div>

    {{-- Month Navigation --}}
    <div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl p-4 mb-5 overflow-hidden animate-slide-up" style="animation-delay: 0.04s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>
        <div class="flex items-center justify-between gap-4">
            <flux:button wire:click="previousMonth()" icon="chevron-left" variant="ghost" size="sm" />
            <span class="text-base font-medium text-white w-36 text-center">
                {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
            </span>
            <flux:button wire:click="nextMonth()" icon="chevron-right" variant="ghost" size="sm" />
        </div>
    </div>

    {{-- Form Modal --}}
    <flux:modal wire:model.self="showForm" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editingId ? 'Edit Budget' : 'Set Budget' }}</flux:heading>

            <div class="grid grid-cols-1 gap-4">
                <flux:select wire:model="category_id" label="Category">
                    <option value="0">-- Select Category --</option>
                    @foreach($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="amount"
                    label="Budget Amount (RM)"
                    type="number"
                    min="1"
                    step="0.01"
                    placeholder="e.g. 500"
                />
            </div>

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Save</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Budget List --}}
    @forelse($budgetList as $item)
    @php $delay = 0.06 + ($loop->index * 0.06); @endphp
    <div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden mb-3 animate-slide-up" style="animation-delay: {{ $delay }}s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>
        <div class="flex items-center justify-between gap-4 p-5">
            <span class="font-medium text-white">{{ $item['category'] }}</span>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <span class="text-zinc-300 font-medium text-sm">RM {{ number_format($item['spent'], 2) }}</span>
                    <span class="text-zinc-500 text-sm"> / RM {{ number_format($item['budget'], 2) }}</span>
                </div>
                <flux:button wire:click="openForm({{ $item['id'] }})" icon="pencil" variant="ghost" size="xs" />
                <flux:button wire:click="delete({{ $item['id'] }})" icon="trash" variant="ghost" size="xs"
                    wire:confirm="Delete this budget?" />
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="px-5 pb-4">
            <div class="flex items-center justify-between text-xs text-zinc-500 mb-1.5">
                <span class="{{ $item['status'] === 'exceeded' ? 'text-rose-400'
                             : ($item['status'] === 'warning' ? 'text-amber-400'
                             : 'text-emerald-400') }}">
                    @if($item['status'] === 'exceeded') Exceeded
                    @elseif($item['status'] === 'warning') Near limit
                    @else On track
                    @endif
                </span>
                <span>{{ $item['percent'] }}%</span>
            </div>
            <div class="w-full bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                <div class="h-1.5 rounded-full bg-gradient-to-r animate-grow-x
                    {{ $item['status'] === 'exceeded' ? 'from-rose-500 to-red-500'
                    : ($item['status'] === 'warning' ? 'from-amber-500 to-orange-500'
                    : 'from-emerald-500 to-teal-500') }}"
                    style="width: {{ $item['percent'] }}%"
                ></div>
            </div>
        </div>
    </div>
    @empty
    <div class="relative bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.06s">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-600/40 to-transparent"></div>
        <div class="flex flex-col items-center justify-center py-16 gap-3">
            <div class="w-12 h-12 rounded-xl bg-zinc-800 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>
            <p class="text-sm text-zinc-500">No budgets yet</p>
            <p class="text-xs text-zinc-400">Add your first budget to get started</p>
        </div>
    </div>
    @endforelse

</div>