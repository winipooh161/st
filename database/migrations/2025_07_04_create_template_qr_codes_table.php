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
        Schema::create('template_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('user_templates')->onDelete('cascade');
            $table->enum('type', ['client', 'creator']); // 'client' - для получения шаблона, 'creator' - для деактивации
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['token', 'type']);
            $table->index(['template_id', 'type']);
            $table->index(['is_used', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_qr_codes');
    }
};
