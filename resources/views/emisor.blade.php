@extends('layouts.app')

@section('title', 'Emisor')

@section('content')
    {{-- El formulario declara el endpoint para que el módulo JS no acople URLs. --}}
    <section class="tarjeta">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Emisor</p>
        <h1 class="mt-1 text-2xl font-bold">Enviar un mensaje</h1>
        <p class="mt-2 text-slate-600">El receptor lo verá en tiempo real mediante Reverb.</p>

        <form id="formulario-mensaje" class="mt-6 space-y-4" action="{{ route('mensaje.enviar') }}" method="post">
            @csrf

            <div>
                <label class="mb-1 block font-medium" for="texto">Mensaje</label>
                <textarea
                    id="texto"
                    class="campo min-h-28 resize-y"
                    name="texto"
                    maxlength="1000"
                    required
                    autofocus
                    placeholder="Escribí algo para la otra pestaña..."
                ></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button id="boton-enviar" class="boton" type="submit">Enviar</button>
                <p id="estado-envio" class="text-sm text-slate-600" aria-live="polite"></p>
            </div>
        </form>
    </section>
@endsection
