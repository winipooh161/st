<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\UserTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserTemplateController extends Controller
{
    /**
     * Display a listing of templates.
     * - Для авторизованных пользователей: их собственные шаблоны
     * - Для неавторизованных: перенаправление на страницу входа
     */
    public function index()
    {
        // Для авторизованных пользователей показываем только их созданные шаблоны
        // (исключаем шаблоны с суффиксом "(копия)" в названии, которые были сохранены от других пользователей)
        if (Auth::check()) {
            $userTemplates = UserTemplate::with('template')
                                      ->where('user_id', Auth::id())
                                      ->where('name', 'not like', '% (копия)')
                                      ->orderBy('created_at', 'desc')
                                      ->get();
                                      
            return view('my-templates', compact('userTemplates'));
        } else {
            // Для неавторизованных пользователей - перенаправление на страницу входа
            return redirect()->route('login')->with('error', 'Для просмотра ваших шаблонов необходимо авторизоваться');
        }
    }

    /**
     * Display a listing of all public templates.
     */
    public function publicTemplates()
    {
        // Показываем все шаблоны для публичного просмотра
        $userTemplates = UserTemplate::with('template')
                                    ->orderBy('created_at', 'desc')
                                    ->get();
        
        return view('public-templates', compact('userTemplates'));
    }

    /**
     * Store a newly created user template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'base_template_id' => 'required|exists:templates,id',
            'template_html' => 'required|string',
            'editable_data' => 'required|array',
            'name' => 'sometimes|string|max:255',
            'cover_path' => 'nullable|string',
            'cover_type' => 'nullable|string|in:image,video',
            'cover_thumbnail' => 'nullable|string',
            'is_series' => 'sometimes|boolean',
            'series_max' => 'nullable|integer|min:0',
            'series_current' => 'nullable|integer|min:0',
            // Поля для даты
            'template_date' => 'nullable|date',
            'date_format' => 'nullable|string|max:50',
            'date_settings' => 'nullable|array',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            // Поддержка старого формата для обратной совместимости
            'series_settings' => 'nullable|array',
            'series_settings.name' => 'nullable|string',
            'series_settings.folder_name' => 'nullable|string',
            'series_settings.total_copies' => 'nullable|integer|min:1',
            'series_settings.available_copies' => 'nullable|integer|min:0',
            'series_settings.description' => 'nullable|string'
        ]);

        try {
            // Отладочная информация: выводим весь запрос для анализа
            Log::debug('Полные данные запроса для сохранения шаблона', [
                'all_data' => $request->all(),
            ]);
            
            // Создаем уникальное имя шаблона, если не предоставлено
            if ($request->name) {
                $templateName = $request->name;
                // Проверяем уникальность имени для данного пользователя
                $counter = 1;
                $originalName = $templateName;
                while (UserTemplate::where('user_id', Auth::id())->where('name', $templateName)->exists()) {
                    $templateName = $originalName . ' (' . $counter . ')';
                    $counter++;
                }
            } else {
                $templateName = 'Мой шаблон ' . (UserTemplate::where('user_id', Auth::id())->count() + 1);
            }
            
            // Подготовка данных для создания шаблона
            $templateData = [
                'user_id' => Auth::id(),
                'template_id' => $request->base_template_id,
                'name' => $templateName,
                'html_content' => $request->template_html,
                'editable_data' => $request->editable_data
            ];
            
            // Добавляем информацию об обложке, если она есть
            if ($request->cover_path) {
                $templateData['cover_path'] = $request->cover_path;
                $templateData['cover_type'] = $request->cover_type;
                if ($request->cover_thumbnail) {
                    $templateData['cover_thumbnail'] = $request->cover_thumbnail;
                }
            }
            
            // Добавляем информацию о дате, если она есть
            if ($request->template_date) {
                $templateData['template_date'] = $request->template_date;
            }
            if ($request->date_format) {
                $templateData['date_format'] = $request->date_format;
            }
            if ($request->date_settings) {
                $templateData['date_settings'] = $request->date_settings;
            }
            
            // Добавляем диапазон дат, если они указаны
            if ($request->date_from) {
                $templateData['date_from'] = $request->date_from;
            }
            if ($request->date_to) {
                $templateData['date_to'] = $request->date_to;
            }
            
            // Отладочная информация
            Log::debug('Данные запроса для сохранения шаблона', [
                'is_series' => $request->input('is_series'),
                'isSeries' => $request->input('isSeries'),
                'series_max' => $request->input('series_max'),
                'seriesMax' => $request->input('seriesMax'),
                'series_current' => $request->input('series_current'),
                'seriesCurrent' => $request->input('seriesCurrent'),
                'series_settings' => $request->input('series_settings')
            ]);
            
            // Обработка различных форматов данных о серии
            $isSeries = null;
            $seriesMax = null;
            $seriesCurrent = null;
            
            // Проверяем прямые поля is_series/series_max/series_current
            if ($request->has('is_series')) {
                $isSeries = filter_var($request->is_series, FILTER_VALIDATE_BOOLEAN);
                $seriesMax = $request->series_max ?? null;
                $seriesCurrent = $request->series_current ?? null;
            }
            
            // Проверяем camelCase поля isSeries/seriesMax/seriesCurrent
            if ($isSeries === null && $request->has('isSeries')) {
                $isSeries = filter_var($request->isSeries, FILTER_VALIDATE_BOOLEAN);
            }
            if ($seriesMax === null && $request->has('seriesMax')) {
                $seriesMax = $request->seriesMax;
            }
            if ($seriesCurrent === null && $request->has('seriesCurrent')) {
                $seriesCurrent = $request->seriesCurrent;
            }
            
            // Проверяем структуру series_settings
            if ($isSeries === null && $request->has('series_settings') && is_array($request->series_settings)) {
                $isSeries = true;
                $seriesMax = $request->series_settings['total_copies'] ?? null;
                $seriesCurrent = $request->series_settings['available_copies'] ?? null;
            }
            
            // Устанавливаем значения по умолчанию, если не заданы
            if ($isSeries !== null && $isSeries) {
                $templateData['is_series'] = true;
                $templateData['series_max'] = $seriesMax ?? 10;
                // Для новой серии series_current равен series_max (все использования доступны)
                $templateData['series_current'] = $seriesMax ?? 10;
                // Генерируем уникальный UUID для серии
                $templateData['series_uuid'] = \Illuminate\Support\Str::uuid()->toString();
                
                Log::debug('Итоговые данные серии', [
                    'is_series' => $templateData['is_series'],
                    'series_max' => $templateData['series_max'],
                    'series_current' => $templateData['series_current'],
                    'series_uuid' => $templateData['series_uuid']
                ]);
            }
            
            $userTemplate = UserTemplate::create($templateData);

            return response()->json([
                'success' => true,
                'message' => 'Шаблон успешно сохранен!',
                'template_id' => $userTemplate->id,
                'redirect_url' => route('my-templates')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сохранении шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified user template (public page).
     */
    public function show(UserTemplate $userTemplate)
    {
        // Убрана проверка принадлежности шаблона пользователю - страница публичная
        
        // Получаем HTML содержимое шаблона
        $html_content = $userTemplate->html_content;
        
        // Инициализируем переменную - пользователь не имеет копии по умолчанию
        $userHasCopy = false;
        
        // Проверяем, есть ли у пользователя копия этого шаблона
        if (Auth::check()) {
            // Проверяем принадлежность шаблона текущему пользователю
            if ($userTemplate->user_id === Auth::id()) {
                // Если это шаблон текущего пользователя, то считаем что копии нет
                $userHasCopy = false;
            } else {
                // Иначе проверяем, есть ли у пользователя копия конкретного шаблона
                $userHasCopy = UserTemplate::where('user_id', Auth::id())
                            ->where('name', 'like', '%' . $userTemplate->name . ' (копия)%')
                            ->exists();
            }
        }
        
        // Загружаем связанную серию шаблонов, если она есть
        if ($userTemplate->series_id) {
            $userTemplate->load('series');
        }
        
        // Проверяем, принадлежит ли шаблон к серии
        if ($userTemplate->is_series && $userTemplate->series_max > 0) {
            // Формируем данные о серии для передачи в шаблон
            $seriesData = [
                'isSeries' => true,
                'max' => $userTemplate->series_max,
                'current' => $userTemplate->series_current,
                'userHasCopy' => $userHasCopy
            ];
        } else {
            // Даже если нет серии, инициализируем базовые данные для корректной работы
            $seriesData = [
                'isSeries' => false,
                'userHasCopy' => $userHasCopy
            ];
        }
        
        return view('templates.show', compact('userTemplate', 'userHasCopy', 'html_content', 'seriesData'));
    }

    /**
     * Show the HTML content of the user template (public page).
     */
    public function showHtml(UserTemplate $userTemplate)
    {
        // Убрана проверка принадлежности шаблона пользователю - страница публичная
        
        // Добавляем JavaScript для передачи информации о серии шаблонов
        $html = $userTemplate->html_content;
        
        // Инициализируем переменную - пользователь не имеет копии по умолчанию
        $userHasCopy = false;
        
        // Проверяем, есть ли у пользователя копия этого шаблона
        if (Auth::check()) {
            // Проверяем принадлежность шаблона текущему пользователю
            if ($userTemplate->user_id === Auth::id()) {
                // Если это шаблон текущего пользователя, то считаем что копии нет
                $userHasCopy = false;
            } else {
                // Иначе проверяем, есть ли у пользователя копия конкретного шаблона
                $userHasCopy = UserTemplate::where('user_id', Auth::id())
                            ->where('name', 'like', '%' . $userTemplate->name . ' (копия)%')
                            ->exists();
            }
        }
        
        // Проверяем, принадлежит ли шаблон к серии
        if ($userTemplate->series_id) {
            // Загружаем связанную серию
            $userTemplate->load('series');
            
            // Формируем данные о серии для передачи в шаблон
            $seriesData = [
                'name' => $userTemplate->series->name,
                'description' => $userTemplate->series->description,
                'total' => $userTemplate->series->total_copies,
                'available' => $userTemplate->series->available_copies,
                'userHasCopy' => $userHasCopy
            ];
            
            // Добавляем скрипт для передачи данных серии в шаблон через postMessage
            $seriesDataScript = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Отправляем данные о серии родительскому окну
                    window.parent.postMessage(JSON.stringify({
                        seriesData: " . json_encode($seriesData) . "
                    }), '*');
                });
            </script>";
            
            // Вставляем скрипт перед закрывающим тегом </body>
            $html = str_replace('</body>', $seriesDataScript . '</body>', $html);
        } else {
            // Даже если нет серии, инициализируем базовые данные для корректной работы
            $seriesData = [
                'userHasCopy' => $userHasCopy
            ];
            
            // Добавляем скрипт для передачи данных
            $seriesDataScript = "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Отправляем базовые данные родительскому окну
                    window.parent.postMessage(JSON.stringify({
                        seriesData: " . json_encode($seriesData) . "
                    }), '*');
                });
            </script>";
            
            // Вставляем скрипт перед закрывающим тегом </body>
            $html = str_replace('</body>', $seriesDataScript . '</body>', $html);
        }
        
        return response($html)
               ->header('Content-Type', 'text/html');
    }

    /**
     * Show the form for editing the specified user template.
     * @deprecated Функция редактирования отключена
     */
    /*
    public function edit(UserTemplate $userTemplate)
    {
        // Проверяем, что шаблон принадлежит текущему пользователю
        if ($userTemplate->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав для редактирования этого шаблона');
        }

        // Если у шаблона есть обложка, добавляем ее в сессию
        if ($userTemplate->cover_path) {
            session([
                'media_editor_file' => $userTemplate->cover_path,
                'media_editor_type' => $userTemplate->cover_type,
                'media_editor_thumbnail' => $userTemplate->cover_thumbnail
            ]);
        } else {
            // Очищаем сессию, если обложки нет
            session()->forget(['media_editor_file', 'media_editor_type', 'media_editor_thumbnail']);
        }

        $baseTemplate = $userTemplate->template;
        return view('templates.edit', compact('userTemplate', 'baseTemplate'));
    }
    */

    /**
     * Update the specified user template.
     * @deprecated Функция редактирования отключена
     */
    /*
    public function update(Request $request, UserTemplate $userTemplate)
    {
        // Проверяем, что шаблон принадлежит текущему пользователю
        if ($userTemplate->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав для редактирования этого шаблона');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'template_html' => 'required|string',
            'editable_data' => 'required|array',
            'cover_path' => 'nullable|string',
            'cover_type' => 'nullable|string|in:image,video',
            'cover_thumbnail' => 'nullable|string'
        ]);

        try {
            // Подготовка данных для обновления
            $updateData = [
                'name' => $request->name,
                'html_content' => $request->template_html,
                'editable_data' => $request->editable_data
            ];
            
            // Проверяем, есть ли данные об обложке в сессии
            if (session('media_editor_file')) {
                $updateData['cover_path'] = session('media_editor_file');
                $updateData['cover_type'] = session('media_editor_type');
                if (session('media_editor_thumbnail')) {
                    $updateData['cover_thumbnail'] = session('media_editor_thumbnail');
                }
            } elseif ($request->cover_path) {
                // Если в сессии нет, но есть в запросе
                $updateData['cover_path'] = $request->cover_path;
                $updateData['cover_type'] = $request->cover_type;
                if ($request->cover_thumbnail) {
                    $updateData['cover_thumbnail'] = $request->cover_thumbnail;
                }
            }
            
            $userTemplate->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Шаблон успешно обновлен!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении шаблона: ' . $e->getMessage()
            ], 500);
        }
    }
    */

    /**
     * Remove the specified user template from storage.
     */
    public function destroy(UserTemplate $userTemplate)
    {
        // Проверяем, что шаблон принадлежит текущему пользователю
        if ($userTemplate->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав для удаления этого шаблона');
        }

        try {
            // Удаляем превью изображение, если есть
            if ($userTemplate->preview_image) {
                Storage::delete('public/' . $userTemplate->preview_image);
            }

            $userTemplate->delete();

            return redirect()->route('my-templates')
                           ->with('success', 'Шаблон успешно удален!');

        } catch (\Exception $e) {
            return redirect()->route('my-templates')
                           ->with('error', 'Ошибка при удалении шаблона: ' . $e->getMessage());
        }
    }

    /**
     * Download user template as HTML file.
     */
    public function download(UserTemplate $userTemplate)
    {
        // Проверяем, что шаблон принадлежит текущему пользователю
        if ($userTemplate->user_id !== Auth::id()) {
            abort(403, 'У вас нет прав для скачивания этого шаблона');
        }

        $filename = str_replace(' ', '_', $userTemplate->name) . '.html';
        
        return response($userTemplate->html_content)
               ->header('Content-Type', 'text/html')
               ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    /**
     * Сохранение шаблона другого пользователя в свою коллекцию.
     * 
     * @param UserTemplate $userTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveToCollection(UserTemplate $userTemplate)
    {
        // Проверяем, что шаблон не принадлежит текущему пользователю
        if ($userTemplate->user_id === Auth::id()) {
            // Перенаправляем на страницу своих шаблонов с сообщением
            return redirect()->route('my-templates')->with('info', 'Этот шаблон уже принадлежит вам.');
        }
        
        // Проверяем, не сохранил ли пользователь уже этот конкретный шаблон
        $exists = UserTemplate::where('user_id', Auth::id())
            ->where('name', 'like', '%' . $userTemplate->name . ' (копия)%')
            ->exists();
            
        if ($exists) {
            return redirect()->back()->with('error', 'Вы уже сохранили этот шаблон в свою коллекцию.');
        }
        
        // Проверяем, не пытается ли пользователь сохранить уже имеющийся у него шаблон
        if ($userTemplate->is_series) {
            // Если шаблон - серия, проверяем есть ли у пользователя уже шаблон из этой серии
            $existingTemplate = UserTemplate::where('user_id', Auth::id())
                ->where('parent_series_id', $userTemplate->id)
                ->first();
            
            if ($existingTemplate) {
                return redirect()->back()->with('error', 'У вас уже есть шаблон из этой серии.');
            }
        } elseif ($userTemplate->parent_series_id) {
            // Если шаблон принадлежит к серии, проверяем есть ли у пользователя уже шаблон из этой серии
            $existingTemplate = UserTemplate::where('user_id', Auth::id())
                ->where('parent_series_id', $userTemplate->parent_series_id)
                ->first();
            
            if ($existingTemplate) {
                return redirect()->back()->with('error', 'У вас уже есть шаблон из этой серии.');
            }
        } else {
            // Если шаблон не является серией, проверяем есть ли у пользователя уже этот шаблон
            $existingTemplate = UserTemplate::where('user_id', Auth::id())
                ->where('template_id', $userTemplate->template_id)
                ->first();
            
            if ($existingTemplate) {
                return redirect()->back()->with('error', 'У вас уже есть этот шаблон.');
            }
        }
        
        // Проверяем, является ли шаблон серией или имеет ли он родительскую серию
        $seriesTemplate = null;
        if ($userTemplate->is_series) {
            // Если сам шаблон - серия, то используем его
            $seriesTemplate = $userTemplate;
        } elseif ($userTemplate->parent_series_id) {
            // Если шаблон принадлежит к серии, находим родительскую серию
            $seriesTemplate = UserTemplate::find($userTemplate->parent_series_id);
        }
        
        // Проверяем, не исчерпана ли серия
        if ($seriesTemplate && $seriesTemplate->series_current <= 0) {
            return redirect()->back()->with('error', 'Серия шаблонов исчерпана. Нельзя получить больше копий.');
        }
        
        // Создаем копию шаблона в коллекции пользователя
        $newUserTemplate = UserTemplate::create([
            'user_id' => Auth::id(),
            'template_id' => $userTemplate->template_id,
            'name' => $userTemplate->name . ' (копия)',
            'html_content' => $userTemplate->html_content,
            'editable_data' => $userTemplate->editable_data,
            'preview_image' => $userTemplate->preview_image,
            'cover_path' => $userTemplate->cover_path,
            'cover_type' => $userTemplate->cover_type,
            'cover_thumbnail' => $userTemplate->cover_thumbnail,
            'date_from' => $userTemplate->date_from,
            'date_to' => $userTemplate->date_to,
            'template_date' => $userTemplate->template_date,
            'date_format' => $userTemplate->date_format,
            'date_settings' => $userTemplate->date_settings,
           
            'is_series' => false,
            'series_max' => 0,
            'series_current' => 0,
            'parent_series_id' => $userTemplate->is_series ? $userTemplate->id : $userTemplate->parent_series_id,
            'series_uuid' => null, // У копии нет собственного UUID
        ]);
        
        // Уменьшаем счетчик серии у оригинального шаблона
        if ($seriesTemplate) {
            $seriesTemplate->decrement('series_current');
            Log::info('Уменьшен счетчик серии для шаблона', [
                'template_id' => $seriesTemplate->id,
                'new_series_current' => $seriesTemplate->series_current - 1
            ]);
        }
        
        return redirect()->route('home')->with('success', 'Шаблон успешно сохранен в вашу коллекцию.');
    }
}
