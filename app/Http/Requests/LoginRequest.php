<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida las credenciales enviadas al iniciar sesión.
 */
final class LoginRequest extends FormRequest
{
    /**
     * Cualquier visitante puede intentar autenticarse.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza el correo para que coincida con el formato del registro.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * Exige ambos datos antes de consultar el sistema de autenticación.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
