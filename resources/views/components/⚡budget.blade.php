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
                ->whereRaw("strftime('%Y-%m', datetime(date)) = ?", [
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
            $budget            = Budget::find($id);
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

        Budget::updateOrCreate(
            [
                'user_id'     => auth()->id(),
                'category_id' => $this->category_id,
                'month'       => $this->month,
                'year'        => $this->year,
            ],
            ['amount' => $this->amount]
        );

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
    <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
        <h2 class="text-lg font-semibold">Monthly Budget</h2>
        <flux:button wire:click="openForm()" icon="plus" variant="primary">
            Set Budget
        </flux:button>
    </div>

    {{-- Month Navigation --}}
    <div class="flex items-center gap-4 mb-6">
        <flux:button wire:click="previousMonth()" icon="chevron-left" variant="ghost" size="sm" />
        <span class="text-base font-medium w-36 text-center">
            {{ \Carbon\Carbon::create($year, $month)->format('F Y') }}
        </span>
        <flux:button wire:click="nextMonth()" icon="chevron-right" variant="ghost" size="sm" />
    </div>

    {{-- Form --}}
    @if($showForm)
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 mb-6">
        <h3 class="font-medium mb-4">{{ $editingId ? 'Edit Budget' : 'Set Budget' }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
        <div class="flex gap-3 mt-4">
            <flux:button wire:click="save()" variant="primary">Save</flux:button>
            <flux:button wire:click="$set('showForm', false)" variant="ghost">Cancel</flux:button>
        </div>
    </div>
    @endif

    {{-- Budget List --}}
    @forelse($budgetList as $item)
    <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 mb-3">
        <div class="flex items-center justify-between mb-2">
            <span class="font-medium">{{ $item['category'] }}</span>
            <div class="flex items-center gap-3">
                <span class="text-sm text-zinc-500">
                    RM {{ number_format($item['spent'], 2) }}
                    / RM {{ number_format($item['budget'], 2) }}
                </span>
                <flux:button wire:click="openForm({{ $item['id'] }})" icon="pencil" variant="ghost" size="xs" />
                <flux:button wire:click="delete({{ $item['id'] }})" icon="trash" variant="ghost" size="xs"
                    wire:confirm="Delete this budget?" />
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-3 overflow-hidden">
            <div
                class="h-3 rounded-full transition-all duration-500
                    {{ $item['status'] === 'exceeded' ? 'bg-red-500'
                    : ($item['status'] === 'warning' ? 'bg-yellow-400'
                    : 'bg-green-500') }}"
                style="width: {{ $item['percent'] }}%"
            ></div>
        </div>

        <div class="flex justify-between text-xs mt-1">
            <span class="{{ $item['status'] === 'exceeded' ? 'text-red-500 font-semibold'
                         : ($item['status'] === 'warning' ? 'text-yellow-500'
                         : 'text-zinc-400') }}">
                @if($item['status'] === 'exceeded') ⚠️ Exceeded!
                @elseif($item['status'] === 'warning') Near limit
                @else On track
                @endif
            </span>
            <span class="text-zinc-400">{{ $item['percent'] }}%</span>
        </div>
    </div>
    @empty
    <div class="text-center text-zinc-400 py-12">
        No budget set for this month.
    </div>
    @endforelse

</div>