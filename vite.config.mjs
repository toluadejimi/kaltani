export default defineConfig({
    plugins: [laravel({
        input: ['resources/js/app.js'],
        refresh: true,
    })],
    server: {
        host: '0.0.0.0',
        port: 5173,
    },
});
