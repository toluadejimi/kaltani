import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    server: {
        https: {
            key: fs.readFileSync('/etc/letsencrypt/live/kaltanimis.com/privkey.pem'),
            cert: fs.readFileSync('/etc/letsencrypt/live/kaltanimis.com/fullchain.pem'),
        },
        host: '0.0.0.0',
        port: 5173,
    },

});
