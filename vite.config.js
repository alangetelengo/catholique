import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/financial-statistics-charts.js',
                'resources/js/dashboard-charts.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        // Laragon / vhost personnalisé (ex. http://catholique) : autre origine que [::1]:5173 → CORS sans ces réglages
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: {
            origin: true,
            credentials: true,
        },
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
        },
    },
});
