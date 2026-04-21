<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['date', 'type']);      // untuk chart queries
            $table->index(['type', 'amount']);    // untuk summary queries
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['date', 'type']);
            $table->dropIndex(['type', 'amount']);
        });
    }
};