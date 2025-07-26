import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    cluster: 'mt1',
    wsHost: 'kaltanimis.com',
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
});
