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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_id')->index();
            $table->enum('platform', ['android', 'ios']);
            $table->string('app_version');
            $table->unsignedInteger('build_number');
            $table->string('os_version')->nullable();
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('network_type')->nullable();
            $table->string('session_id')->nullable();
            $table->foreignId('user_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
