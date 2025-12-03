<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
    Schema::table('users', function (Blueprint $table) {
        // Por defecto abren a las 9 y cierran a las 16 (4pm)
        $table->time('opening_time')->default('09:00:00')->nullable();
        $table->time('closing_time')->default('16:00:00')->nullable();
    });
    }   

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['opening_time', 'closing_time']);
    });
    }
}
