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
        host: 'kaltanimis.com',
        port: 5173,
        strictPort: true,
        cors: true,
        https: {
            key: fs.readFileSync('/etc/letsencrypt/live/kaltanimis.com/privkey.pem'),
            cert: fs.readFileSync('/etc/letsencrypt/live/kaltanimis.com/fullchain.pem'),
        },
    },
});
