<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('total_copies')->default(0);
            $table->integer('available_copies')->default(0);
            $table->string('folder_name');
            $table->timestamps();
        });

        Schema::table('user_templates', function (Blueprint $table) {
            $table->foreignId('series_id')->nullable()
                  ->after('template_id')
                  ->constrained('template_series')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropColumn('series_id');
        });

        Schema::dropIfExists('template_series');
    }
}
