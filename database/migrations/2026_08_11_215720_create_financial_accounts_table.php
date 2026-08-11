<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert initial data
        \Illuminate\Support\Facades\DB::table('financial_accounts')->insert([
            ['name' => 'Giro', 'code' => 'giro', 'description' => 'Akun Giro Perusahaan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rekening Bank', 'code' => 'rekening', 'description' => 'Rekening Transfer Utama', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cash', 'code' => 'cash', 'description' => 'Uang Tunai / Kas Kecil', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Direksi', 'code' => 'direksi', 'description' => 'Akun Keuangan Direksi', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('financial_account_id')->nullable()->constrained('financial_accounts')->nullOnDelete();
            $table->string('discount_type', 10)->default('fixed'); // 'percent' or 'fixed'
            $table->decimal('discount_value', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['financial_account_id']);
            $table->dropColumn(['financial_account_id', 'discount_type', 'discount_value']);
        });

        Schema::dropIfExists('financial_accounts');
    }
};
