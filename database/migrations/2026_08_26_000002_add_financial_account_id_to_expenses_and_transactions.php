<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->nullable()
                ->after('category_id')
                ->constrained('financial_accounts')
                ->nullOnDelete();
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->foreignId('financial_account_id')
                ->nullable()
                ->after('category_id')
                ->constrained('financial_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['financial_account_id']);
            $table->dropColumn('financial_account_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['financial_account_id']);
            $table->dropColumn('financial_account_id');
        });
    }
};
