<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert initial data corresponding to the old enums
        \Illuminate\Support\Facades\DB::table('work_order_types')->insert([
            ['name' => 'Pengecekan', 'code' => 'checking', 'description' => 'Pengecekan awal', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Servis/Perbaikan', 'code' => 'service', 'description' => 'Servis / Perbaikan AC', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Instalasi', 'code' => 'installation', 'description' => 'Instalasi unit baru', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perawatan', 'code' => 'maintenance', 'description' => 'Perawatan rutin', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Add foreign key to work_orders
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('work_order_type_id')->nullable()->after('type')->constrained('work_order_types');
        });

        // Map existing work_orders.type enum to work_order_types.id
        $typesMap = \Illuminate\Support\Facades\DB::table('work_order_types')->pluck('id', 'code');
        foreach ($typesMap as $code => $id) {
            \Illuminate\Support\Facades\DB::table('work_orders')->where('type', $code)->update(['work_order_type_id' => $id]);
        }

        // Make it non-nullable after migration and drop the old type column
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('work_order_type_id')->nullable(false)->change();
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->after('work_order_type_id');
        });

        // Map back
        $typesMap = \Illuminate\Support\Facades\DB::table('work_order_types')->pluck('code', 'id');
        foreach ($typesMap as $id => $code) {
            \Illuminate\Support\Facades\DB::table('work_orders')->where('work_order_type_id', $id)->update(['type' => $code]);
        }

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('type', 20)->nullable(false)->change();
            $table->dropForeign(['work_order_type_id']);
            $table->dropColumn('work_order_type_id');
        });

        Schema::dropIfExists('work_order_types');
    }
};
