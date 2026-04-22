@props(['totalIncome', 'totalExpense', 'balance'])

<div class="grid grid-cols-3 gap-4 w-full">
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm">
        <p class="text-sm text-gray-500">Income</p>
        <p class="text-xl font-bold text-green-600">RM {{ number_format($totalIncome, 2) }}</p>
    </div>
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm">
        <p class="text-sm text-gray-500">Expense</p>
        <p class="text-xl font-bold text-red-500">RM {{ number_format($totalExpense, 2) }}</p>
    </div>
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 shadow-sm">
        <p class="text-sm text-gray-500">Balance</p>
        <p class="text-xl font-bold text-blue-600">RM {{ number_format($balance, 2) }}</p>
    </div>
</div>