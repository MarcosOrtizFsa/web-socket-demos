<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/* Solo los participantes pueden autorizar la conexión al canal privado. */
Broadcast::channel('conversacion.{conversationId}', function (User $user, int $conversationId): bool {
    return Conversation::query()
        ->whereKey($conversationId)
        ->whereHas('participants', fn ($query) => $query->whereKey($user->getKey()))
        ->exists();
});
