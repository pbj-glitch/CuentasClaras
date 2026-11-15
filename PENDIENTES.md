# Cuentas Claras: qué está hecho y qué falta

## Cómo levantarlo

```bash
composer install
cp .env.example .env        # y poner GROQ_API_KEY
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve           # http://127.0.0.1:8000
php artisan test            # 25 tests, todos deberían pasar
```

## Hecho

**Base (Maria):** arquitectura, entorno, migraciones, rutas base, `GroqService::analizarTexto`.

**Lógica de negocio**
- `app/Services/Liquidacion.php`: reparte cada ítem entre quienes lo consumieron, cobra la propina en proporción y ajusta el redondeo para que todo cuadre al peso. También calcula los saldos y las transferencias mínimas para quedar a mano.
- Modelos completos (`Grupo`, `Miembro`, `Gasto`, `User`…). `Gasto::registrar()` guarda el gasto, sus ítems y las partes de cada uno. `Grupo::incorporar()` une a un usuario y le permite reclamar un integrante sin cuenta. Cada grupo tiene su `codigo_invitacion` (tipo `7K4-M2Q`), que se genera solo.

**Frontend** (Blade + `public/css/app.css`, sin npm)
- Login y registro.
- Mis grupos, crear grupo (con integrantes que aún no tienen la app).
- Grupo: gastos por día, saldo de cada uno.
- Nuevo gasto: carga manual por ítems, marcando quién consumió qué, con propina.
- Saldos: transferencias sugeridas, "Marcar pagado", deshacer y compartir por WhatsApp.
- Invitar: código del grupo, aceptar o rechazar solicitudes, agregar integrantes sin cuenta.
- Unirse con código, eligiendo "soy X" para quedarse con su saldo.

## Falta: APIs e IA

Cada punto tiene un comentario `{{-- Aquí va … --}}` en la vista donde va.

### 1. Leer boletas con IA (Groq)
- `GroqController` está vacío. La idea es que lea una **foto** (modelo de visión `GROQ_VISION_MODEL`) o una **frase** ("pagué 15 lucas de bencina entre todos menos la Caro") y devuelva los ítems.
- Lo más simple es que llene el mismo formulario de `gastos/create.blade.php`. `GastoController::create` le pasa a la vista un arreglo `$items` con la forma `['nombre', 'cantidad', 'precio_unitario', 'miembros' => [ids]]`; si Groq arma ese arreglo, la vista lo muestra listo para revisar y guardar.
- Extra: "asignar con IA" (el usuario escribe "las pizzas para todos, Ana y yo las cervezas" y se marcan las casillas).
- `GroqService::analizarTexto` se cae si Groq responde algo que no es JSON (`json_decode` da `null` y el método debe devolver `array`). Hay que manejar ese error y el de la conexión.
- Usar `Http::fake()` en los tests para no gastar la API (ver `tests/Feature/GroqServiceTest.php`).

### 2. QR para invitar
- `app/Services/QrService.php` está vacío (`bacon/bacon-qr-code` ya está instalado).
- El QR debe llevar al link `route('unirse.show', $grupo->codigo_invitacion)`. Va en `invitar/show.blade.php`.
- Opcional: escanear el QR desde la app en `unirse/index.blade.php` (por ejemplo con `html5-qrcode` desde CDN). Con la cámara normal del celular igual funciona, porque el QR es un link.
- La tabla `invitacions` (token + `expira_en`) todavía no se usa. Sirve si quieren links que venzan; si no, se puede borrar.

### 3. Tiempo real (polling cada 3 s)
- La ruta `grupos.estado` apunta a `GrupoController::estadoJson`, que **no existe todavía**. Debe devolver un JSON con una "versión" que cambie cuando hay un gasto, pago, integrante o solicitud nuevo (por ejemplo, un `md5` de los conteos y fechas).
- `public/js/polling.js` está vacío. El layout ya lo carga en las vistas del grupo, y la URL a consultar está en `data-estado` del `<div class="app">`. Si la versión cambia, se recarga la página o se muestra un aviso.

### 4. Cierre
- Seeder con datos de demo (`DatabaseSeeder` todavía crea solo `test@example.com`).
- README del proyecto (el actual es el que trae Laravel).
