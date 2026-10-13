<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\ChatMessageSent;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Verifica creación, autorización, persistencia y emisión del chat.
 */
final class ChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Dos personas obtienen una única conversación directa reutilizable.
     */
    public function test_a_user_can_start_a_direct_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $firstResponse = $this->actingAs($sender)->post(route('chat.start'), [
            'user_id' => $recipient->getKey(),
        ]);
        $secondResponse = $this->actingAs($sender)->post(route('chat.start'), [
            'user_id' => $recipient->getKey(),
        ]);

        $conversation = Conversation::query()->sole();

        $firstResponse->assertRedirect(route('chat.show', $conversation));
        $secondResponse->assertRedirect(route('chat.show', $conversation));
        $this->assertCount(2, $conversation->participants);
        $this->assertDatabaseCount('conversations', 1);
        $this->actingAs($sender)
            ->get(route('chat.show', $conversation))
            ->assertOk()
            ->assertSee($recipient->email);
    }

    /**
     * Un participante puede guardar y emitir un mensaje validado.
     */
    public function test_a_participant_can_send_a_message(): void
    {
        Event::fake([ChatMessageSent::class]);

        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = Conversation::query()->create([
            'type' => 'direct',
            'direct_key' => "{$sender->id}:{$recipient->id}",
        ]);
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $response = $this->actingAs($sender)->postJson(route('chat.send', $conversation), [
            'body' => 'Hola desde la prueba',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'body' => 'Hola desde la prueba',
        ]);
        Event::assertDispatched(ChatMessageSent::class);
    }

    /**
     * Una persona ajena no puede abrir ni enviar a una conversación privada.
     */
    public function test_an_outsider_cannot_access_a_conversation(): void
    {
        $participant = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = Conversation::query()->create([
            'type' => 'direct',
            'direct_key' => '1:2',
        ]);
        $conversation->participants()->attach($participant);

        $this->actingAs($outsider)
            ->get(route('chat.show', $conversation))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->postJson(route('chat.send', $conversation), ['body' => 'No autorizado'])
            ->assertForbidden();
    }

    /**
     * Broadcasting autoriza al participante y rechaza a una persona ajena.
     */
    public function test_the_private_channel_only_authorizes_participants(): void
    {
        $participant = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = Conversation::query()->create([
            'type' => 'direct',
            'direct_key' => '1:2',
        ]);
        $conversation->participants()->attach($participant);
        $payload = [
            'channel_name' => "private-conversacion.{$conversation->id}",
            'socket_id' => '1234.5678',
        ];

        $this->actingAs($participant)
            ->post('/broadcasting/auth', $payload)
            ->assertOk();

        $this->actingAs($outsider)
            ->post('/broadcasting/auth', $payload)
            ->assertForbidden();
    }
}
