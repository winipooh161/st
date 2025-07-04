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
        Schema::table('user_templates', function (Blueprint $table) {
            $table->integer('series_max')->default(0);
            $table->integer('series_current')->default(0);
            $table->boolean('is_series')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->dropColumn('series_max');
            $table->dropColumn('series_current');
            $table->dropColumn('is_series');
        });
    }
};
