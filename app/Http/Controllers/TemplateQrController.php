<?php

namespace App\Http\Controllers;

use App\Models\UserTemplate;
use App\Models\TemplateQrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TemplateQrController extends Controller
{
    /**
     * Генерировать QR-код для клиента (получение шаблона)
     */
    public function generateClientQr(Request $request, UserTemplate $template)
    {
        // Проверяем, что пользователь является владельцем шаблона
        if ($template->user_id !== Auth::id()) {
            return response()->json(['error' => 'У вас нет прав на генерацию QR-кода для этого шаблона'], 403);
        }

        // Проверяем, что это серия
        if (!$template->is_series || $template->series_max <= 0) {
            return response()->json(['error' => 'Этот шаблон не является серией или неправильно настроен'], 400);
        }
        
        // Проверяем, есть ли доступные использования (для показа QR-кода разрешаем только при series_current > 0)
        if ($template->series_current <= 0) {
            // Логируем попытку получения QR-кода для исчерпанной серии
            \Illuminate\Support\Facades\Log::warning('Попытка получить QR-код для исчерпанной серии', [
                'template_id' => $template->id,
                'user_id' => Auth::id(),
                'series_max' => $template->series_max,
                'series_current' => $template->series_current
            ]);
            
            return response()->json([
                'error' => 'Серия завершена - нет доступных использований',
                'series_exhausted' => true
            ], 400);
        }

        // Генерируем уникальный токен для QR-кода
        $token = Str::random(32);
        
        // Создаем или обновляем запись QR-кода
        $qrRecord = TemplateQrCode::updateOrCreate(
            [
                'template_id' => $template->id,
                'type' => 'client'
            ],
            [
                'token' => $token,
                'expires_at' => now()->addHours(24), // QR-код действует 24 часа
                'is_used' => false,
                'created_by' => Auth::id()
            ]
        );

        // URL для клиента (используем правильный маршрут)
        $clientUrl = route('template.claim-via-qr', ['token' => $token]);

        // Генерируем QR-код с помощью Google Charts API
        $qrCodeUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($clientUrl) . "&choe=UTF-8&chld=L|2";
        
        // Сохраняем QR-код как URL (без загрузки файла)
        $qrPath = $qrCodeUrl;

        return response()->json([
            'success' => true,
            'qr_url' => $qrPath,
            'token' => $token,
            'expires_at' => $qrRecord->expires_at->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Генерировать QR-код для создателя (деактивация шаблона)
     */
    public function generateCreatorQr(Request $request, UserTemplate $template)
    {
        // Проверяем, что пользователь является владельцем шаблона
        if ($template->user_id !== Auth::id()) {
            return response()->json(['error' => 'У вас нет прав на генерацию QR-кода для этого шаблона'], 403);
        }

        // Проверяем, что это серия ИЛИ шаблон, полученный из серии
        $isOriginalSeries = $template->is_series && $template->series_max > 0;
        $isFromSeries = str_contains($template->name, '(Получено по QR)');
        $isUsed = str_contains($template->name, '(Использован)');
        
        if (!$isOriginalSeries && !$isFromSeries) {
            return response()->json(['error' => 'Этот шаблон не связан с серией'], 400);
        }
        
        // Проверяем, что шаблон еще не использован
        if ($isUsed) {
            return response()->json(['error' => 'Этот шаблон уже помечен как использованный'], 400);
        }

        // Генерируем уникальный токен для QR-кода создателя
        $token = Str::random(32);
        
        // Создаем или обновляем запись QR-кода
        $qrRecord = TemplateQrCode::updateOrCreate(
            [
                'template_id' => $template->id,
                'type' => 'creator'
            ],
            [
                'token' => $token,
                'expires_at' => now()->addHours(1), // QR-код создателя действует 1 час
                'is_used' => false,
                'created_by' => Auth::id()
            ]
        );

        // URL для создателя
        $creatorUrl = route('template.deactivate-via-qr', ['token' => $token]);

        // Генерируем QR-код с помощью Google Charts API
        $qrCodeUrl = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($creatorUrl) . "&choe=UTF-8&chld=L|2";
        
        // Сохраняем QR-код как URL (без загрузки файла)
        $qrPath = $qrCodeUrl;

        return response()->json([
            'success' => true,
            'qr_url' => $qrPath,
            'token' => $token,
            'expires_at' => $qrRecord->expires_at->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Клиент сканирует QR-код и получает шаблон
     */
    public function claimViaQr(Request $request, $token)
    {
        $qrRecord = TemplateQrCode::where('token', $token)
            ->where('type', 'client')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$qrRecord) {
            return redirect()->route('home')->with('error', 'QR-код недействителен или истек срок его действия');
        }

        $template = $qrRecord->template;
        
        // Проверяем, что шаблон существует
        if (!$template) {
            return redirect()->route('home')->with('error', 'Шаблон не найден');
        }

        // Проверяем, что у шаблона есть доступные использования
        if (!$template->is_series || $template->series_current <= 0) {
            // Помечаем QR-код как использованный, чтобы избежать повторных попыток
            $qrRecord->update([
                'is_used' => true,
                'used_at' => now()
            ]);
            
            return redirect()->route('home')->with('error', 'Нет доступных использований для этой серии или серия уже завершена');
        }

        // Если пользователь не авторизован, перенаправляем на вход
        if (!Auth::check()) {
            session(['qr_claim_token' => $token]);
            return redirect()->route('login')->with('info', 'Войдите в систему, чтобы получить шаблон');
        }

        // Проверяем, что пользователь еще не получал шаблон из этой конкретной серии
        // Используем parent_series_id для более точной проверки
        $existingCopy = UserTemplate::where('user_id', Auth::id())
            ->where('parent_series_id', $template->id)
            ->exists();

        if ($existingCopy) {
            return redirect()->route('templates.show', $template->id)
                ->with('info', 'У вас уже есть копия этого шаблона из данной серии');
        }

        // Создаем копию шаблона для пользователя
        $userTemplate = UserTemplate::create([
            'user_id' => Auth::id(),
            'name' => $template->name . ' (Получено по QR)',
            'description' => $template->description,
            'template_id' => $template->template_id,
            'html_content' => $template->html_content,
            'editable_data' => $template->editable_data,
            'cover_path' => $template->cover_path,
            'cover_type' => $template->cover_type,
            'cover_thumbnail' => $template->cover_thumbnail,
            'date_from' => $template->date_from,
            'date_to' => $template->date_to,
            'template_date' => $template->template_date,
            'date_format' => $template->date_format,
            'date_settings' => $template->date_settings,
            'is_series' => false, // Полученный шаблон не является серией
            'series_max' => 0,
            'series_current' => 0,
            'parent_series_id' => $template->id, // Указываем родительскую серию
            'series_uuid' => null, // У копии нет собственного UUID
        ]);

        // Уменьшаем количество доступных использований в оригинальном шаблоне серии
        $template->decrement('series_current');

        // Помечаем QR-код как использованный
        $qrRecord->update([
            'is_used' => true,
            'used_by' => Auth::id(),
            'used_at' => now()
        ]);

        return redirect()->route('templates.show', $userTemplate->id)
            ->with('success', 'Шаблон успешно получен!');
    }

    /**
     * Создатель сканирует QR-код и деактивирует шаблон
     */
    public function deactivateViaQr(Request $request, $token)
    {
        $qrRecord = TemplateQrCode::where('token', $token)
            ->where('type', 'creator')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$qrRecord) {
            return redirect()->route('home')->with('error', 'QR-код недействителен или истек срок его действия');
        }

        $template = $qrRecord->template;

        // Проверяем, что пользователь является владельцем шаблона
        if ($template->user_id !== Auth::id()) {
            return redirect()->route('home')->with('error', 'У вас нет прав на деактивацию этого шаблона');
        }

        // Деактивируем шаблон (помечаем как использованный)
        // Если это шаблон из серии (полученный по QR), просто помечаем его как неактивный
        if (str_contains($template->name, '(Получено по QR)')) {
            $template->update([
                'name' => $template->name . ' (Использован)'
            ]);
        } else {
            // Если это оригинальный шаблон серии, деактивируем его
            $template->update([
                'series_current' => 0,
                'name' => $template->name . ' (Деактивирован)'
            ]);
        }

        // Помечаем QR-код как использованный
        $qrRecord->update([
            'is_used' => true,
            'used_by' => Auth::id(),
            'used_at' => now()
        ]);

        return redirect()->route('templates.show', $template->id)
            ->with('success', 'Шаблон успешно деактивирован!');
    }

    /**
     * Получить состояние QR-кода для шаблона
     */
    public function getQrStatus(UserTemplate $template)
    {
        // Проверяем права доступа
        if ($template->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $clientQr = TemplateQrCode::where('template_id', $template->id)
            ->where('type', 'client')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        $creatorQr = TemplateQrCode::where('template_id', $template->id)
            ->where('type', 'creator')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        return response()->json([
            'client_qr_active' => $clientQr ? true : false,
            'creator_qr_active' => $creatorQr ? true : false,
            'client_qr_url' => $clientQr ? "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode(route('template.claim-via-qr', ['token' => $clientQr->token])) . "&choe=UTF-8&chld=L|2" : null,
            'creator_qr_url' => $creatorQr ? "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode(route('template.deactivate-via-qr', ['token' => $creatorQr->token])) . "&choe=UTF-8&chld=L|2" : null,
            'series_current' => $template->series_current,
            'series_max' => $template->series_max
        ]);
    }

    /**
     * Проверить статус серии шаблона
     */
    public function checkSeriesStatus(UserTemplate $template)
    {
        // Проверяем права доступа
        if ($template->user_id !== Auth::id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $isOriginalSeries = $template->is_series && $template->series_max > 0;
        $isFromSeries = str_contains($template->name, '(Получено по QR)');
        $isUsed = str_contains($template->name, '(Использован)');
        $seriesAvailable = $template->series_current > 0;

        return response()->json([
            'is_series' => $isOriginalSeries,
            'from_series' => $isFromSeries,
            'is_used' => $isUsed,
            'series_available' => $seriesAvailable,
            'series_current' => $template->series_current,
            'series_max' => $template->series_max,
            'can_generate_qr' => ($isOriginalSeries && $seriesAvailable) || ($isFromSeries && !$isUsed)
        ]);
    }
}
