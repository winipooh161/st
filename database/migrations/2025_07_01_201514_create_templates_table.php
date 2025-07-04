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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('html_content');
            $table->json('editable_elements')->nullable(); // Добавляем поле для редактируемых элементов
            $table->boolean('is_base_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('preview_image')->nullable();
            $table->timestamps();
            
            // Добавляем индексы для оптимизации
            $table->index(['is_base_template', 'is_active']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
