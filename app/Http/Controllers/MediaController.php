<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Создание нового экземпляра контроллера.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Отображение редактора медиафайлов.
     *
     * @param string|null $template
     * @return \Illuminate\View\View
     */
    public function editor($template = null)
    {
        return redirect()->route('media.editor');
    }

    /**
     * Обработка загруженных медиафайлов.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request)
    {
        // Перенаправление на основной метод обработки медиа
        return app()->call([app()->make('App\Http\Controllers\MediaEditorController'), 'processMedia'], [
            'request' => $request
        ]);
    }
}
   