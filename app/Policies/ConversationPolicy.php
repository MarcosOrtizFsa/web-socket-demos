<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

/**
 * Centraliza la regla de acceso compartida por HTTP y Broadcasting.
 */
final class ConversationPolicy
{
    /**
     * Solo un participante puede abrir una conversación.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($user);
    }

    /**
     * Solo un participante puede enviar mensajes a la conversación.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $conversation->hasParticipant($user);
    }
}
