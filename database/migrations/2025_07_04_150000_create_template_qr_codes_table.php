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
            $table->enum('type', ['client', 'creator']); // тип QR-кода: для клиента или создателя
            $table->string('token', 64)->unique(); // уникальный токен
            $table->timestamp('expires_at'); // время истечения
            $table->boolean('is_used')->default(false); // использован ли QR-код
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // кто создал
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null'); // кто использовал
            $table->timestamp('used_at')->nullable(); // когда использован
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['token', 'type']);
            $table->index(['template_id', 'type']);
            $table->index(['expires_at', 'is_used']);
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
