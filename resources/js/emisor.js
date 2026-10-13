/**
 * Activa el formulario emisor cuando está presente en la página actual.
 */
const formulario = document.querySelector('#formulario-mensaje');

if (formulario) {
    const campoTexto = formulario.querySelector('#texto');
    const botonEnviar = formulario.querySelector('#boton-enviar');
    const estadoEnvio = formulario.querySelector('#estado-envio');

    // Envía el formulario con Axios para conservar la página y el foco del usuario.
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        botonEnviar.disabled = true;
        estadoEnvio.textContent = 'Enviando…';
        estadoEnvio.className = 'text-sm text-slate-600';

        try {
            await window.axios.post(formulario.action, {
                texto: campoTexto.value,
            });

            // Limpia el campo únicamente cuando el servidor confirmó el envío.
            formulario.reset();
            campoTexto.focus();
            estadoEnvio.textContent = 'Mensaje enviado.';
            estadoEnvio.className = 'text-sm text-emerald-700';
        } catch (error) {
            // Prioriza el mensaje de validación y conserva un fallback para otros errores.
            const errores = error.response?.data?.errors;
            estadoEnvio.textContent = errores?.texto?.[0] ?? 'No se pudo enviar el mensaje.';
            estadoEnvio.className = 'text-sm text-red-700';
        } finally {
            botonEnviar.disabled = false;
        }
    });
}
