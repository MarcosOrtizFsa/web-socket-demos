<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida la persona seleccionada al iniciar una conversación privada.
 */
final class StartConversationRequest extends FormRequest
{
    /**
     * Solo una persona autenticada puede iniciar conversaciones.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Asegura que el destinatario exista y no sea el usuario actual.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::notIn([$this->user()?->getKey()]),
            ],
        ];
    }
}
