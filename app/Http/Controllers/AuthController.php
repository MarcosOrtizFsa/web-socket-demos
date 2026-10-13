<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Gestiona registro, inicio y cierre de sesión con el correo como identidad.
 */
final class AuthController extends Controller
{
    /** Muestra el formulario de acceso. */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /** Autentica las credenciales y renueva la sesión contra fijación. */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'El correo o la contraseña no son correctos.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('chat.index'));
    }

    /** Muestra el formulario de creación de cuenta. */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /** Crea la cuenta, autentica al usuario y lo dirige al chat. */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->safe()->only([
            'name',
            'email',
            'password',
        ]));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('chat.index');
    }

    /** Cierra la sesión y renueva el token CSRF antes de volver al acceso. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
