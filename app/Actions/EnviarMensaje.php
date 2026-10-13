<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\MensajeEnviado;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Construye y dispara un mensaje sin acoplar esa lógica al controlador HTTP.
 */
final class EnviarMensaje
{
    /**
     * Recibe el despachador por inyección para facilitar futuras pruebas.
     */
    public function __construct(private readonly Dispatcher $eventos) {}

    /**
     * Genera una única marca temporal y difunde el evento de forma síncrona.
     */
    public function ejecutar(string $texto): MensajeEnviado
    {
        $evento = new MensajeEnviado(
            texto: $texto,
            fechaHora: now()->format('d/m/Y H:i:s'),
        );

        $this->eventos->dispatch($evento);

        return $evento;
    }
}
