<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Proporciona autorización mediante políticas a todos los controladores.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
