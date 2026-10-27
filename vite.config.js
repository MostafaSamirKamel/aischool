import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/shield-permissions.js',
                'resources/css/filament-admin-theme.css', // Custom Admin Theme
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/instructor/theme.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
