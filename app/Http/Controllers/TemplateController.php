<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TemplateController extends Controller
{
    /**
     * Обновление полей шаблона через API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFields(Request $request)
    {
        try {
            $request->validate([
                'template_id' => 'required',
                'fields' => 'required|array'
            ]);
            
            $templateId = $request->input('template_id');
            $fields = $request->input('fields');
            
            // Находим шаблон по ID
            $template = Template::find($templateId);
            
            // Если шаблон не найден, пытаемся найти пользовательский шаблон
            if (!$template) {
                $userTemplate = \App\Models\UserTemplate::where('slug', $templateId)
                    ->orWhere('id', $templateId)
                    ->first();
                
                if ($userTemplate) {
                    // Обновляем поля пользовательского шаблона
                    $userTemplate->fields = $fields;
                    $userTemplate->save();
                    
                    Log::info('Поля пользовательского шаблона обновлены', [
                        'template_id' => $templateId,
                        'fields_count' => count($fields)
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Поля пользовательского шаблона успешно обновлены',
                        'template_id' => $userTemplate->id
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон не найден'
                ], 404);
            }
            
            // Обновляем поля шаблона
            $template->fields = $fields;
            $template->save();
            
            Log::info('Поля шаблона обновлены', [
                'template_id' => $templateId,
                'fields_count' => count($fields)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Поля шаблона успешно обновлены',
                'template_id' => $template->id
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении полей шаблона: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении полей шаблона: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Показать страницу создания шаблона с оптимизацией
     */
    public function create()
    {
        try {
            // Проверяем, что пользователь пришел с медиа-редактора или у него уже есть данные об обложке
            $referrer = request()->headers->get('referer');
            $hasMediaEditorData = session()->has('media_editor_file') && session()->has('media_editor_type');
            
            if (!$hasMediaEditorData && (!$referrer || !str_contains($referrer, '/media/editor'))) {
                // Перенаправляем на медиа-редактор, если пользователь пытается открыть страницу напрямую
                return redirect()->route('media.editor', ['return_url' => route('templates.create')]);
            }
            
            // Передаем флаг true для указания, что шаблон загружается на странице создания
            $baseTemplate = Template::getBaseTemplate(true);
            
            if (!$baseTemplate) {
                return view('templates.create', [
                    'baseTemplate' => null,
                    'error' => 'Базовый шаблон не найден'
                ]);
            }
            
            // Передаем флаг isCreatePage в представление
            return view('templates.create', compact('baseTemplate'));
            
        } catch (\Exception $e) {
            Log::error('Ошибка загрузки базового шаблона: ' . $e->getMessage());
            
            return view('templates.create', [
                'baseTemplate' => null,
                'error' => 'Ошибка загрузки шаблона'
            ]);
        }
    }
    
    /**
     * Предварительный просмотр шаблона с улучшенным кешированием
     */
    public function preview(Template $template)
    {
        // Определяем текущий режим просмотра (создание или публичный просмотр)
        $isCreatePage = request()->route()->getName() === 'templates.create' || 
                         str_contains(request()->headers->get('referer') ?? '', '/templates/create') || 
                         str_contains(request()->headers->get('referer') ?? '', '/templates/edit');
        
        $mode = $isCreatePage ? 'editor' : 'public';
        
        // Используем улучшенное многоуровневое кеширование на стороне сервера для шаблонов
        $cacheKey = 'template_preview_' . $template->id . '_' . md5($template->updated_at) . '_' . $mode;
        $etag = '"' . $cacheKey . '_' . md5(request()->header('User-Agent')) . '"';
        
        // Проверяем If-None-Match заголовок для экономии трафика с поддержкой Vary
        if (request()->header('If-None-Match') === $etag) {
            return response('', 304);
        }
        
        // Добавляем зависимость от User-Agent для адаптивного контента
        $userAgent = request()->header('User-Agent', '');
        $isMobile = preg_match('/(android|iphone|ipod|ipad|mobile)/i', $userAgent);
        
        // Используем оригинальный HTML-контент без оптимизации
        $htmlContent = Cache::remember($cacheKey . '_' . ($isMobile ? 'mobile' : 'desktop'), 86400, function() use ($template) {
            return $template->html_content;
        });
        
        return response($htmlContent)
               ->header('Content-Type', 'text/html; charset=utf-8')
               ->header('Cache-Control', 'public, max-age=86400, s-maxage=86400, immutable') // 24 часа кеширования
               ->header('X-Frame-Options', 'SAMEORIGIN')
               ->header('X-Content-Type-Options', 'nosniff')
               ->header('Vary', 'Accept-Encoding, User-Agent')
               ->header('ETag', $etag)
               ->header('X-Template-Mode', $mode); // Передаем режим в заголовке
    }
    
    // Функция generateResourcePreloads удалена

    // Функция optimizeHtmlForIframe удалена

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Template $template)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        //
    }
    
    /**
     * Удаляет обложку из сессии
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeCover()
    {
        try {
            // Удаляем данные об обложке из сессии
            session()->forget(['media_editor_file', 'media_editor_type', 'media_editor_thumbnail']);
            
            return response()->json([
                'success' => true,
                'message' => 'Обложка успешно удалена'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении обложки: ' . $e->getMessage()
            ], 500);
        }
    }
}
