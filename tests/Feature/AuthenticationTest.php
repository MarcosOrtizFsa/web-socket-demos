<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprueba el acceso mínimo necesario para proteger el chat.
 */
final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Un visitante puede registrarse y entra directamente a su bandeja.
     */
    public function test_a_visitor_can_register_and_enter_the_chat(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('chat.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com']);
    }

    /**
     * Un visitante no autenticado es enviado a la pantalla de acceso.
     */
    public function test_a_guest_cannot_open_the_chat(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('chat.index'))->assertRedirect(route('login'));
    }
}
