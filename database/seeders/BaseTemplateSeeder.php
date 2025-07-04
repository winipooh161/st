<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class BaseTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем базовый шаблон, если его еще нет
        if (!Template::where('is_base_template', true)->exists()) {
            Template::create([
                'name' => 'Базовый шаблон',
                'description' => 'Простой базовый шаблон для создания новых дизайнов',
                'html_content' => $this->getBaseTemplateHtml(),
                'is_base_template' => true,
                'is_active' => true,
            ]);
        }
    }

    private function getBaseTemplateHtml(): string
    {
        return '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Базовый шаблон</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            aspect-ratio: 9/16;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: linear-gradient(45deg, #4285f4, #34a853);
            color: white;
            padding: 40px 30px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        
        .content h2 {
            color: #2c3e50;
            font-size: 2em;
            margin-bottom: 20px;
            font-weight: 400;
        }
        
        .content p {
            font-size: 1.1em;
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.8;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #4285f4;
        }
        
        .feature h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        
        .feature p {
            color: #6c757d;
            font-size: 0.95em;
            margin: 0;
            line-height: 1.5;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
            flex-shrink: 0;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 0.9em;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                aspect-ratio: unset;
                min-height: calc(100vh - 20px);
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .header p {
                font-size: 1em;
            }
            
            .content h2 {
                font-size: 1.6em;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Добро пожаловать</h1>
            <p>Создайте что-то удивительное</p>
        </div>
        
        <div class="content">
            <h2>Начните свой проект</h2>
            <p>Этот базовый шаблон поможет вам создать красивый и функциональный дизайн. Настройте его под свои потребности.</p>
            
            <div class="features">
                <div class="feature">
                    <h3>🎨 Дизайн</h3>
                    <p>Современный и адаптивный дизайн, который отлично выглядит на всех устройствах</p>
                </div>
                <div class="feature">
                    <h3>⚡ Быстрый</h3>
                    <p>Оптимизированный код для быстрой загрузки и плавной работы</p>
                </div>
                <div class="feature">
                    <h3>🛠️ Гибкий</h3>
                    <p>Легко настраивается и адаптируется под любые требования проекта</p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>© 2025 Ваш проект. Создано с помощью базового шаблона.</p>
        </div>
    </div>
</body>
</html>';
    }
}
