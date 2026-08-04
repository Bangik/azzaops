<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number', 50)->unique();
            $table->string('type', 20);
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('service_category_id')->constrained('service_categories');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('location');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('priority', 10)->default('3');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('parent_wo_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('status');
            $table->index('customer_id');
            $table->index('scheduled_date');
            $table->index('parent_wo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
