<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\ChatMessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Persiste un mensaje y solo después lo publica en tiempo real.
 */
final class SendChatMessage
{
    /**
     * Inyecta el despachador para que la acción pueda probarse aisladamente.
     */
    public function __construct(private readonly Dispatcher $events) {}

    /**
     * Guarda el mensaje, carga su autor y emite el evento WebSocket.
     */
    public function execute(Conversation $conversation, User $sender, string $body): Message
    {
        $message = $conversation->messages()->create([
            'user_id' => $sender->getKey(),
            'body' => $body,
        ]);

        // Actualiza la conversación para ordenarla por actividad reciente.
        $conversation->touch();
        $message->load('user:id,name,email');
        $this->events->dispatch(new ChatMessageSent($message));

        return $message;
    }
}
