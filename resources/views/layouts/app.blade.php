<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- El token permite que Axios realice peticiones POST protegidas por CSRF. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') · {{ config('app.name') }}</title>

    {{-- Vite carga los estilos, Axios, Echo y los módulos de cada pantalla. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- La navegación adapta sus acciones al estado de autenticación. --}}
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <a class="font-bold text-emerald-700" href="{{ auth()->check() ? route('chat.index') : route('login') }}">CorreoChat</a>

            @auth
                <div class="flex items-center gap-4">
                    <span class="hidden text-sm text-slate-600 sm:inline">{{ auth()->user()->email }}</span>
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button class="text-sm font-medium text-slate-600 hover:text-red-600" type="submit">Salir</button>
                    </form>
                </div>
            @endauth
        </nav>
    </header>

    {{-- Cada vista aporta únicamente el contenido específico de su responsabilidad. --}}
    <main class="@yield('main-class', 'contenedor')">
        @yield('content')
    </main>
</body>
</html>
