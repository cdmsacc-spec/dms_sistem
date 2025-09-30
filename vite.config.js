import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/filament/staff_crew/theme.css', 'resources/js/app.js'], 
            refresh: true,
        }),
    ],
});
