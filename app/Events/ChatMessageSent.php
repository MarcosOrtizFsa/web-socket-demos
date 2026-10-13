<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Publica un mensaje persistido únicamente para sus participantes autorizados.
 */
final class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Conserva el modelo necesario para construir canal y payload.
     */
    public function __construct(public readonly Message $message) {}

    /**
     * Aísla cada conversación dentro de su propio canal privado.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversacion.{$this->message->conversation_id}")];
    }

    /**
     * Mantiene un nombre de evento estable para el frontend.
     */
    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }

    /**
     * Expone solamente datos seguros y necesarios para dibujar el mensaje.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->getKey(),
            'conversationId' => $this->message->conversation_id,
            'body' => $this->message->body,
            'sentAt' => $this->message->created_at?->toIso8601String(),
            'sender' => [
                'id' => $this->message->user->getKey(),
                'name' => $this->message->user->name,
                'email' => $this->message->user->email,
            ],
        ];
    }
}
