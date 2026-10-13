@extends('layouts.app')

@section('title', 'Receptor')

@section('content')
    {{-- Esta pantalla permanece suscrita al canal y agrega cada evento recibido. --}}
    <section class="tarjeta">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-600">Receptor</p>
        <h1 class="mt-1 text-2xl font-bold">Mensajes en tiempo real</h1>
        <p class="mt-2 text-slate-600">
            Dejá esta pestaña abierta y enviá mensajes desde la pantalla emisora.
        </p>

        <p id="estado-receptor" class="mt-4 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-600" aria-live="polite">
            Conectando con el canal mensajes…
        </p>

        <ul id="lista-mensajes" class="mt-5 space-y-3" aria-live="polite">
            {{-- JavaScript inserta aquí elementos seguros usando textContent. --}}
        </ul>
    </section>
@endsection
