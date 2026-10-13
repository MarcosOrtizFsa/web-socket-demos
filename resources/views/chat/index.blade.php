@extends('layouts.app')

@section('title', 'Chat')
@section('main-class', 'mx-auto h-[calc(100vh-57px)] max-w-7xl p-0 sm:p-4')

@section('content')
    {{-- La cuadrícula mantiene juntas la agenda y la conversación activa. --}}
    <section class="grid h-full overflow-hidden bg-white shadow-sm sm:rounded-xl sm:ring-1 sm:ring-slate-200 md:grid-cols-[320px_1fr]">
        <aside class="flex min-h-0 flex-col border-r border-slate-200 {{ $activeConversation ? 'hidden md:flex' : 'flex' }}">
            {{-- El buscador consulta por nombre o correo sin exponer usuarios anónimamente. --}}
            <div class="border-b border-slate-200 p-4">
                <h1 class="text-xl font-bold">Conversaciones</h1>
                <form class="mt-3" action="{{ route('chat.index') }}" method="get">
                    <label class="sr-only" for="buscar">Buscar personas</label>
                    <input
                        id="buscar"
                        class="campo"
                        name="buscar"
                        type="search"
                        value="{{ $search }}"
                        minlength="2"
                        placeholder="Nombre o correo…"
                    >
                </form>

                @if ($search !== '')
                    <div class="mt-3 space-y-2">
                        @forelse ($searchResults as $person)
                            <form action="{{ route('chat.start') }}" method="post">
                                @csrf
                                <input name="user_id" type="hidden" value="{{ $person->id }}">
                                <button class="w-full rounded-lg border border-slate-200 p-3 text-left hover:bg-emerald-50" type="submit">
                                    <span class="block font-semibold">{{ $person->name }}</span>
                                    <span class="block truncate text-sm text-slate-500">{{ $person->email }}</span>
                                </button>
                            </form>
                        @empty
                            <p class="rounded-lg bg-slate-100 p-3 text-sm text-slate-600">No encontramos otra persona con ese dato.</p>
                        @endforelse
                    </div>
                @endif

                @error('user_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- La lista se ordena según la última actividad de cada conversación. --}}
            <div class="min-h-0 flex-1 overflow-y-auto">
                @forelse ($conversations as $conversation)
                    @php
                        $otherParticipant = $conversation->participants->first(
                            fn ($participant) => $participant->id !== $currentUser->id
                        );
                        $isActive = $activeConversation?->id === $conversation->id;
                    @endphp

                    <a
                        class="block border-b border-slate-100 p-4 transition {{ $isActive ? 'bg-emerald-50' : 'hover:bg-slate-50' }}"
                        href="{{ route('chat.show', $conversation) }}"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate font-semibold">{{ $otherParticipant?->name ?? 'Conversación' }}</span>
                            @if ($conversation->latestMessage)
                                <time class="shrink-0 text-xs text-slate-400">{{ $conversation->latestMessage->created_at->format('H:i') }}</time>
                            @endif
                        </div>
                        <p class="truncate text-sm text-slate-500">
                            {{ $conversation->latestMessage?->body ?? $otherParticipant?->email ?? 'Sin mensajes' }}
                        </p>
                    </a>
                @empty
                    <p class="p-5 text-center text-sm text-slate-500">Buscá una persona por correo para iniciar tu primer chat.</p>
                @endforelse
            </div>
        </aside>

        @if ($activeConversation)
            @php
                $otherParticipant = $activeConversation->participants->first(
                    fn ($participant) => $participant->id !== $currentUser->id
                );
            @endphp

            {{-- Los atributos data entregan al módulo JS solo identificadores y URLs necesarios. --}}
            <div
                id="chat-app"
                class="flex min-h-0 flex-col"
                data-conversation-id="{{ $activeConversation->id }}"
                data-current-user-id="{{ $currentUser->id }}"
            >
                <header class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <a class="text-xl text-slate-500 md:hidden" href="{{ route('chat.index') }}" aria-label="Volver">←</a>
                        <div class="min-w-0">
                            <h2 class="truncate font-bold">{{ $otherParticipant?->name ?? 'Conversación' }}</h2>
                            <p class="truncate text-sm text-slate-500">{{ $otherParticipant?->email }}</p>
                        </div>
                    </div>
                    <span id="connection-status" class="text-xs font-medium text-amber-600">Conectando…</span>
                </header>

                {{-- El historial inicial viene de la base; los siguientes mensajes llegan por Echo. --}}
                <ul id="message-list" class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-100 p-4" aria-live="polite">
                    @forelse ($messages as $chatMessage)
                        @php($isOwn = $chatMessage->user_id === $currentUser->id)
                        <li class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $chatMessage->id }}">
                            <div class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm {{ $isOwn ? 'bg-emerald-600 text-white' : 'bg-white text-slate-900' }}">
                                <p class="mb-1 text-xs font-semibold {{ $isOwn ? 'text-emerald-100' : 'text-emerald-700' }}">
                                    {{ $isOwn ? 'Vos' : $chatMessage->user->name }}
                                </p>
                                <p class="whitespace-pre-wrap break-words">{{ $chatMessage->body }}</p>
                                <time class="mt-1 block text-right text-[11px] {{ $isOwn ? 'text-emerald-100' : 'text-slate-400' }}" datetime="{{ $chatMessage->created_at->toIso8601String() }}">
                                    {{ $chatMessage->created_at->format('H:i') }}
                                </time>
                            </div>
                        </li>
                    @empty
                        <li id="empty-message-state" class="py-8 text-center text-sm text-slate-500">Todavía no hay mensajes. Iniciá la conversación.</li>
                    @endforelse
                </ul>

                {{-- Axios envía el texto por HTTP; el evento WebSocket actualiza ambos navegadores. --}}
                <form id="message-form" class="border-t border-slate-200 bg-white p-3" action="{{ route('chat.send', $activeConversation) }}" method="post">
                    @csrf
                    <div class="flex items-end gap-2">
                        <label class="sr-only" for="message-body">Mensaje</label>
                        <textarea
                            id="message-body"
                            class="campo max-h-32 min-h-11 resize-none"
                            name="body"
                            maxlength="2000"
                            rows="1"
                            required
                            autofocus
                            placeholder="Escribí un mensaje…"
                        ></textarea>
                        <button id="message-submit" class="boton shrink-0" type="submit">Enviar</button>
                    </div>
                    <p id="message-feedback" class="mt-1 text-sm text-red-600" aria-live="polite"></p>
                </form>
            </div>
        @else
            {{-- El estado vacío guía al usuario sin seleccionar una conversación arbitrariamente. --}}
            <div class="hidden items-center justify-center bg-slate-100 p-8 text-center md:flex">
                <div>
                    <div class="text-5xl">💬</div>
                    <h2 class="mt-4 text-2xl font-bold">Elegí una conversación</h2>
                    <p class="mt-2 max-w-sm text-slate-600">Seleccioná un chat existente o buscá a alguien por su correo.</p>
                </div>
            </div>
        @endif
    </section>
@endsection
