<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jalankan `php artisan invoices:fix-duplicates` dulu sebelum migrate di prod
        // jika ada invoice ganda, karena constraint ini akan gagal jika masih ada duplikat.
        Schema::table('invoices', function (Blueprint $table) {
            // Satu Work Order hanya boleh punya satu invoice (mencegah invoice ganda)
            $table->unique('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['work_order_id']);
        });
    }
};
