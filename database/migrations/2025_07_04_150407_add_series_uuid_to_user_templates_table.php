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
            $table->string('series_uuid', 36)->nullable()->comment('Уникальный идентификатор серии');
            $table->unsignedBigInteger('parent_series_id')->nullable()->comment('ID родительской серии');
            $table->index('series_uuid');
            $table->index('parent_series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->dropColumn(['series_uuid', 'parent_series_id']);
        });
    }
};
