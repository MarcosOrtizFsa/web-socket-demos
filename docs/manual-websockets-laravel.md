# Manual técnico simple: WebSockets con Laravel 12, Reverb y Echo

## 1. Objetivo

Este manual explica cómo construir una demostración sencilla de comunicación en tiempo real:

- Una página **Emisor** envía un mensaje al servidor.
- Laravel valida el mensaje y genera un evento.
- Laravel Reverb distribuye el evento mediante WebSocket.
- Laravel Echo recibe el evento en el navegador.
- Una página **Receptor** muestra el mensaje sin recargar.

El flujo completo es:

```text
Emisor → Controlador → Acción → Evento → Reverb → Echo → Receptor
```

---

## 2. Conceptos fundamentales

### HTTP

HTTP funciona normalmente mediante petición y respuesta. El navegador realiza una petición y el servidor responde. Cuando el servidor tiene información nueva, no puede enviarla por iniciativa propia usando una petición HTTP tradicional.

### WebSocket

WebSocket mantiene una conexión abierta entre el navegador y el servidor. Gracias a esto, el servidor puede enviar información nueva inmediatamente, sin esperar otra petición ni recargar la página.

### Broadcasting de Laravel

Broadcasting es el sistema de Laravel que convierte determinados eventos PHP en mensajes que pueden enviarse a clientes externos.

### Laravel Reverb

Reverb es el servidor WebSocket oficial de Laravel. Mantiene las conexiones abiertas y distribuye los eventos a los navegadores suscritos.

### Laravel Echo

Echo es la biblioteca JavaScript que se ejecuta en el navegador. Se conecta con Reverb, se suscribe a canales y escucha eventos.

### Pusher JS

Reverb utiliza el protocolo de Pusher. Por eso Echo usa `pusher-js` como cliente de transporte aunque no se utilice el servicio externo de Pusher.

---

## 3. Requisitos previos

Antes de comenzar se necesita:

- PHP 8.2 o superior.
- Composer.
- Node.js y npm.
- Un proyecto Laravel 12 funcional.

Se pueden comprobar las versiones con:

```powershell
php -v
composer --version
node --version
npm.cmd --version
```

En Windows se usa `npm.cmd` si PowerShell no permite ejecutar `npm.ps1`.

---

## 4. Crear el proyecto Laravel 12

Si todavía no existe el proyecto, se crea con:

```powershell
composer create-project laravel/laravel nombre-del-proyecto "^12.0"
cd nombre-del-proyecto
```

Laravel genera el archivo `.env`, la clave de la aplicación y la estructura inicial. Si fuera necesario generar la clave manualmente:

```powershell
php artisan key:generate
```

Para instalar las dependencias frontend iniciales:

```powershell
npm.cmd install
```

---

## 5. Instalar Broadcasting, Reverb y Echo

Laravel 12 incluye un instalador para preparar Broadcasting:

```powershell
php artisan install:broadcasting --reverb
```

Este comando normalmente realiza las siguientes tareas:

1. Instala `laravel/reverb` mediante Composer.
2. Publica `config/broadcasting.php`.
3. Publica `config/reverb.php`.
4. Crea `routes/channels.php`.
5. Crea `resources/js/echo.js`.
6. Agrega variables de Reverb al archivo `.env`.
7. Instala `laravel-echo` y `pusher-js` mediante npm.

Si `echo.js` existe pero Vite informa que no encuentra `laravel-echo` o `pusher-js`, se instalan manualmente:

```powershell
npm.cmd install --save-dev laravel-echo pusher-js
```

No es necesario repetir este comando si los paquetes ya aparecen en `package.json` y `node_modules`.

Para comprobar la instalación PHP:

```powershell
composer show laravel/reverb
```

Para comprobar los paquetes JavaScript:

```powershell
npm.cmd ls laravel-echo pusher-js --depth=0
```

---

## 6. Configurar el entorno

