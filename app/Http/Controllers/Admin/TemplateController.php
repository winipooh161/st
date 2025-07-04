<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::orderBy('created_at', 'desc')->get();
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_content' => 'required|string',
            'is_base_template' => 'boolean',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Если создается базовый шаблон, снимаем флаг с других
        if ($request->has('is_base_template') && $request->is_base_template) {
            Template::where('is_base_template', true)->update(['is_base_template' => false]);
        }

        // Автоматически анализируем редактируемые элементы
        $editableElements = $this->extractEditableElements($request->html_content);

        $templateData = [
            'name' => $request->name,
            'description' => $request->description,
            'html_content' => $request->html_content,
            'editable_elements' => $editableElements,
            'is_base_template' => $request->has('is_base_template'),
            'is_active' => true
        ];

        // Обработка загрузки изображения превью
        if ($request->hasFile('preview_image')) {
            $path = $request->file('preview_image')->store('templates/previews', 'public');
            $templateData['preview_image'] = $path;
        }

        $template = Template::create($templateData);

        $elementsCount = count($editableElements);
        $message = "Шаблон успешно создан! Найдено {$elementsCount} редактируемых элементов.";

        return redirect()
            ->route('admin.templates.index')
            ->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Template $template)
    {
        return view('admin.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Template $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_content' => 'required|string',
            'is_base_template' => 'boolean',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Если устанавливается базовый шаблон, снимаем флаг с других
        if ($request->has('is_base_template') && $request->is_base_template) {
            Template::where('is_base_template', true)
                    ->where('id', '!=', $template->id)
                    ->update(['is_base_template' => false]);
        }

        // Автоматически анализируем редактируемые элементы при изменении HTML
        $editableElements = $this->extractEditableElements($request->html_content);

        $templateData = [
            'name' => $request->name,
            'description' => $request->description,
            'html_content' => $request->html_content,
            'editable_elements' => $editableElements,
            'is_base_template' => $request->has('is_base_template'),
        ];

        // Обработка загрузки нового изображения превью
        if ($request->hasFile('preview_image')) {
            // Удаляем старое изображение
            if ($template->preview_image) {
                Storage::disk('public')->delete($template->preview_image);
            }
            
            $path = $request->file('preview_image')->store('templates/previews', 'public');
            $templateData['preview_image'] = $path;
        }

        $template->update($templateData);

        $elementsCount = count($editableElements);
        $message = "Шаблон успешно обновлен! Найдено {$elementsCount} редактируемых элементов.";

        return redirect()
            ->route('admin.templates.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Template $template)
    {
        // Удаляем изображение превью если есть
        if ($template->preview_image) {
            Storage::disk('public')->delete($template->preview_image);
        }

        $template->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Шаблон успешно удален!');
    }

    /**
     * Переключить статус базового шаблона
     */
    public function toggleBaseTemplate(Template $template)
    {
        if (!$template->is_base_template) {
            // Снимаем флаг базового шаблона с других
            Template::where('is_base_template', true)->update(['is_base_template' => false]);
            $template->update(['is_base_template' => true]);
            $message = 'Шаблон установлен как базовый!';
        } else {
            $template->update(['is_base_template' => false]);
            $message = 'Шаблон больше не является базовым!';
        }

        return redirect()
            ->route('admin.templates.index')
            ->with('success', $message);
    }

    /**
     * Анализировать HTML шаблона и найти редактируемые элементы
     */
    public function analyzeEditableElements(Request $request, Template $template)
    {
        try {
            $editableElements = $this->extractEditableElements($template->html_content);
            
            // Сохраняем найденные элементы в базу данных
            $template->update([
                'editable_elements' => $editableElements
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Редактируемые элементы успешно проанализированы',
                'elements_count' => count($editableElements),
                'elements' => $editableElements
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при анализе шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Извлечь редактируемые элементы из HTML
     */
    private function extractEditableElements($htmlContent)
    {
        $editableElements = [];
        
        // Создаем DOMDocument для парсинга HTML
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $elementId = 1;

        // 1. Ищем элементы с data-editable атрибутом (приоритет)
        $dataEditableElements = $xpath->query('//*[@data-editable]');
        foreach ($dataEditableElements as $element) {
            if ($element instanceof \DOMElement) {
                $editableValue = $element->getAttribute('data-editable');
                
                // Используем значение data-editable как ID
                $editableId = $editableValue;
                
                $fieldName = $element->getAttribute('data-field-name') ?: $editableId;
                $fieldType = $element->getAttribute('data-field-type') ?: $this->detectFieldType($element);
                
                $elementData = [
                    'id' => $editableId,
                    'field_name' => $fieldName,
                    'type' => $fieldType,
                    'selector' => '[data-editable="' . $editableValue . '"]',
                    'tag' => $element->nodeName,
                    'content' => $this->getElementContent($element),
                    'placeholder' => $this->generatePlaceholder($fieldType, $fieldName)
                ];
                
                // Добавляем дополнительные атрибуты для ссылок и изображений
                if ($fieldType === 'link' || $fieldType === 'url') {
                    $elementData['href'] = $element->getAttribute('href') ?: '#';
                }
                
                if ($fieldType === 'image') {
                    $elementData['alt'] = $element->getAttribute('alt') ?: '';
                    $elementData['src'] = $element->getAttribute('src') ?: '';
                }
                
                $editableElements[] = $elementData;
            }
        }

        // 2. Ищем элементы с contenteditable
        $contentEditableElements = $xpath->query('//*[@contenteditable="true" or @contenteditable=""]');
        foreach ($contentEditableElements as $element) {
            if ($element instanceof \DOMElement && !$element->hasAttribute('data-editable')) {
                $fieldName = $this->generateFieldName($element, $elementId);
                
                $editableElements[] = [
                    'id' => 'editable_' . $elementId,
                    'field_name' => $fieldName,
                    'type' => 'text',
                    'selector' => $this->generateUniqueSelector($element, $elementId),
                    'tag' => $element->nodeName,
                    'content' => $this->getElementContent($element),
                    'placeholder' => 'Введите текст...'
                ];
                
                $elementId++;
            }
        }

        // 3. Ищем заголовки (h1-h6)
        $headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        foreach ($headings as $heading) {
            if ($heading instanceof \DOMElement && !$heading->hasAttribute('data-editable') && !$heading->hasAttribute('contenteditable')) {
                $text = trim($heading->textContent);
                if (strlen($text) > 0) {
                    $fieldName = 'heading_' . strtolower($heading->nodeName) . '_' . $elementId;
                    
                    $editableElements[] = [
                        'id' => 'editable_' . $elementId,
                        'field_name' => $fieldName,
                        'type' => 'text',
                        'selector' => $this->generateUniqueSelector($heading, $elementId),
                        'tag' => $heading->nodeName,
                        'content' => $text,
                        'placeholder' => 'Введите заголовок...'
                    ];
                    
                    $elementId++;
                }
            }
        }

        // 4. Ищем параграфы с текстом
        $paragraphs = $xpath->query('//p[normalize-space(text()) != ""]');
        foreach ($paragraphs as $paragraph) {
            if ($paragraph instanceof \DOMElement && !$paragraph->hasAttribute('data-editable') && !$paragraph->hasAttribute('contenteditable')) {
                $text = trim($paragraph->textContent);
                if (strlen($text) > 5) { // Только значимые параграфы
                    $fieldName = 'paragraph_' . $elementId;
                    
                    $editableElements[] = [
                        'id' => 'editable_' . $elementId,
                        'field_name' => $fieldName,
                        'type' => 'text',
                        'selector' => $this->generateUniqueSelector($paragraph, $elementId),
                        'tag' => $paragraph->nodeName,
                        'content' => $text,
                        'placeholder' => 'Введите текст...'
                    ];
                    
                    $elementId++;
                }
            }
        }

        // 5. Ищем изображения
        $images = $xpath->query('//img[@src]');
        foreach ($images as $img) {
            if ($img instanceof \DOMElement && !$img->hasAttribute('data-editable')) {
                $src = $img->getAttribute('src');
                $alt = $img->getAttribute('alt');
                $fieldName = 'image_' . $elementId;
                
                $editableElements[] = [
                    'id' => 'editable_' . $elementId,
                    'field_name' => $fieldName,
                    'type' => 'image',
                    'selector' => $this->generateUniqueSelector($img, $elementId),
                    'tag' => 'img',
                    'content' => $src,
                    'alt' => $alt,
                    'placeholder' => 'URL изображения...'
                ];
                
                $elementId++;
            }
        }

        // 6. Ищем ссылки
        $links = $xpath->query('//a[@href and normalize-space(text()) != ""]');
        foreach ($links as $link) {
            if ($link instanceof \DOMElement && !$link->hasAttribute('data-editable')) {
                $text = trim($link->textContent);
                $href = $link->getAttribute('href');
                if (strlen($text) > 0 && $href !== '#') {
                    $fieldName = 'link_' . $elementId;
                    
                    $editableElements[] = [
                        'id' => 'editable_' . $elementId,
                        'field_name' => $fieldName,
                        'type' => 'link',
                        'selector' => $this->generateUniqueSelector($link, $elementId),
                        'tag' => 'a',
                        'content' => $text,
                        'href' => $href,
                        'placeholder' => 'Текст ссылки...'
                    ];
                    
                    $elementId++;
                }
            }
        }

        return $editableElements;
    }

    /**
     * Генерировать CSS селектор для элемента
     */
    private function generateElementSelector($element, $elementId)
    {
        // Добавляем data-editable атрибут для простоты селекции
        if ($element->nodeType === XML_ELEMENT_NODE) {
            $element->setAttribute('data-editable', 'editable_' . $elementId);
            return '[data-editable="editable_' . $elementId . '"]';
        }
        
        return 'body'; // fallback
    }

    /**
     * Определить тип поля по элементу
     */
    private function detectFieldType(\DOMElement $element): string
    {
        $tagName = strtolower($element->nodeName);
        
        switch ($tagName) {
            case 'img':
                return 'image';
            case 'a':
                return 'link';
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return 'heading';
            case 'input':
                $type = $element->getAttribute('type');
                return $type ?: 'text';
            case 'textarea':
                return 'textarea';
            case 'select':
                return 'select';
            default:
                return 'text';
        }
    }

    /**
     * Получить содержимое элемента
     */
    private function getElementContent(\DOMElement $element): string
    {
        $tagName = strtolower($element->nodeName);
        
        switch ($tagName) {
            case 'img':
                return $element->getAttribute('src') ?: '';
            case 'a':
                return trim($element->textContent);
            case 'input':
                return $element->getAttribute('value') ?: '';
            case 'textarea':
                return trim($element->textContent);
            case 'select':
                // Ищем выбранную опцию
                $xpath = new \DOMXPath($element->ownerDocument);
                $selected = $xpath->query('.//option[@selected]', $element)->item(0);
                return ($selected && $selected instanceof \DOMElement) ? $selected->getAttribute('value') : '';
            default:
                return trim($element->textContent);
        }
    }

    /**
     * Сгенерировать placeholder для поля
     */
    private function generatePlaceholder(string $fieldType, string $fieldName): string
    {
        switch ($fieldType) {
            case 'image':
                return 'URL изображения...';
            case 'link':
                return 'Текст ссылки...';
            case 'heading':
                return 'Введите заголовок...';
            case 'email':
                return 'Введите email...';
            case 'tel':
                return 'Введите телефон...';
            case 'url':
                return 'Введите URL...';
            case 'textarea':
                return 'Введите описание...';
            default:
                return 'Введите текст...';
        }
    }

    /**
     * Сгенерировать имя поля для элемента
     */
    private function generateFieldName(\DOMElement $element, int $elementId): string
    {
        // Пробуем получить из атрибутов
        if ($element->hasAttribute('id') && $element->getAttribute('id')) {
            return $element->getAttribute('id');
        }
        
        if ($element->hasAttribute('name') && $element->getAttribute('name')) {
            return $element->getAttribute('name');
        }
        
        if ($element->hasAttribute('class') && $element->getAttribute('class')) {
            $classes = explode(' ', $element->getAttribute('class'));
            foreach ($classes as $class) {
                if (!str_starts_with($class, 'template-') && !str_starts_with($class, 'mb-') && 
                    !str_starts_with($class, 'btn') && !str_starts_with($class, 'd-')) {
                    return $class;
                }
            }
        }
        
        // Генерируем на основе тега
        $tagName = strtolower($element->nodeName);
        return $tagName . '_' . $elementId;
    }

    /**
     * Сгенерировать уникальный CSS селектор
     */
    private function generateUniqueSelector(\DOMElement $element, int $elementId): string
    {
        // Добавляем data-editable атрибут для простого поиска
        $element->setAttribute('data-editable', 'editable_' . $elementId);
        return '[data-editable="editable_' . $elementId . '"]';
    }
}
