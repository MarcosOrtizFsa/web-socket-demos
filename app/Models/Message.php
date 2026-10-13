<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa un mensaje de texto persistido dentro de una conversación.
 */
final class Message extends Model
{
    use HasFactory;

    /**
     * Limita la asignación masiva a los datos propios de un mensaje.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'body',
    ];

    /**
     * Obtiene la conversación a la que pertenece el mensaje.
     *
     * @return BelongsTo<Conversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Obtiene la persona que escribió el mensaje.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
