/**
 * Activa la suscripción Echo cuando la pantalla receptora está presente.
 */
const listaMensajes = document.querySelector('#lista-mensajes');

if (listaMensajes) {
    const estadoReceptor = document.querySelector('#estado-receptor');

    // El punto inicial obliga a Echo a usar exactamente el alias de broadcastAs().
    window.Echo.channel('mensajes')
        .listen('.mensaje.enviado', (evento) => {
            const elemento = document.createElement('li');
            const texto = document.createElement('p');
            const fechaHora = document.createElement('time');

            // textContent evita interpretar HTML recibido dentro del mensaje.
            elemento.className = 'rounded-lg border border-slate-200 bg-slate-50 p-4';
            texto.className = 'break-words font-medium';
            texto.textContent = evento.texto;
            fechaHora.className = 'mt-1 block text-xs text-slate-500';
            fechaHora.textContent = evento.fechaHora;

            elemento.append(texto, fechaHora);
            listaMensajes.prepend(elemento);

            estadoReceptor.textContent = 'Último mensaje recibido correctamente.';
            estadoReceptor.className = 'mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700';
        });

    // La suscripción ya fue registrada; Reverb completará la conexión WebSocket.
    estadoReceptor.textContent = 'Escuchando el canal público mensajes.';
}