El archivo `.env` debe contener una sola definición de `BROADCAST_CONNECTION` y las credenciales de Reverb:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=identificador-local
REVERB_APP_KEY=clave-local
REVERB_APP_SECRET=secreto-local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Las variables `REVERB_*` son usadas por Laravel y el servidor Reverb. Las variables `VITE_REVERB_*` se exponen al JavaScript del navegador.

Después de modificar `.env`, se limpia la configuración almacenada en caché:

```powershell
php artisan optimize:clear
```

> No se deben publicar las credenciales reales de `.env` en un repositorio. El archivo `.env.example` solo debe contener valores de ejemplo.

---

## 7. Configuración de Broadcasting y Reverb

### `config/broadcasting.php`

Este archivo define cómo Laravel publica los eventos. La conexión predeterminada lee:

```php
'default' => env('BROADCAST_CONNECTION', 'null'),
```

La conexión `reverb` usa `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, host, puerto y esquema.

### `config/reverb.php`

Este archivo configura el servidor WebSocket y las aplicaciones autorizadas a conectarse. Para desarrollo local, Reverb escucha normalmente en el puerto `8080`.

### `routes/channels.php`

Este archivo contiene reglas de autorización para canales privados y de presencia. El ejemplo usa un canal público llamado `mensajes`, por lo que no necesita una regla en este archivo.

---

## 8. Configurar Laravel Echo

El archivo `resources/js/echo.js` crea el cliente Echo:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

`resources/js/bootstrap.js` debe importar este archivo:

```javascript
import './echo';
```

Finalmente, `resources/js/app.js` importa `bootstrap.js`, por lo que Echo queda disponible como `window.Echo`.

---

## 9. Crear el evento que se transmite

El evento se encuentra en:

```text
app/Events/MensajeEnviado.php
```

Sus responsabilidades son:

1. Transportar el texto y la fecha/hora.
2. Indicar el canal público `mensajes`.
3. Definir el alias estable `mensaje.enviado`.
4. Indicar que debe transmitirse inmediatamente.

La estructura principal es:

```php
final class MensajeEnviado implements ShouldBroadcastNow
{
    public function __construct(
        public readonly string $texto,
        public readonly string $fechaHora,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('mensajes')];
    }

    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }
}
```

Las propiedades públicas forman automáticamente los datos del evento que recibirá Echo.

### ¿Por qué `ShouldBroadcastNow`?

`ShouldBroadcastNow` transmite el evento durante la misma petición HTTP. Es conveniente para este ejemplo porque no requiere ejecutar un worker de colas.

En una aplicación con más tráfico conviene cambiarlo por `ShouldBroadcast`. En ese caso, el evento se coloca en una cola y se procesa con:

```powershell
php artisan queue:work
```

---

## 10. Crear el FormRequest

El archivo es:

```text
app/Http/Requests/EnviarMensajeRequest.php
```

El FormRequest mantiene la validación fuera del controlador. En este ejemplo:

- Elimina espacios al principio y al final.
- Exige que `texto` esté presente.
- Exige que sea una cadena.
- Limita el mensaje a 1000 caracteres.
- Devuelve mensajes de error en español.

La regla principal es:

```php
public function rules(): array
{
    return [
        'texto' => ['required', 'string', 'max:1000'],
    ];
}
```

---

## 11. Crear la acción de envío

El archivo es:

```text
app/Actions/EnviarMensaje.php
```

La acción contiene la lógica de aplicación:

1. Recibe el texto validado.
2. Genera la fecha y hora.
3. Construye `MensajeEnviado`.
4. Despacha el evento.
5. Devuelve el evento al controlador.

Separar esta lógica facilita reutilizarla desde otro controlador, un comando Artisan o un proceso automático.

---

## 12. Crear el controlador

El archivo es:

```text
app/Http/Controllers/MensajeController.php
```

El controlador tiene tres métodos:

- `emisor()`: muestra el formulario.
- `receptor()`: muestra la pantalla que escucha.
- `enviar()`: recibe la petición validada y delega el trabajo a la acción.

El controlador no construye ni despacha directamente el evento. Su trabajo es coordinar HTTP, validación, acción y respuesta JSON.

---

## 13. Registrar las rutas

Las rutas están agrupadas en `routes/web.php`:

```php
Route::controller(MensajeController::class)->group(function (): void {
    Route::get('/', 'emisor');
    Route::get('/emisor', 'emisor')->name('emisor');
    Route::get('/receptor', 'receptor')->name('receptor');
    Route::post('/mensajes', 'enviar')->name('mensaje.enviar');
});
```

Se pueden revisar con:

```powershell
php artisan route:list --except-vendor
```

---

## 14. Crear las vistas Blade

Se utiliza un layout compartido:

```text
resources/views/layouts/app.blade.php
```

El layout contiene:

- Metadatos HTML.
- Token CSRF.
- Carga de CSS y JavaScript mediante Vite.
- Navegación entre Emisor y Receptor.

Las dos pantallas son:

```text
resources/views/emisor.blade.php
resources/views/receptor.blade.php
```

El emisor contiene un formulario con un `textarea`. El receptor contiene una lista vacía que JavaScript completa al recibir eventos.

---

## 15. JavaScript del emisor

El archivo es:

```text
resources/js/emisor.js
```

Su funcionamiento es:

1. Detecta el formulario.
2. Cancela la recarga normal con `preventDefault()`.
3. Envía un `POST` con Axios a `/mensajes`.
4. Muestra el resultado del envío.
5. Limpia el campo únicamente si el servidor responde correctamente.
6. Muestra los errores de validación sin recargar.

El formulario incluye `@csrf`, y el layout expone el token CSRF requerido por Laravel.

---

## 16. JavaScript del receptor

El archivo es:

```text
resources/js/receptor.js
```

La suscripción fundamental es:

```javascript
window.Echo.channel('mensajes')
    .listen('.mensaje.enviado', (evento) => {
        // Agregar el mensaje recibido a la lista.
    });
