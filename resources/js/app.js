import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.PUSHER_APP_KEY,
    wsHost: import.meta.env.PUSHER_HOST,
    wsPort: import.meta.env.PUSHER_PORT,
    forceTLS: import.meta.env.PUSHER_SCHEME === 'wss',
    encrypted: import.meta.env.PUSHER_SCHEME === 'wss',
    cluster: import.meta.env.PUSHER_APP_CLUSTER || 'mt1',
    enabledTransports: ['ws', 'wss'],
});
