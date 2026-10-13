<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Representa una persona autenticada e identificada públicamente por su correo.
 */
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> Atributos permitidos durante el registro. */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** @var list<string> Datos sensibles que nunca deben serializarse. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Convierte fechas y cifra automáticamente las contraseñas asignadas.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Obtiene las conversaciones en las que participa el usuario.
     *
     * @return BelongsToMany<Conversation, $this>
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)->withTimestamps();
    }

    /**
     * Obtiene los mensajes escritos por el usuario.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
