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
            $table->date('template_date')->nullable()->after('cover_thumbnail')->comment('Дата, выбранная пользователем в шаблоне');
            $table->string('date_format', 50)->nullable()->after('template_date')->comment('Формат отображения даты (например, d.m.Y, Y-m-d)');
            $table->json('date_settings')->nullable()->after('date_format')->comment('Дополнительные настройки для даты в формате JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->dropColumn(['template_date', 'date_format', 'date_settings']);
        });
    }
};
