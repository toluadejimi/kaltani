import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    wsHost: 'kaltanimis.com',
    wsPort: 6001,
    forceTLS: false,  // important: disable forcing TLS here
    encrypted: false,
    disableStats: true,
    cluster:'mt1',
    enabledTransports: ['ws'], // only ws, no wss
});
