# Cuentas Claras

Aplicación web en **Laravel 13 (PHP 8.3+)** para dividir gastos grupales al peso chileno (CLP), con reparto ítem por ítem, cálculo proporcional de propina, lectura de boletas y gastos con **Inteligencia Artificial (Groq)**, invitación por **código QR** y actualización en **tiempo real**.

---

## ¿Qué hace la aplicación?

1. **Grupos e integrantes sin cuenta**: Crea un grupo (ej. *"Viaje a Pucón"*) y agrega de inmediato a todos los amigos, aunque todavía no se hayan registrado en la app.
2. **Carga de gastos manual o con IA (Groq)**:
   - **Con IA**: Escribe una frase natural (*"pagué 15 lucas de bencina entre todos"*) o sube la **foto de una boleta** y la IA rellenará automáticamente el comercio, quién pagó, la propina, los ítems, precios y quién consumió cada cosa.
   - **Manual**: Agrega ítems, cantidades, precios unitarios, atajos de propina (0%, 10%, 15%) y marca quiénes participaron en cada producto.
3. **Reparto exacto al peso (`Liquidacion`)**:
   - Cada ítem se divide entre quienes lo consumieron.
   - La propina se cobra en proporción a lo que consumió cada persona.
   - El redondeo se ajusta automáticamente para que la suma de las partes cuadre exactamente con el total al peso chileno.
4. **Saldos y transferencias mínimas**:
   - Muestra el balance de cada integrante (quién está a favor y quién debe).
   - Calcula el menor número de transferencias posibles para saldar todas las deudas del grupo, permite **marcar como pagado** (o deshacer) y compartir el cobro directo por **WhatsApp**.
5. **Invitación con QR y reclamo de identidad**:
   - Cada grupo genera un código único (ej. `PUC-206`) y un **código QR** en SVG (`BaconQrCode`).
   - Al entrar con el link/QR o escribir el código en **Unirse**, el usuario puede elegir *"Soy Caro"* (un integrante sin cuenta creado antes) para quedarse con su historial y saldo.
6. **Tiempo real (Polling)**:
   - Las vistas del grupo consultan cambios cada 3 segundos (`/grupos/{grupo}/estado`) y avisan automáticamente cuando alguien agrega un gasto, registra un pago o envía una solicitud.

---

## Requisitos previos

- **PHP 8.3 o superior** (con extensiones `sqlite3`, `pdo_sqlite`, `mbstring`, `openssl`, `curl`).
- **Composer**.
- *(Recomendado en Windows)*: Instalar **[Laravel Herd para Windows](https://herd.laravel.com/windows)**, que incluye PHP y Composer listos para usar.
- **No requiere Node.js ni npm** (el frontend usa Blade + CSS/JS nativos).

---

## Instalación paso a paso

### En Windows (CMD)

```cmd
:: 1. Instalar dependencias de PHP
composer install

:: 2. Copiar archivo de configuración y generar clave de la app
copy .env.example .env
php artisan key:generate

:: 3. Crear base de datos SQLite y cargar tablas + datos de demostración
type nul > database\database.sqlite
php artisan migrate:fresh --seed

:: 4. Levantar el servidor local
php artisan serve
```

### En macOS / Linux / Git Bash

```bash
# 1. Instalar dependencias de PHP
composer install

# 2. Copiar archivo de configuración y generar clave de la app
cp .env.example .env
php artisan key:generate

# 3. Crear base de datos SQLite y cargar tablas + datos de demostración
touch database/database.sqlite
php artisan migrate:fresh --seed

# 4. Levantar el servidor local
php artisan serve
```

Una vez iniciado el servidor, abre en tu navegador: **http://127.0.0.1:8000**

---

## Configurar la Inteligencia Artificial (Groq)

Para usar el botón **"Analizar con IA"** (lectura de texto o fotos de boletas) al crear un gasto:

1. Obtén una API Key gratuita en [console.groq.com](https://console.groq.com).
2. Abre el archivo `.env` en la raíz del proyecto y agrega tu clave en:
   ```env
   GROQ_API_KEY=gsk_tu_clave_aqui
   GROQ_VISION_MODEL=meta-llama/llama-4-scout-17b-16e-instruct
   GROQ_TEXT_MODEL=openai/gpt-oss-120b
   ```

---

## Usuarios de prueba (cargados con `--seed`)

Al ejecutar `php artisan migrate:fresh --seed`, puedes iniciar sesión inmediatamente con cualquiera de estas cuentas:

| Nombre | Correo electrónico | Contraseña | Rol en la demo |
| :--- | :--- | :--- | :--- |
| **Vicente** | `Vicente@cuentasclaras.cl` | `contraseña` | Creador de *Viaje a Pucón* y miembro de *Asado del viernes* |
| **Maria** | `Maria@cuentasclaras.cl` | `contraseña` | Creadora de *Asado del viernes* y miembro de *Viaje a Pucón* |
| **Matias** | `Matias@cuentasclaras.cl` | `contraseña` | Tiene una solicitud pendiente para unirse a *Viaje a Pucón* reclamando ser "Benja" |

### Códigos de grupos de prueba:
- **`PUC-206`**: Grupo *"Viaje a Pucón"* (incluye gastos con propina, un pago registrado, integrantes sin cuenta `Caro` y `Benja`, y una solicitud pendiente de aprobación en la pestaña **Invitar**).
- **`ASA-999`**: Grupo *"Asado del viernes"*.

---

## Ejecutar las pruebas automatizadas

El proyecto cuenta con **27 tests** (unitarios y de integración) que verifican el reparto de gastos, redondeo al peso, saldos, transferencias mínimas, permisos de grupos y llamadas a Groq simuladas con `Http::fake()`:

```bash
php artisan test
```
