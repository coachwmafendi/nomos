# Nomos

Nomos is a personal finance management web application for individual users. It provides a single place to track income and expenses, set budgets, manage recurring bills, and understand spending behaviour through analytics and insights.

## Key Features

- **Dashboard**: High-level overview of financial health with summary cards and spending charts.
- **Transaction Tracking**: Fast entry of income and expenses with optional file attachments.
- **Personalized Categories**: Per-user category management with default sets seeded on registration.
- **Monthly Budgeting**: Set and track budgets per category with real-time progress indicators.
- **Recurring Transactions**: Automate recurring bills and income with a confirmation workflow.
- **Financial Insights**: Detailed spending analysis, trends, and data-driven recommendations.
- **Reporting**: Exportable transaction lists and visual spending reports.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.4, SQLite
- **Frontend**: Livewire 4, Flux UI, Tailwind CSS 4, Alpine.js
- **Auth**: Laravel Fortify (2FA, Email Verification)

## Installation

1. Clone the repository.
2. Install dependencies: `composer install` and `npm install`.
3. Set up environment: `cp .env.example .env`.
4. Run migrations and seeders: `php artisan migrate --seed`.
5. Start the development server: `npm run dev` and use Laravel Herd.

## Architecture

- **Single-File Components (SFC)**: Livewire components use the `.blade.php` format with PHP logic and templates in one file.
- **Action Pattern**: Business logic for CRUD operations is encapsulated in Action classes within `app/Actions/`.
- **Data Isolation**: All user data (transactions, categories, budgets, recurring transactions) is scoped to the authenticated user using Global Scopes.
