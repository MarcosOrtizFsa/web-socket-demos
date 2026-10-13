<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SendChatMessage;
use App\Actions\StartDirectConversation;
use App\Http\Requests\SendChatMessageRequest;
use App\Http\Requests\StartConversationRequest;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Coordina la interfaz, creación de conversaciones y envío de mensajes.
 */
final class ChatController extends Controller
{
    /** Muestra la bandeja sin seleccionar una conversación. */
    public function index(Request $request): View
    {
        return $this->renderChat($request);
    }

    /** Abre una conversación si el usuario forma parte de ella. */
    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('view', $conversation);

        return $this->renderChat($request, $conversation);
    }

    /** Encuentra o crea el chat directo y redirige a su pantalla. */
    public function start(
        StartConversationRequest $request,
        StartDirectConversation $startConversation,
    ): RedirectResponse {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $otherUser = User::query()->findOrFail($request->integer('user_id'));
        $conversation = $startConversation->execute($currentUser, $otherUser);

        return redirect()->route('chat.show', $conversation);
    }

    /** Autoriza, persiste y transmite un mensaje; responde JSON a Axios. */
    public function send(
        SendChatMessageRequest $request,
        Conversation $conversation,
        SendChatMessage $sendMessage,
    ): JsonResponse {
        $this->authorize('sendMessage', $conversation);

        /** @var User $currentUser */
        $currentUser = $request->user();
        $message = $sendMessage->execute(
            $conversation,
            $currentUser,
            $request->string('body')->toString(),
        );

        return response()->json([
            'message' => 'Mensaje enviado.',
            'id' => $message->getKey(),
        ], 201);
    }

    /** Construye los datos compartidos por la bandeja y el chat activo. */
    private function renderChat(Request $request, ?Conversation $activeConversation = null): View
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $search = trim((string) $request->query('buscar', ''));

        $conversations = $currentUser->conversations()
            ->with(['participants:id,name,email', 'latestMessage'])
            ->orderByDesc('conversations.updated_at')
            ->get();

        /** @var EloquentCollection<int, User> $searchResults */
        $searchResults = new EloquentCollection;

        if (mb_strlen($search) >= 2) {
            $searchResults = User::query()
                ->whereKeyNot($currentUser->getKey())
                ->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'email']);
        }

        $messages = collect();

        if ($activeConversation !== null) {
            $activeConversation->loadMissing('participants:id,name,email');
            $messages = $activeConversation->messages()
                ->with('user:id,name,email')
                ->latest()
                ->limit(100)
                ->get()
                ->reverse()
                ->values();
        }

        return view('chat.index', compact(
            'activeConversation',
            'conversations',
            'currentUser',
            'messages',
            'search',
            'searchResults',
        ));
    }
}
