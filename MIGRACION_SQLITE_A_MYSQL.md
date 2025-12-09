# 🚀 GUÍA DE MIGRACIÓN - SQLite → MySQL + Datos del Equipo

## ✅ CAMBIOS YA REALIZADOS

### 1. Configuración de Base de Datos
- ✅ `config/database.php` - Cambio a MySQL como default
- ✅ `.env.example` - Configuración MySQL completa

### 2. Datos del Equipo
- ✅ Migración: `database/migrations/2025_12_08_000000_create_team_members_table.php`
- ✅ Modelo: `app/Models/TeamMember.php`
- ✅ Seeder: `database/seeders/TeamMemberSeeder.php`
- ✅ Controlador: `app/Http/Controllers/TeamController.php`
- ✅ Página React: `resources/js/Pages/Team/Show.jsx`
- ✅ Rutas: `routes/web.php` (agregadas rutas públicas)

### 3. Testing
- ✅ `phpunit.xml` - Configurado para MySQL en testing

---

## 📋 PASOS A EJECUTAR MANUALMENTE

### PASO 1: Crear Base de Datos MySQL

```bash
# Conectarte a MySQL como root
mysql -u root -p

# Crear la base de datos
CREATE DATABASE plvl_rct CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear base de datos de testing
CREATE DATABASE plvl_rct_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Salir
exit
```

### PASO 2: Actualizar archivo `.env`

Copia `.env.example` a `.env` y asegúrate de tener:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plvl_rct
DB_USERNAME=root
DB_PASSWORD=
```

### PASO 3: Ejecutar Migraciones

```bash
# Generar APP_KEY si no existe
php artisan key:generate

# Ejecutar todas las migraciones
php artisan migrate

# Ejecutar seeds (incluye datos del equipo)
php artisan db:seed
```

### PASO 4: Verificar Datos del Equipo

```bash
# Ver los datos en la consola
php artisan tinker

# Dentro de tinker:
>>> App\Models\TeamMember::all();
```

Deberías ver 4 miembros del equipo:
- Yesica Paola Carrascal Quintero (Instructora líder)
- Diego Armando Quintero Contreras (Desarrollador 1)
- Kevy Duvan Coronel Caballero (Desarrollador 2)
- George Jesus Vera Pallarez (Desarrollador 3)

### PASO 5: Acceder a la página de Equipo

Abre tu navegador y ve a:
```
http://localhost:8000/team
```

Deberías ver tarjetas visuales con todos los miembros del equipo agrupados por rol.

---

## 🧪 PASOS OPCIONALES DE TESTING

### Ejecutar Tests con MySQL

```bash
# Asegúrate de que la base de datos de testing existe
php artisan migrate --database=testing

# Ejecutar tests
php artisan test

# O con phpunit directamente
./vendor/bin/phpunit
```

---

## 📊 ESTRUCTURA DE DATOS DEL EQUIPO

### Tabla: `team_members`

| Campo | Tipo | Ejemplo |
|-------|------|---------|
| `id` | int | 1 |
| `nombre` | string(255) | Yesica Paola Carrascal Quintero |
| `rol` | string(100) | Instructora líder |
| `email` | string(255) | null/email@example.com |
| `celular` | string(20) | 3112395817 |
| `cedula` | string(20) | 1003257664 |
| `descripcion` | text | Instructora líder del proyecto |
| `foto_url` | string(255) | null (opcional para futuro) |
| `orden` | int | 1 (para ordenar en UI) |
| `activo` | boolean | true |
| `created_at` | timestamp | auto |
| `updated_at` | timestamp | auto |

---

## 🔗 RUTAS NUEVAS

### Página Pública
- `GET /team` - Página de equipo con todos los miembros

### APIs
- `GET /api/team` - JSON con todos los miembros activos
- `GET /api/team/{rol}` - JSON filtrado por rol (ej: `/api/team/Desarrollador%201`)

---

## ✨ CARACTERÍSTICAS IMPLEMENTADAS

✅ **Migración SQLite → MySQL**
- Default cambiado a MySQL
- Configuración `.env.example` completa
- `phpunit.xml` actualizado para testing en MySQL

✅ **Datos del Equipo**
- 4 miembros con datos exactos
- Tabla flexible para futuras adiciones
- Página visual con tarjetas por rol
- APIs JSON para consumo frontend

✅ **Sin Romper Nada**
- Migraciones anteriores siguen siendo válidas
- Compatible con Laravel 11+
- Estructura modular y escalable

---

## 🆘 TROUBLESHOOTING

### Error: "SQLSTATE[HY000]: General error: 1 no such table"
**Solución:** Ejecutar migraciones
```bash
php artisan migrate
```

### Error: "Access denied for user 'root'"
**Solución:** Verificar credenciales en `.env` o crear usuario MySQL

### Error: "Unknown database"
**Solución:** Crear la base de datos manualmente (Ver PASO 1)

### Datos del equipo no aparecen
**Solución:** Ejecutar seeders
```bash
php artisan db:seed --class=TeamMemberSeeder
```

---

## 📝 NOTAS IMPORTANTES

1. **Base de Datos de Testing**: Se usa `plvl_rct_test` para tests. Debe crearse manualmente.

2. **Datos Exactos del Equipo**: Los datos en `TeamMemberSeeder.php` son exactamente como se especificaron:
   - Instructora: Yesica Paola Carrascal Quintero, celular 3112395817
   - Dev 1: Diego Armando Quintero Contreras (sin celular/email)
   - Dev 2: Kevy Duvan Coronel Caballero, celular 3004907439, email caballerokevin418@gmail.com
   - Dev 3: George Jesus Vera Pallarez, cédula 1003257664, celular 1003257664

3. **Página de Equipo**: Accesible públicamente en `/team`, sin requerir autenticación.

4. **Escalabilidad**: El sistema está diseñado para agregar más miembros fácilmente a través del admin o migraciones futuras.

---

## 📞 SOPORTE

Si encuentras problemas:
1. Verifica que MySQL esté corriendo
2. Verifica credenciales en `.env`
3. Revisa logs en `storage/logs/laravel.log`
4. Ejecuta `php artisan migrate:fresh --seed` para resetear todo (⚠️ borra datos)
