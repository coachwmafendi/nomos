# Nomos — Product Requirements Document

## Overview

Nomos is a personal finance management web application for individual users. It provides a single place to track income and expenses, set budgets, manage recurring bills, and understand spending behaviour through analytics and insights.

**Stack:** Laravel 13 · Livewire 4 · Flux UI · Tailwind CSS 4 · Alpine.js · SQLite  
**Auth:** Laravel Fortify (email verification, 2FA support)

---

## Goals

1. Give users a clear picture of where their money goes each month.
2. Make transaction entry fast — minimal friction, optional attachments.
3. Surface patterns and anomalies without the user having to ask.
4. Warn users when recurring bills are due.
5. Keep budgets visible so users can course-correct mid-month.

---

## Users

Single-user per account. Multi-user architecture exists (user_id on most tables) to ensure data isolation.

---

## Features

### 1. Dashboard

**Route:** `/dashboard`  
**Component:** `components/pages/⚡dashboard.blade.php`

- Date range filter (default: current month).
- **Summary cards** — Total Income, Total Expense, Balance for selected range.
- **Monthly comparison** — current month expense vs same period last month, with sparkline (7-day area chart) and percentage change badge.
- **Weekly spending chart** — 7-day bar chart (expense only).
- **Top categories** — top 5 expense categories with progress bars and percentage.
- **Recent transactions** — last 5 transactions with category, amount, date.
- **Recurring pending banner** — shown when recurring transactions are due today; links to Recurring page.

---

### 2. Transactions

**Route:** `/transactions`  
**Component:** `components/transactions/⚡manage-transactions.blade.php`

#### List / Filter

- Paginated table (10 per page).
- Search by description.
- Filter by type (income / expense) and category.
- Date range filter (from / to).
- Sort by date, amount, description (toggle asc/desc).
- Export to CSV (respects current filters).

#### Create / Edit

- Fields: Description, Amount (RM), Type, Category, Date.
- Optional file attachment (JPG, PNG, PDF — max 5 MB).
- Drag-and-drop upload zone.
- Inline validation with error messages.
- Flux modal dialog.

#### Delete

- Confirmation modal before deletion.
- Toast notification on success.

#### Chart

- Income vs Expense bar chart (ApexCharts).
- Year selector + optional month selector (monthly view → daily breakdown).
- Chart updates reactively when transactions are added/edited/deleted.

#### Summary Strip

- Total Income, Total Expense, Balance (all-time, not date-filtered).

---

### 3. Categories

**Route:** `/manage-categories`  
**Component:** `components/⚡manage-categories.blade.php`

- Paginated list (15 per page) with transaction count per category.
- Filter by type (income / expense / both).
- Create, edit, delete via modals.
- Delete shows transaction count warning.
- **Per-user scoping:** Each user has their own set of categories. Default categories are seeded upon registration.

---

### 4. Budget

**Route:** `/budget`  
**Component:** `components/⚡budget.blade.php`

- Month navigation (previous / next buttons + month selector).
- Per-category budget amounts for selected month.
- Spent vs budget with progress bar:
  - Green — under 80% of budget.
  - Yellow — 80–100%.
  - Red — over budget.
- Create / edit budgets via modal.
- Delete confirmation.
- Shows "no budget set" state for categories without budgets.

---

### 5. Recurring Transactions

**Route:** `/recurring`  
**Component:** `components/⚡recurring-transactions.blade.php`

- Two sections: **Pending** (due today or overdue) and **All recurring**.
- Frequencies: daily, weekly, monthly, yearly.
- Optional end date and category.
- **Confirm** action — creates an actual transaction and advances next_due_date.
- **Skip** action — advances next_due_date without creating a transaction.
- Active / paused toggle.
- Create / edit / delete via modals.

---

### 6. Bar Reports

**Route:** `/bars-report`  
**Component:** `components/⚡bars-report.blade.php`

- Bar chart (ApexCharts) — income and expense by month or by category.
- Date range filtering.
- Summary table below chart.

---

### 7. Transaction Report

**Route:** `/report`  
**Component:** `components/transactions/⚡transaction-report.blade.php`

- Income vs expense summary for selected period.
- Expense breakdown by category (table + percentages).
- Monthly trend chart (full year, income vs expense).
- Year / month filter.

---

### 8. Insights

**Route:** `/insights`  
**Component:** `components/pages/⚡insights.blade.php`

- Month selector (previous / next buttons).
- **Summary cards:**
  - Total spending
  - Average daily spending
  - Top spending category
  - Biggest increase vs last month
- **Category breakdown** — top 6 categories with progress bars.
- **Category movement** — biggest increase and biggest decrease vs last month.
- **Spending trend** — area chart (daily spending for selected month).
- **Spending patterns:**
  - Highest spending weekday
  - Weekend vs weekday comparison
  - Peak spending day
- **Recurring expenses summary** — count, total, next due date.
- **Recommendations** — up to 3 data-driven suggestions.
- Animated card entrance (staggered).

---

### 9. Financial Quote

**Route:** `/quote`  
**Component:** `components/⚡financial-quote.blade.php`

- Fetches a money-related joke/quote from JokeAPI.
- Fallback quote on API failure (Benjamin Franklin).
- Refresh button for new quote.

---

### 10. Settings

**Route prefix:** `/settings`

| Page | Route | Purpose |
|------|-------|---------|
| Profile | `/settings/profile` | Update name and email |
| Appearance | `/settings/appearance` | Dark/light mode toggle |
| Security | `/settings/security` | Change password, 2FA setup |

**2FA:** TOTP-based (QR code scan). Recovery codes downloadable.

---

## Data Model

### transactions
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| description | string | |
| amount | decimal(10,2) | |
| type | enum | income / expense |
| category_id | bigint nullable | FK → categories |
| date | datetime | |

### categories
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| name | string | |
| type | enum | income / expense / both |

### budgets
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| category_id | bigint | FK → categories |
| amount | decimal(12,2) | |
| month | tinyint | 1–12 |
| year | smallint | |

Unique constraint: (user_id, category_id, month, year).

### recurring_transactions
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| category_id | bigint nullable | FK → categories |
| name | string | |
| amount | decimal(10,2) | |
| type | enum | income / expense |
| frequency | enum | daily / weekly / monthly / yearly |
| start_date | date | |
| next_due_date | date | auto-calculated |
| end_date | date nullable | |
| is_active | boolean | default true |

### transaction_attachments
| Column | Type | Notes |
|--------|------|-------|
| id | bigint | PK |
| transaction_id | bigint | FK → transactions |
| original_name | string | filename shown to user |
| stored_name | string | hashed storage name |
| path | string | relative path on disk |
| disk | string | default: public |
| mime_type | string | |
| size | bigint | bytes |
| uploaded_by | bigint nullable | FK → users |

---

## Non-Functional Requirements

- **Responsiveness** — works on mobile (sidebar collapses).
- **Dark mode** — full support via Tailwind `dark:` classes and Flux theme.
- **Performance** — expensive queries cached with `#[Computed(cache: true)]`. Chart data cached 5 minutes per year/month key.
- **Security** — all routes behind `auth` + `verified` middleware. File uploads validated (type + size). Queries scoped to `auth()->id()`.
- **SQLite** — all raw SQL must use `strftime()` not MySQL date functions.

---

## Known Limitations / Future Work

- No multi-currency support.
- No goal/savings tracking.
- No bank/API import (manual entry only).
- No mobile app — web only.
- CSV export but no import.
- No notification/reminder system for recurring transactions (only banner on dashboard).
