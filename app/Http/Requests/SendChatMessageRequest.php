<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Normaliza y valida cada mensaje textual del chat.
 */
final class SendChatMessageRequest extends FormRequest
{
    /**
     * La política de conversación completa la autorización en el controlador.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Elimina espacios exteriores antes de validar el contenido.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'body' => trim((string) $this->input('body')),
        ]);
    }

    /**
     * Mantiene los mensajes pequeños y compatibles con el transporte WebSocket.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * Presenta errores claros dentro de la interfaz del chat.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'Escribí un mensaje antes de enviarlo.',
            'body.max' => 'El mensaje no puede superar los 2000 caracteres.',
        ];
    }
}
