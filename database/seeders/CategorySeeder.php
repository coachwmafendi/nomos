<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Expense categories
            ['name' => 'Food & Drinks',   'type' => 'expense'],
            ['name' => 'Transport',        'type' => 'expense'],
            ['name' => 'Shopping',         'type' => 'expense'],
            ['name' => 'Bills & Utilities','type' => 'expense'],
            ['name' => 'Health/Medical',           'type' => 'expense'],
            ['name' => 'Business',           'type' => 'expense'],
            ['name' => 'Entertainment',    'type' => 'expense'],
            ['name' => 'Education',        'type' => 'expense'],
            ['name' => 'Travel',           'type' => 'expense'],
            ['name' => 'Donation/Zakat',    'type' => 'expense'],
            ['name' => 'Household',           'type' => 'expense'],
            ['name' => 'Remuneration',           'type' => 'expense'],
            ['name' => 'Other Expense',    'type' => 'expense'],

            // Income categories
            ['name' => 'Salary',           'type' => 'income'],
            ['name' => 'Freelance',        'type' => 'income'],
            ['name' => 'Investment',       'type' => 'income'],
            ['name' => 'Commission',        'type' => 'income'],
            ['name' => 'Gift',             'type' => 'income'],
           ['name' => 'Bonus',        'type' => 'income'],
            ['name' => 'Pension',        'type' => 'income'],
            ['name' => 'Other Income',     'type' => 'income'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['type' => $category['type']]
            );
        }
    }
}