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
        Schema::table('templates', function (Blueprint $table) {
            // Проверяем, существуют ли индексы перед их созданием
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexNames = array_keys($sm->listTableIndexes('templates'));
            
            // Добавляем составной индекс для быстрого поиска базового шаблона
            if (!in_array('idx_base_active', $indexNames)) {
                $table->index(['is_base_template', 'is_active'], 'idx_base_active');
            }
            
            // Индекс для поиска по статусу
            if (!in_array('idx_active', $indexNames)) {
                $table->index('is_active', 'idx_active');
            }
            
            // Индекс для базовых шаблонов
            if (!in_array('idx_base', $indexNames)) {
                $table->index('is_base_template', 'idx_base');
            }
            
            // Добавляем индекс для поиска по дате обновления (для кеширования)
            if (!in_array('idx_updated_at', $indexNames)) {
                $table->index('updated_at', 'idx_updated_at');
            }
            
            // Добавляем индекс для поиска по имени шаблона
            if (!in_array('idx_name', $indexNames)) {
                $table->index('name', 'idx_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            // Безопасное удаление индексов
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexNames = array_keys($sm->listTableIndexes('templates'));
            
            if (in_array('idx_base_active', $indexNames)) {
                $table->dropIndex('idx_base_active');
            }
            if (in_array('idx_active', $indexNames)) {
                $table->dropIndex('idx_active');
            }
            if (in_array('idx_base', $indexNames)) {
                $table->dropIndex('idx_base');
            }
            if (in_array('idx_updated_at', $indexNames)) {
                $table->dropIndex('idx_updated_at');
            }
            if (in_array('idx_name', $indexNames)) {
                $table->dropIndex('idx_name');
            }
        });
    }
};
