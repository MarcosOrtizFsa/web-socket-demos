<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida y normaliza los datos enviados desde la pantalla emisora.
 */
final class EnviarMensajeRequest extends FormRequest
{
    /**
     * Este ejemplo público no exige autenticación para enviar mensajes.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Elimina espacios externos antes de validar y difundir el texto.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'texto' => trim((string) $this->input('texto', '')),
        ]);
    }

    /**
     * Limita el payload para mantener acotado el ejemplo WebSocket.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'texto' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Devuelve mensajes de validación claros para la interfaz en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'texto.required' => 'Escribí un mensaje antes de enviarlo.',
            'texto.max' => 'El mensaje no puede superar los 1000 caracteres.',
        ];
    }
}
