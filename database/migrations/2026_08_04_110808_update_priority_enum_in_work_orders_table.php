<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First convert existing old string priorities to new string numeric priorities
        DB::table('work_orders')->where('priority', 'urgent')->update(['priority' => '1']);
        DB::table('work_orders')->where('priority', 'high')->update(['priority' => '2']);
        DB::table('work_orders')->where('priority', 'normal')->update(['priority' => '3']);
        DB::table('work_orders')->where('priority', 'low')->update(['priority' => '4']);

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('priority', 10)->default('3')->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('priority', 10)->default('normal')->change();
        });

        DB::table('work_orders')->where('priority', '1')->update(['priority' => 'urgent']);
        DB::table('work_orders')->where('priority', '2')->update(['priority' => 'high']);
        DB::table('work_orders')->where('priority', '3')->update(['priority' => 'normal']);
        DB::table('work_orders')->where('priority', '4')->update(['priority' => 'low']);
    }
};
