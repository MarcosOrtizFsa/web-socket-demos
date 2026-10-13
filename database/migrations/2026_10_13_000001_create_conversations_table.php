<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea las conversaciones y su relación flexible con los participantes.
 */
return new class extends Migration
{
    /**
     * Construye las tablas necesarias para conversaciones directas y futuros grupos.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('direct');
            $table->string('direct_key')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('conversation_user', function (Blueprint $table): void {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['conversation_id', 'user_id']);
            $table->index(['user_id', 'conversation_id']);
        });
    }

    /**
     * Elimina primero la tabla dependiente para respetar las claves foráneas.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_user');
        Schema::dropIfExists('conversations');
    }
};
