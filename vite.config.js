import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Aplikasi internal (dashboard admin/dosen/mahasiswa + auth)
                'resources/css/app.css',
                'resources/js/app.js',
                // Landing page publik
                'resources/css/landing.css',
                'resources/js/landing.js',
            ],
            refresh: true,
        }),
    ],
});
