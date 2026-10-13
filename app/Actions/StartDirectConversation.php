<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Encuentra o crea la única conversación directa entre dos usuarios.
 */
final class StartDirectConversation
{
    /**
     * Usa una clave ordenada para impedir conversaciones directas duplicadas.
     */
    public function execute(User $firstUser, User $secondUser): Conversation
    {
        $participantIds = [$firstUser->getKey(), $secondUser->getKey()];
        sort($participantIds, SORT_NUMERIC);
        $directKey = implode(':', $participantIds);

        return DB::transaction(function () use ($directKey, $participantIds): Conversation {
            $conversation = Conversation::query()->firstOrCreate(
                ['direct_key' => $directKey],
                ['type' => 'direct'],
            );

            $conversation->participants()->syncWithoutDetaching($participantIds);

            return $conversation;
        });
    }
}
