<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('work_orders', function (Blueprint $table) {
      $table->foreignId('vendor_id')
        ->nullable()
        ->after('customer_id')
        ->constrained('vendors')
        ->nullOnDelete();
    });

    Schema::table('work_order_items', function (Blueprint $table) {
      $table->decimal('vendor_unit_price', 15, 2)
        ->nullable()
        ->after('unit_price');
    });
  }

  public function down(): void
  {
    Schema::table('work_order_items', function (Blueprint $table) {
      $table->dropColumn('vendor_unit_price');
    });

    Schema::table('work_orders', function (Blueprint $table) {
      $table->dropForeign(['vendor_id']);
      $table->dropColumn('vendor_id');
    });
  }
};
