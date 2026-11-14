# 🧪🛡️ TaskBoard — Semana 15

![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-orange?logo=mysql)
![Bruno](https://img.shields.io/badge/Bruno-API_Testing-orange)
![PHPUnit](https://img.shields.io/badge/PHPUnit-Testing-6C4A7E)
![UPED](https://img.shields.io/badge/UPED-Integración_de_Sistemas-0B2E59)

Proyecto desarrollado para la asignatura **Integración de Sistemas** de la **Universidad Pedagógica de El Salvador (UPED)**.

Durante la **Semana 15** se realizaron pruebas de casos límite sobre la API, corrección de errores, manejo uniforme de excepciones, protección de la integridad de los datos con `DB::transaction()` y pruebas de regresión con PHPUnit.

**Estudiante:** Josthyn Stanley Cruz Vásquez  
**Carrera:** Ingeniería en Sistemas y Computación  
**Universidad:** Universidad Pedagógica de El Salvador  
**Ciclo:** 02-2026  

---

# 🎯 Objetivo general

Comprobar el comportamiento de la API ante casos límite, corregir errores detectados y garantizar que las operaciones relacionadas se ejecuten de forma segura y consistente.

---

# 📅 Jueves — Pruebas de casos límite

Se utilizó **Bruno** para ejecutar una colección de 18 pruebas sobre el endpoint de transacciones.

Entre los casos evaluados se incluyeron:

- monto mínimo y máximo
- monto cero y negativo
- montos con más de dos decimales
- comercio inexistente
- nombres demasiado largos
- estado enviado manualmente
- JSON mal formado
- eliminación repetida
- monto enviado como texto

Antes de las correcciones:

```text
Total: 18
Passed: 15
Failed: 3
```

Errores detectados:

```text
E-01 → monto superior al máximo
E-02 → monto con tres decimales
E-03 → estado enviado al crear
```

---

# 🔧 Correcciones aplicadas

Las reglas principales quedaron así:

```php
'monto' => [
    'required',
    'numeric',
    'decimal:0,2',
    'min:0.01',
    'max:99999999.99',
],

'estado' => ['prohibited'],
```

Con estas validaciones la API responde correctamente con `422` cuando los datos no cumplen las reglas.

Después de las correcciones:

```text
Total: 18
Passed: 18
Failed: 0
```

---

# 📅 Viernes — Manejo de errores e integridad de datos

Se configuró `bootstrap/app.php` para evitar mostrar detalles técnicos de la base de datos al cliente.

Ante un error de base de datos la API devuelve un mensaje JSON controlado:

```json
{
    "message": "No se pudo guardar la información. Intenta de nuevo más tarde."
}
```

Los detalles técnicos quedan únicamente en los logs de Laravel.

---

# 🔄 DB::transaction()

Se realizó un experimento para comprobar la diferencia entre guardar datos con y sin una transacción de base de datos.

Sin `DB::transaction()`:

```text
La transacción podía quedar guardada aunque el proceso terminara con error 500.
```

Con `DB::transaction()`:

```text
Si ocurre una excepción, Laravel realiza rollback y evita registros incompletos.
```

La creación final registra tanto la transacción como su evento asociado.

Verificación:

```text
Transacciones: 13
Eventos: 1
```

---

# 🧪 PHPUnit — Extra completado

También se realizaron pruebas automáticas de regresión para comprobar:

- E-01 monto superior al máximo
- E-02 monto con tres decimales
- E-03 estado prohibido
- creación del evento relacionado

Resultado final:

```text
Tests: 6 passed
Assertions: 13
```

---

# ✅ Resultado final

```text
Bruno   → 18 passed / 0 failed
PHPUnit → 6 passed / 13 assertions
```

Se logró:

- corregir E-01, E-02 y E-03
- validar correctamente los casos límite
- devolver errores JSON controlados
- proteger la integridad de los datos
- utilizar rollback con `DB::transaction()`
- agregar pruebas de regresión automatizadas

---

# 🛠️ Tecnologías utilizadas

- Laravel 12
- PHP 8.2
- MySQL / MariaDB
- Eloquent ORM
- REST
- JSON
- Bruno
- PHPUnit
- Tinker
- Git
- GitHub
- XAMPP

---

# ▶️ Ejecución del proyecto

```bash
git clone https://github.com/StanleyJv/taskboard-semana15.git
cd taskboard-semana15
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

API:

```text
http://127.0.0.1:8000/api
```

---

# 🔗 Repositorio

https://github.com/StanleyJv/taskboard-semana15

---

**Integración de Sistemas · Ciclo 02-2026 · UPED**

