<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
            $table->integer('job_order')->nullable()->after('scheduled_time');
            $table->index('job_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex(['job_order']);
            $table->dropColumn(['scheduled_time', 'job_order']);
        });
    }
};
