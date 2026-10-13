<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Representa un espacio privado compartido por dos o más participantes.
 */
final class Conversation extends Model
{
    use HasFactory;

    /**
     * Permite crear conversaciones directas desde la acción de aplicación.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'direct_key',
    ];

    /**
     * Obtiene las personas autorizadas a consultar y escuchar la conversación.
     *
     * @return BelongsToMany<User, $this>
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Obtiene todos los mensajes ordenables de la conversación.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Expone el mensaje más reciente para construir la lista lateral.
     *
     * @return HasOne<Message, $this>
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Indica si una persona forma parte de la conversación.
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participants()->whereKey($user->getKey())->exists();
    }
}
