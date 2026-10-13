<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persiste el historial textual de cada conversación.
 */
return new class extends Migration
{
    /**
     * Crea mensajes vinculados al remitente y a su conversación.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Elimina la tabla de mensajes al revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
