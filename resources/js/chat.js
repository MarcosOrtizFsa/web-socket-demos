/**
 * Conecta la conversación activa con su canal privado y gestiona el formulario.
 */
const chat = document.querySelector('#chat-app');

if (chat) {
    const conversationId = chat.dataset.conversationId;
    const currentUserId = Number(chat.dataset.currentUserId);
    const form = document.querySelector('#message-form');
    const input = document.querySelector('#message-body');
    const submit = document.querySelector('#message-submit');
    const feedback = document.querySelector('#message-feedback');
    const messageList = document.querySelector('#message-list');
    const connectionStatus = document.querySelector('#connection-status');

    /**
     * Desplaza el historial para mantener visible el mensaje más reciente.
     */
    const scrollToLatest = () => {
        messageList.scrollTop = messageList.scrollHeight;
    };

    /**
     * Construye nodos seguros; textContent evita ejecutar HTML recibido.
     */
    const appendMessage = (message) => {
        if (document.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        // Quita la ayuda inicial apenas llega el primer mensaje real.
        document.querySelector('#empty-message-state')?.remove();

        const item = document.createElement('li');
        const bubble = document.createElement('div');
        const author = document.createElement('p');
        const body = document.createElement('p');
        const time = document.createElement('time');
        const isOwn = Number(message.sender.id) === currentUserId;

        item.dataset.messageId = message.id;
        item.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;
        bubble.className = `max-w-[80%] rounded-2xl px-4 py-3 shadow-sm ${isOwn ? 'bg-emerald-600 text-white' : 'bg-white text-slate-900'}`;
        author.className = `mb-1 text-xs font-semibold ${isOwn ? 'text-emerald-100' : 'text-emerald-700'}`;
        author.textContent = isOwn ? 'Vos' : message.sender.name;
        body.className = 'whitespace-pre-wrap break-words';
        body.textContent = message.body;
        time.className = `mt-1 block text-right text-[11px] ${isOwn ? 'text-emerald-100' : 'text-slate-400'}`;
        time.dateTime = message.sentAt;
        time.textContent = new Intl.DateTimeFormat('es-AR', {
            hour: '2-digit',
            minute: '2-digit',
        }).format(new Date(message.sentAt));

        bubble.append(author, body, time);
        item.append(bubble);
        messageList.append(item);
        scrollToLatest();
    };

    // Escucha solo el canal privado autorizado para la conversación visible.
    window.Echo.private(`conversacion.${conversationId}`)
        .listen('.mensaje.enviado', appendMessage);

    // Informa de manera simple el estado real de la conexión WebSocket.
    const connection = window.Echo.connector.pusher.connection;
    if (connection.state === 'connected') {
        connectionStatus.textContent = 'En línea';
        connectionStatus.className = 'text-xs font-medium text-emerald-600';
    }
    connection.bind('connected', () => {
        connectionStatus.textContent = 'En línea';
        connectionStatus.className = 'text-xs font-medium text-emerald-600';
    });
    connection.bind('unavailable', () => {
        connectionStatus.textContent = 'Sin conexión';
        connectionStatus.className = 'text-xs font-medium text-red-600';
    });

    // Envía por HTTP; Reverb devuelve el mensaje a todos los clientes, incluido el emisor.
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        submit.disabled = true;
        feedback.textContent = '';

        try {
            await window.axios.post(form.action, { body: input.value });
            form.reset();
            input.focus();
        } catch (error) {
            const errors = error.response?.data?.errors;
            feedback.textContent = errors?.body?.[0] ?? 'No se pudo enviar el mensaje.';
        } finally {
            submit.disabled = false;
        }
    });

    scrollToLatest();
}
