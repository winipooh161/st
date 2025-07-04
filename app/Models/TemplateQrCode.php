<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateQrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'type', // 'client' или 'creator'
        'token',
        'expires_at',
        'is_used',
        'created_by',
        'used_by',
        'used_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /**
     * Связь с шаблоном
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(UserTemplate::class, 'template_id');
    }

    /**
     * Пользователь, создавший QR-код
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Пользователь, использовавший QR-код
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Проверка, действителен ли QR-код
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expires_at > now();
    }

    /**
     * Проверка, истек ли QR-код
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }
}
