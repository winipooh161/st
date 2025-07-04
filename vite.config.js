import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',   'resources/css/style.css',
                'resources/css/modal-styles.css',
                'resources/css/mobile-nav-optimized.css',
                'resources/css/media-editor.css', // Добавляем медиа-редактор
                'resources/js/app.js',      'resources/js/mobile-nav/index.js',
                'resources/js/mobile-nav-wheel-picker.js',
                'resources/js/loading-spinner.js',
                'resources/js/register-sw.js', // Добавляем скрипт для регистрации Service Worker
            
              
                'public/js/media-editor.js', // Добавляем медиа-редактор
               
                'resources/sass/app.scss',
            ],
            refresh: true,
        }),
    ],
    server: {
        // Настройка CORS для разработки
        cors: {
            origin: ['https://tyty', 'http://tyty', 'http://localhost', 'http://127.0.0.1', 'http://localhost:5173'],
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            credentials: true
        },
        // Настройка для доступа по сети
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        // Дополнительные заголовки для CORS
        headers: {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers': 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
        },
    },
});
 