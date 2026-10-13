import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb utiliza el protocolo de Pusher, por eso Echo necesita este cliente global.
window.Pusher = Pusher;

// La conexión toma sus valores del entorno para funcionar igual en local y producción.
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
