<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Representa un mensaje listo para difundirse en tiempo real.
 *
 * Se usa ShouldBroadcastNow para que el ejemplo no dependa de un worker de
 * colas. Los datos públicos forman el payload que recibirá Laravel Echo.
 */
final class MensajeEnviado implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Crea el evento con el contenido mínimo que necesita el receptor.
     */
    public function __construct(
        public readonly string $texto,
        public readonly string $fechaHora,
    ) {}

    /**
     * Indica que el mensaje viaja por el canal público "mensajes".
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('mensajes')];
    }

    /**
     * Expone un nombre estable y desacoplado del nombre PHP de la clase.
     */
    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }
}
