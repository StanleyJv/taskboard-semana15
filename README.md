# 🔐🌐 TaskBoard — Semana 14

![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-orange?logo=mysql)
![Sanctum](https://img.shields.io/badge/Laravel-Sanctum-red)
![API](https://img.shields.io/badge/API-REST-0B2E59)
![Insomnia](https://img.shields.io/badge/Insomnia-API_Testing-5A36E9?logo=insomnia)
![UPED](https://img.shields.io/badge/UPED-Integración_de_Sistemas-0B2E59)

Proyecto desarrollado para la asignatura **Integración de Sistemas** de la **Universidad Pedagógica de El Salvador (UPED)**.

Durante la **Semana 14** se protegió la API REST de TaskBoard con Laravel Sanctum y luego se realizaron pruebas, depuración y consumo desde distintos clientes.

**Estudiante:** Josthyn Stanley Cruz Vásquez  
**Carrera:** Ingeniería en Sistemas y Computación  
**Universidad:** Universidad Pedagógica de El Salvador  
**Ciclo:** 02-2026  

---

# 🎯 Objetivo general

Convertir la API abierta de Semana 13 en una API protegida mediante tokens personales, aplicar aislamiento por usuario, validar permisos sobre comercios y transacciones, probar distintos escenarios de seguridad y consumir la API desde PHP y JavaScript.

---

# 📅 Jueves — Autenticación de API con Laravel Sanctum

## 🚪 API inicialmente abierta

Antes de Sanctum se comprobó que la API permitía acciones sin autenticación:

```text
GET /api/comercios
GET /api/transacciones
POST /api/transacciones
DELETE /api/transacciones/{id}
```

Esto evidenció que cualquier cliente podía consultar, crear o eliminar información.

---

## 🛡️ Preparación de Sanctum

Se verificó Laravel Sanctum mediante:

```bash
composer show laravel/sanctum
php artisan migrate:status
```

y la existencia de:

```text
personal_access_tokens
```

---

## 👤 HasApiTokens en User

El modelo `User` fue actualizado con:

```php
use Laravel\Sanctum\HasApiTokens;
```

y:

```php
use HasApiTokens, HasFactory, Notifiable;
```

Esto habilita:

```text
createToken()
tokens()
currentAccessToken()
```

---

## 🔑 Login de API

Se creó:

```text
app/Http/Controllers/Api/AuthApiController.php
```

El login valida correo, contraseña y dispositivo.

Una autenticación correcta responde:

```text
201 Created
```

y devuelve un token Bearer.

Una contraseña incorrecta responde:

```text
422 Unprocessable Content
```

---

## 🔒 Rutas protegidas

El login permanece público:

```php
Route::post('/login', [AuthApiController::class, 'login'])
    ->middleware('throttle:5,1');
```

Las demás rutas se protegen con:

```php
Route::middleware('auth:sanctum')->group(function () {
    // rutas protegidas
});
```

Entre ellas:

```text
GET    /api/yo
POST   /api/logout
GET    /api/comercios
GET    /api/comercios/{comercio}
GET    /api/transacciones
POST   /api/transacciones
GET    /api/transacciones/{transaccion}
DELETE /api/transacciones/{transaccion}
GET    /api/tokens
DELETE /api/tokens/{id}
```

---

## 🙋 Endpoint /api/yo

Permite identificar al usuario dueño del token.

Con token válido:

```text
200 OK
```

Sin token:

```text
401 Unauthorized
```

---

## 📡 Accept: application/json

Se comprobó:

```text
Sin token + Accept: application/json → 401
Sin token + sin Accept              → 302
```

---

## 🏪 Aislamiento de comercios

Los comercios se filtran mediante:

```php
$request->user()
    ->comercios()
    ->orderBy('nombre_comercio')
    ->get();
```

El acceso a un comercio ajeno se bloquea con:

```php
abort_unless(
    $comercio->user_id === $request->user()->id,
    404
);
```

Se verificó que Ana solo vea sus comercios y Carlos solo los suyos.

---

## 💳 Protección de transacciones

Las transacciones se filtran por el usuario dueño del comercio:

```php
Transaccion::whereHas(
    'comercio',
    fn ($q) => $q->where(
        'user_id',
        $request->user()->id
    )
)
```

También se agregó autorización para bloquear transacciones ajenas.

---

## ✅ Validación al crear transacciones

`GuardarTransaccionApiRequest` valida que el comercio pertenezca al usuario autenticado.

Si Ana intenta crear una transacción en un comercio de Carlos:

```text
422 Unprocessable Content
```

---

## 🚪 Logout y revocación

El logout elimina el token actual:

```php
$request->user()
    ->currentAccessToken()
    ->delete();
```

Ciclo comprobado:

```text
GET /api/yo      → 200
POST /api/logout → 204
GET /api/yo      → 401
```

---

## ⏳ Expiración de tokens

En `config/sanctum.php`:

```php
'expiration' => 60 * 24,
```

equivalente a 24 horas.

---

## 📱 Sesiones abiertas

Se agregó:

```text
GET /api/tokens
```

para listar sesiones del usuario autenticado sin exponer el token.

También:

```text
DELETE /api/tokens/{id}
```

para cerrar una sesión específica.

---

# 📅 Viernes — Probar, depurar y consumir la API protegida

## 🧪 Colección con dos identidades

La colección de Insomnia se organizó con:

```text
0. Públicas
1. Ana
2. Carlos
```

Se guardaron variables:

```text
{{ tokenAna }}
{{ tokenCarlos }}
```

para evitar pegar tokens manualmente.

---

## ✅ Pruebas automáticas

Se agregaron pruebas automáticas en Insomnia para validar:

- códigos HTTP
- aislamiento de comercios
- ausencia de `user_id`
- respuestas `200`, `401`, `404` y `422`

---

## 🧾 Matriz de pruebas

Se verificaron:

- login de Ana y Carlos
- login inválido
- acceso sin token
- `/api/yo`
- aislamiento de comercios
- creación en comercio ajeno
- eliminación de transacción ajena
- logout y revocación

---

# 🧨 Depuración: errores provocados

## Error A — token sin Bearer

Resultado:

```text
401 Unauthorized
```

Corrección:

```text
Authorization: Bearer {{ tokenAna }}
```

## Error B — falta Accept

Resultado:

```text
302 Found
```

Corrección:

```text
Accept: application/json
```

## Error C — HasApiTokens eliminado

Resultado:

```text
500 Internal Server Error
```

Corrección:

```php
use Laravel\Sanctum\HasApiTokens;
```

y:

```php
use HasApiTokens, HasFactory, Notifiable;
```

## Error D — fuga lógica de comercios

La API siguió respondiendo:

```text
200 OK
```

pero Ana pudo ver comercios ajenos.

La prueba automática detectó el problema.

Corrección:

```php
$request->user()->comercios()
```

---

# 🔍 Inspección de tokens con Tinker

Se utilizaron comandos como:

```php
User::find(1)->tokens()->get(['id', 'name', 'last_used_at']);
```

y:

```php
PersonalAccessToken::findToken('TOKEN');
```

Un token revocado devuelve:

```text
null
```

---

# 🧩 Consumo desde PHP

La API se consumió desde Tinker usando el cliente HTTP de Laravel.

Login:

```php
$login = Http::acceptJson()->post("$base/login", [
    'email' => 'ana@taskboard.test',
    'password' => 'password123',
    'dispositivo' => 'tienda-en-linea',
]);
```

Consulta autenticada:

```php
$r = Http::withToken($token)
    ->acceptJson()
    ->get("$base/transacciones", [
        'estado' => 'Completada'
    ]);
```

Resultados:

```text
Login → 201
Consulta → 200
```

Un token inválido responde:

```text
401 Unauthorized
```

---

# 🌐 Cliente web con fetch()

Se creó:

```text
public/cliente.html
```

El cliente permite:

- iniciar sesión
- obtener token
- consultar comercios
- mostrar datos del usuario
- reaccionar a `401`
- cerrar sesión

Las peticiones protegidas utilizan:

```javascript
'Authorization': 'Bearer ' + token
```

---

## 🏪 Panel del cliente web

El cliente fue ampliado para mostrar la cantidad de transacciones completadas por comercio.

También incluye un botón:

```text
Salir
```

que ejecuta:

```text
POST /api/logout
```

y revoca el token `cliente-web`.

---

# 🔐 Buenas prácticas aplicadas

- No publicar tokens reales.
- No colocar tokens en URLs.
- Usar `Bearer Token`.
- Configurar expiración.
- Revocar tokens al cerrar sesión.
- Usar HTTPS en producción.
- No exponer hashes.
- Filtrar datos por usuario.
- Usar `textContent` en lugar de `innerHTML`.
- Reaccionar correctamente a `401`.

---

# ✅ Códigos HTTP comprobados

| Código | Significado |
|---|---|
| `200` | Petición correcta |
| `201` | Recurso/token creado |
| `204` | Operación correcta sin cuerpo |
| `302` | Redirección |
| `401` | No autenticado |
| `404` | Recurso ajeno/no visible |
| `422` | Validación fallida |
| `500` | Error interno provocado |

---

# 🛠️ Tecnologías utilizadas

- Laravel 12
- Laravel Sanctum
- PHP 8.2
- MySQL / MariaDB
- Eloquent ORM
- REST
- JSON
- Insomnia
- JavaScript
- Fetch API
- Laravel HTTP Client
- Tinker
- Git
- GitHub
- XAMPP

---

# ▶️ Ejecución del proyecto

```bash
git clone https://github.com/StanleyJv/taskboard-semana14.git
cd taskboard-semana14
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Cliente web:

```text
http://127.0.0.1:8000/cliente.html
```

API:

```text
http://127.0.0.1:8000/api
```

---

# 🔗 Repositorio

https://github.com/StanleyJv/taskboard-semana14

---

**Integración de Sistemas · Ciclo 02-2026 · UPED**
