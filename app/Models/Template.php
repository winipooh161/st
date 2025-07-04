<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'html_content',
        'editable_elements',
        'is_base_template',
        'is_active',
        'preview_image'
    ];

    protected $casts = [
        'is_base_template' => 'boolean',
        'is_active' => 'boolean',
        'editable_elements' => 'array',
    ];

    /**
     * Получить базовый шаблон для пользователей без кеширования
     * @param bool $isCreate Флаг создания шаблона (для условного отображения элементов)
     * @return \App\Models\Template|null
     */
    public static function getBaseTemplate($isCreate = false)
    {
        $template = self::where('is_base_template', true)
                   ->where('is_active', true)
                   ->select(['id', 'name', 'html_content', 'editable_elements', 'updated_at'])
                   ->first();
        
        if ($template && $isCreate) {
            // Добавляем к объекту информацию о контексте создания шаблона
            $template->isCreatePage = true;
        }
        
        return $template;
    }
    
    // Метод clearBaseTemplateCache удален, поскольку мы убрали кеширование

    /**
     * Связь с пользовательскими шаблонами
     */
    public function userTemplates()
    {
        return $this->hasMany(UserTemplate::class);
    }
}
