<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('work_order_reports')->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('photo_type', 20)->default('after');
            $table->string('caption')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_report_photos');
    }
};
