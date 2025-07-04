<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\MediaOptimizationService;

class MediaEditorController extends Controller
{
    /**
     * Сервис оптимизации медиафайлов
     * 
     * @var \App\Services\MediaOptimizationService
     */
    protected $mediaOptimizationService;
    
    /**
     * Создание нового экземпляра контроллера.
     *
     * @param \App\Services\MediaOptimizationService $mediaOptimizationService
     * @return void
     */
    public function __construct(MediaOptimizationService $mediaOptimizationService)
    {
        $this->middleware('auth');
        $this->mediaOptimizationService = $mediaOptimizationService;
    }

    /**
     * Отображение редактора медиафайлов.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Очищаем старые данные сессии при открытии редактора
        Session::forget(['media_editor_file', 'media_editor_type', 'media_editor_thumbnail']);
        
        return view('media.editor');
    }

    /**
     * Обработка загруженных медиафайлов.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processMedia(Request $request)
    {
        $request->validate([
            'media_file' => 'required|file|max:50000|mimes:jpeg,png,jpg,gif',
        ]);

        $file = $request->file('media_file');
        $extension = $file->getClientOriginalExtension();
        $mediaType = $file->getClientMimeType();
        
        // Тип медиа всегда изображение
        $mediaTypeStr = 'image';
        
        // Создаем имя файла и путь для сохранения
        $fileName = 'media_' . time() . '_' . Str::random(10) . '.' . $extension;
        $filePath = 'media/' . auth()->id();
        
        // Сохраняем исходный файл
        $savedPath = $file->storeAs($filePath, $fileName, 'public');
        
        try {
            // Обрабатываем изображение
            $options = [
                'maxWidth' => 1200,
                'maxHeight' => 1200,
                'webpQuality' => 80,
                'convertToWebP' => true
            ];
            
            if ($request->has('crop_data')) {
                $cropData = json_decode($request->input('crop_data'), true);
                $options = array_merge($options, $cropData);
            }
            
            $result = $this->mediaOptimizationService->processImage($savedPath, $options);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Ошибка при обработке изображения'
                ], 500);
            }
            
            $processedPath = $result['file_path'];
            
            // Сохраняем в сессии информацию о файле
            Session::put('media_editor_file', $processedPath);
            Session::put('media_editor_type', $mediaTypeStr);
            
            $response = [
                'success' => true,
                'file_path' => Storage::url($processedPath),
                'file_name' => $processedPath,
                'file_type' => $mediaTypeStr,
                'session_id' => Session::getId()
            ];
            
            // Добавляем URL для перенаправления, если он был передан в запросе
            if ($request->has('return_url')) {
                $response['return_url'] = $request->input('return_url');
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('MediaEditor: Ошибка при обработке файла', [
                'error' => $e->getMessage(),
                'file_path' => $savedPath
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при обработке файла: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обрабатывает медиа и перенаправляет на страницу создания шаблона.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processMediaAndCreateTemplate(Request $request)
    {
        $request->validate([
            'media_file' => 'required|file|max:50000|mimes:jpeg,png,jpg,gif',
        ]);

        $file = $request->file('media_file');
        $extension = $file->getClientOriginalExtension();
        $mediaType = $file->getClientMimeType();
        
        // Тип медиа всегда изображение
        $mediaTypeStr = 'image';
        
        // Создаем имя файла и путь для сохранения
        $fileName = 'media_' . time() . '_' . Str::random(10) . '.' . $extension;
        $filePath = 'media/' . auth()->id();
        
        // Сохраняем исходный файл
        $savedPath = $file->storeAs($filePath, $fileName, 'public');
        
        try {
            // Обрабатываем изображение
            $options = [
                'maxWidth' => 1200,
                'maxHeight' => 1200,
                'webpQuality' => 80,
                'convertToWebP' => true
            ];
            
            if ($request->has('crop_data')) {
                $cropData = json_decode($request->input('crop_data'), true);
                $options = array_merge($options, $cropData);
            }
            
            $result = $this->mediaOptimizationService->processImage($savedPath, $options);
            
            if (!$result['success']) {
                return back()->with('error', $result['error'] ?? 'Ошибка при обработке изображения');
            }
            
            $processedPath = $result['file_path'];
            
            // Сохраняем в сессии информацию о файле для передачи на страницу создания шаблона
            Session::put('media_editor_file', $processedPath);
            Session::put('media_editor_type', $mediaTypeStr);
            
            // Определяем URL для перенаправления
            $redirectUrl = $request->input('return_url') ?: route('templates.create');
            
            // Перенаправляем на соответствующую страницу
            return redirect($redirectUrl)->with([
                'media_file' => Storage::url($processedPath),
                'media_type' => $mediaTypeStr,
                'media_thumbnail' => null
            ]);
            
        } catch (\Exception $e) {
            Log::error('MediaEditor: Ошибка при обработке файла', [
                'error' => $e->getMessage(),
                'file_path' => $savedPath
            ]);
            
            return back()->with('error', 'Произошла ошибка при обработке файла: ' . $e->getMessage());
        }
    }
    
    /**
     * Отображение редактора для конкретного шаблона.
     *
     * @param int $templateId
     * @return \Illuminate\View\View
     */
    public function editForTemplate($templateId)
    {
        // Очищаем старые данные сессии
        Session::forget(['media_editor_file', 'media_editor_type', 'media_editor_thumbnail']);
        
        return view('media.editor', [
            'template_id' => $templateId
        ]);
    }
}
