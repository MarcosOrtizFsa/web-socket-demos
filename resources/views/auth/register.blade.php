@extends('layouts.app')

@section('title', 'Crear cuenta')

@section('content')
    {{-- El registro crea la sesión inmediatamente y dirige al usuario al chat. --}}
    <section class="tarjeta mx-auto max-w-md">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600">CorreoChat</p>
        <h1 class="mt-1 text-2xl font-bold">Crear cuenta</h1>
        <p class="mt-2 text-slate-600">Tu correo será la identidad con la que podrán encontrarte.</p>

        <form class="mt-6 space-y-4" action="{{ route('register.store') }}" method="post">
            @csrf

            <div>
                <label class="mb-1 block font-medium" for="name">Nombre</label>
                <input id="name" class="campo" name="name" type="text" value="{{ old('name') }}" maxlength="100" required autofocus autocomplete="name">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block font-medium" for="email">Correo</label>
                <input id="email" class="campo" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block font-medium" for="password">Contraseña</label>
                <input id="password" class="campo" name="password" type="password" minlength="8" required autocomplete="new-password">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block font-medium" for="password_confirmation">Repetir contraseña</label>
                <input id="password_confirmation" class="campo" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
            </div>

            <button class="boton w-full" type="submit">Registrarme y entrar</button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            ¿Ya tenés cuenta?
            <a class="enlace" href="{{ route('login') }}">Ingresá</a>
        </p>
    </section>
@endsection
