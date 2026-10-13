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
    {{-- La navegación común permite alternar rápidamente entre emisor y receptor. --}}
    <header class="border-b border-slate-200 bg-white">
        <nav class="mx-auto flex max-w-3xl items-center justify-between px-4 py-4">
            <a class="font-bold text-slate-900" href="{{ route('emisor') }}">WebSocket Demo</a>
            <div class="flex gap-4">
                <a class="enlace" href="{{ route('emisor') }}">Emisor</a>
                <a class="enlace" href="{{ route('receptor') }}">Receptor</a>
            </div>
        </nav>
    </header>

    {{-- Cada vista aporta únicamente el contenido específico de su responsabilidad. --}}
    <main class="contenedor">
        @yield('content')
    </main>
</body>
</html>
