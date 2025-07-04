<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'name',
        'html_content',
        'editable_data',
        'preview_image',
        'cover_path',
        'cover_type',
        'cover_thumbnail',
        'is_series',
        'series_max',
        'series_current',
        'series_uuid',
        'parent_series_id',
        'template_date',
        'date_format',
        'date_settings',
        'date_from',
        'date_to'
    ];

    protected $casts = [
        'editable_data' => 'array',
        'date_settings' => 'array',
        'template_date' => 'date',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    /**
     * Связь с пользователем
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с базовым шаблоном
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    

}