```

Puntos importantes:

- `channel('mensajes')` coincide con `new Channel('mensajes')` del evento PHP.
- `.mensaje.enviado` coincide con `broadcastAs()`.
- El punto inicial es necesario cuando se escucha un alias definido con `broadcastAs()`.
- Se usa `textContent` para mostrar el mensaje sin interpretar posibles etiquetas HTML.

Los módulos `emisor.js` y `receptor.js` son importados desde `resources/js/app.js`.

---

## 17. Estilos

Los estilos se encuentran en:

```text
resources/css/app.css
```

El proyecto ya utiliza Tailwind CSS, por lo que se agregaron clases pequeñas para tarjetas, campos, botones y enlaces. No se instaló ninguna biblioteca visual adicional.

---

## 18. Levantar el sistema

Se necesitan tres terminales abiertas en el directorio del proyecto.

### Terminal 1: servidor Laravel

```powershell
php artisan serve
```

Laravel queda disponible normalmente en:

```text
http://127.0.0.1:8000
```

### Terminal 2: servidor WebSocket Reverb

```powershell
php artisan reverb:start
```

Reverb queda escuchando normalmente en:

```text
localhost:8080
```

### Terminal 3: Vite

```powershell
npm.cmd run dev
```

Vite compila CSS y JavaScript y habilita la recarga automática durante el desarrollo.

No se debe usar el puerto de Vite como URL principal de la aplicación. Las vistas Blade se abren desde Laravel en el puerto `8000`.

### Cola

No se necesita una cuarta terminal porque el evento implementa `ShouldBroadcastNow`.

---

## 19. Probar con dos pestañas

1. Confirmar que Laravel, Reverb y Vite estén ejecutándose.
2. Abrir el receptor:

   ```text
   http://127.0.0.1:8000/receptor
   ```

3. Abrir el emisor en otra pestaña:

   ```text
   http://127.0.0.1:8000/emisor
   ```

4. Escribir un mensaje en el Emisor.
5. Presionar **Enviar**.
6. Comprobar que el campo se limpie.
7. Cambiar a la pestaña Receptor.
8. Confirmar que el mensaje y su fecha/hora aparezcan sin recargar.

---

## 20. Verificaciones técnicas

### Comprobar rutas

```powershell
php artisan route:list --except-vendor
```

### Ejecutar pruebas PHP

```powershell
php artisan test
```

### Comprobar estilo PHP

```powershell
vendor\bin\pint --test
```

### Compilar el frontend para producción

```powershell
npm.cmd run build
```

### Limpiar cachés después de cambiar configuración

```powershell
php artisan optimize:clear
```

---

## 21. Errores frecuentes

### Vite no encuentra `laravel-echo`

Error típico:

```text
Failed to resolve import "laravel-echo"
```

Solución:

```powershell
npm.cmd install --save-dev laravel-echo pusher-js
npm.cmd run dev
```

### El mensaje se envía, pero el receptor no recibe nada

Revisar:

1. Que `php artisan reverb:start` esté ejecutándose.
2. Que `BROADCAST_CONNECTION=reverb` exista una sola vez.
3. Que `REVERB_PORT` y `VITE_REVERB_PORT` correspondan al puerto `8080`.
4. Que el canal PHP y JavaScript se llamen exactamente `mensajes`.
5. Que Echo escuche `.mensaje.enviado`, incluido el punto inicial.
6. La consola del navegador y la terminal de Reverb.

### Error CSRF 419

Comprobar que:

- La petición se realice desde la URL de Laravel, no directamente desde Vite.
- El layout incluya el token CSRF.
- Axios se cargue desde `bootstrap.js`.
- El navegador acepte las cookies de la aplicación.

### Se usa `ShouldBroadcast` y no llegan eventos

`ShouldBroadcast` usa la cola. Hay que iniciar un worker:

```powershell
php artisan queue:work
```

Para esta demostración se usa `ShouldBroadcastNow`, por lo que ese proceso no es necesario.

---

## 22. Cómo explicarlo oralmente

Una explicación breve puede ser:

> El usuario envía un mensaje por HTTP con Axios. Laravel lo valida mediante un FormRequest y el controlador delega el trabajo a una acción. La acción genera un evento que implementa ShouldBroadcastNow. Laravel publica ese evento en el canal público mensajes y Reverb lo distribuye por WebSocket. En la otra pestaña, Laravel Echo está suscrito al mismo canal, recibe el evento mensaje.enviado y actualiza la lista del navegador sin recargar la página.

Los cuatro nombres que deben coincidir son:

| Elemento | Valor |
|---|---|
| Conexión de broadcasting | `reverb` |
| Canal | `mensajes` |
| Alias del evento | `mensaje.enviado` |
| Listener de Echo | `.mensaje.enviado` |

---

## 23. Cómo escalar el ejemplo

### Canales privados

Cambiar `Channel` por `PrivateChannel`, agregar una regla de autorización en `routes/channels.php` y usar `Echo.private(...)`.

### Canales de presencia

Usar `PresenceChannel` y `Echo.join(...)` para conocer qué usuarios están conectados y reaccionar cuando entran o salen.

### Guardar mensajes

Crear una tabla y un modelo `Mensaje`. La acción guardaría primero el registro y luego emitiría el evento. Así los receptores podrían recuperar el historial.

### Procesar con colas

Cambiar `ShouldBroadcastNow` por `ShouldBroadcast` y ejecutar workers. Esto evita que una petición HTTP espere a que termine el broadcasting.

### Producción

En producción se deben configurar TLS, un proxy inverso, procesos supervisados para Reverb y las colas, dominios permitidos y credenciales seguras diferentes para cada entorno.
