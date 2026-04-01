import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const vitePort = Number(env.VITE_PORT || 5173);

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/css/auth.css',
                    'resources/css/admin.css',    // Админ-панель
                    'resources/js/admin.js',   // Только для админки
                    'resources/js/app.js',
                    'resources/js/bootstrap.js',
                    'resources/js/password-toggle.js',
                    'resources/js/profile.js',
                    'resources/js/search.js'
                ],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: vitePort,
            strictPort: true,
            hmr: {
                host: env.VITE_HMR_HOST || 'localhost',
                port: vitePort,
            },
        },
    };
});
