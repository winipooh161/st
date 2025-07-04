<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTemplateSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Удаляем внешний ключ в таблице user_templates
        if (Schema::hasColumn('user_templates', 'series_id')) {
            Schema::table('user_templates', function (Blueprint $table) {
                $table->dropForeign(['series_id']);
                $table->dropColumn('series_id');
            });
        }
        
        // Удаляем таблицу template_series
        Schema::dropIfExists('template_series');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Создаем таблицу template_series заново
        Schema::create('template_series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('total_copies')->default(0);
            $table->integer('available_copies')->default(0);
            $table->string('folder_name');
            $table->timestamps();
        });
        
        // Добавляем колонку series_id в user_templates
        Schema::table('user_templates', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()
                  ->after('template_id')
                  ->constrained('template_series')
                  ->onDelete('set null');
        });
    }
}
