<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['work_order_id', 'ended_at']);
        });

        Schema::table('work_order_reports', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->after('materials_used');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_reports', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });

        Schema::dropIfExists('work_order_sessions');
    }
};
