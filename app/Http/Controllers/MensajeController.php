<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EnviarMensaje;
use App\Http\Requests\EnviarMensajeRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Coordina las pantallas y delega el envío a la acción de dominio.
 */
final class MensajeController extends Controller
{
    /**
     * Muestra el formulario desde el que se emiten mensajes.
     */
    public function emisor(): View
    {
        return view('emisor');
    }

    /**
     * Muestra la pantalla que escucha el canal público en tiempo real.
     */
    public function receptor(): View
    {
        return view('receptor');
    }

    /**
     * Valida la petición, ejecuta la acción y responde al cliente emisor.
     */
    public function enviar(
        EnviarMensajeRequest $request,
        EnviarMensaje $enviarMensaje,
    ): JsonResponse {
        $evento = $enviarMensaje->ejecutar($request->string('texto')->toString());

        return response()->json([
            'mensaje' => 'Mensaje enviado correctamente.',
            'datos' => [
                'texto' => $evento->texto,
                'fechaHora' => $evento->fechaHora,
            ],
        ]);
    }
}
