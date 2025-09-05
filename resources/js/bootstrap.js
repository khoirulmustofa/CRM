// resources/js/bootstrap.js

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// --- Standard Laravel Echo Setup ---
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally if needed by other scripts
window.Pusher = Pusher;

// Create the global Echo instance as expected by Laravel docs
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// --- End Standard Laravel Echo Setup ---