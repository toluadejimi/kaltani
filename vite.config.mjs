// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel([
//             'resources/css/app.css',
//             'resources/js/app.js',
//         ]),
//     ],
// });

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],

    https: {
        key: fs.readFileSync('/etc/letsencrypt/live/kaltanimis.com/privkey.pem'),
        cert: fs.readFileSync('/etc/letsencrypt/live/kaltanimis.com/fullchain.pem'),
    },

    server: {
        host: 'kaltanimis.com',  // your dev hostname
        port: 5173,
        strictPort: true,
        cors: true,
        https: true,

    },
});


