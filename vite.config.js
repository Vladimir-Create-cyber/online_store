import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/front.css',    // Публичная часть
                'resources/css/admin.css',    // Админ-панель
                'resources/js/front.js',   // Только для фронтенда
                'resources/js/admin.js',   // Только для админки
                'resources/js/app.js',
                'resources/js/bootstrap.js',
                'resources/js/password-toggle',
                'resources/js/profile.js',
                'resources/js/search.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
