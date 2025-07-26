import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],

    server: {
        host: '203.161.41.46',
        port: 5173,
        strictPort: true,
        cors: true,
        https: false,
    },
});
