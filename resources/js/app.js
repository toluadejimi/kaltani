import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'local',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
    wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    forceTLS: false,
    encrypted: false,
    disableStats: true,
});

window.Echo.channel('chat')
    .listen('.MessageSent', (e) => {
        const messagesDiv = document.getElementById('messages');
        if (!messagesDiv) return;

        // Clear "Waiting for messages..." if it's the first message
        if (messagesDiv.innerText.trim() === 'Waiting for messages...') {
            messagesDiv.innerText = '';
        }

        // Format time
        const time = new Date().toLocaleTimeString();

        // Create new message line
        const line = `[${time}] Message received: ${e.message}\n`;

        // Append the new message
        messagesDiv.innerText += line;

        // Scroll to bottom
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    });

