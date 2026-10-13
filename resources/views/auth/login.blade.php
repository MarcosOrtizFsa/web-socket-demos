@extends('layouts.app')

@section('title', 'Ingresar')

@section('content')
    {{-- El acceso usa correo y contraseña, sin exponer información sobre cuentas existentes. --}}
    <section class="tarjeta mx-auto max-w-md">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600">CorreoChat</p>
        <h1 class="mt-1 text-2xl font-bold">Ingresar</h1>
        <p class="mt-2 text-slate-600">Accedé con tu correo para continuar conversando.</p>

        <form class="mt-6 space-y-4" action="{{ route('login.store') }}" method="post">
            @csrf

            <div>
                <label class="mb-1 block font-medium" for="email">Correo</label>
                <input id="email" class="campo" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block font-medium" for="password">Contraseña</label>
                <input id="password" class="campo" name="password" type="password" required autocomplete="current-password">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input class="rounded border-slate-300" name="remember" type="checkbox" value="1">
                Recordarme
            </label>

            <button class="boton w-full" type="submit">Ingresar</button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-600">
            ¿Todavía no tenés cuenta?
            <a class="enlace" href="{{ route('register') }}">Registrate</a>
        </p>
    </section>
@endsection
