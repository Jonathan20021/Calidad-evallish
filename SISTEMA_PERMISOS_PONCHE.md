# Sistema de Permisos para Usuarios de Ponche

## Descripción General

Se ha implementado un sistema completo de gestión de permisos individuales para usuarios provenientes de la base de datos de Ponche.

## Características Implementadas

### 1. **Control de Acceso por Rol**
- **Usuarios AGENT de Ponche**: NO tienen acceso al sistema de calidad (bloqueados en el login)
- **Usuarios NO-AGENT de Ponche** (QA, Supervisor, etc.): Pueden acceder con permisos configurables

### 2. **Gestión de Permisos desde la UI**

Los administradores pueden configurar permisos individuales para cada usuario de Ponche desde la interfaz de usuarios.

#### Permisos Disponibles:

**Gestión de Usuarios:**
- Ver usuarios
- Crear y editar usuarios

**Gestión de Clientes:**
- Ver clientes
- Crear y editar clientes

**Campañas:**
- Ver campañas
- Crear y editar campañas

**Evaluaciones:**
- Ver evaluaciones
- Crear evaluaciones

**Reportes:**
- Ver reportes

**Capacitación:**
- Ver capacitación
- Gestionar capacitación

**Configuración:**
- Gestionar configuración del sistema

### 3. **Interfaz de Usuario**

#### En el Listado de Usuarios
Los usuarios de Ponche (excepto agents) mostrarán un botón **"Permisos"** en la columna de acciones.

#### Pantalla de Configuración de Permisos
- Interfaz intuitiva con checkboxes agrupados por categoría
- Se pueden activar/desactivar permisos según sea necesario
- Los cambios se guardan inmediatamente

## Archivos Creados/Modificados

### Nuevos Archivos:
1. `src/Models/UserPermission.php` - Modelo para gestionar permisos de usuarios
2. `src/Views/users/edit_permissions.php` - Vista para configurar permisos
3. `database/migrate_user_permissions.php` - Script de migración de la tabla
4. `database/schema.sql` - Actualizado con la tabla `user_permissions`

### Archivos Modificados:
1. `src/Helpers/Auth.php` - Lógica de verificación de permisos actualizada
2. `src/Controllers/UserController.php` - Métodos para gestionar permisos
3. `src/Views/users/index.php` - Botón de permisos agregado
4. `src/Router/Router.php` - Soporte para rutas con parámetros
5. `public/index.php` - Rutas de permisos agregadas

## Uso

### Para Configurar Permisos:

1. Ir a **Usuarios** en el menú principal
2. Localizar un usuario de Ponche (identificado en la columna "Origen")
3. Hacer clic en el botón **"Permisos"** (ícono de engranajes morado)
4. Marcar/desmarcar los permisos deseados
5. Hacer clic en **"Guardar Permisos"**

### Rutas:
- **GET** `/users/permissions/{id}` - Ver/editar permisos de un usuario
- **POST** `/users/permissions/{id}` - Actualizar permisos de un usuario

## Notas Técnicas

### Base de Datos
La tabla `user_permissions` almacena los permisos individuales de cada usuario:
- Clave foránea a `users(id)` con DELETE CASCADE
- Índice único por `user_id` (un solo registro de permisos por usuario)
- 12 campos de permisos tipo TINYINT(1) para cada permiso específico

### Lógica de Permisos
1. **Admins**: Tienen todos los permisos (sin cambios)
2. **Usuarios de Ponche (NO-AGENT)**: Se verifica la tabla `user_permissions`
3. **Usuarios QA de Calidad**: Usan la configuración global `qa_permissions`
4. **Otros roles**: Sin permisos especiales

### Bloqueo de Agents
Los usuarios con rol "agent" de Ponche son bloqueados en el login con el mensaje:
> "Acceso no permitido para agentes en el sistema de calidad."

## Migración

La tabla `user_permissions` ya ha sido creada. Si necesitas recrearla:

```bash
php database/migrate_user_permissions.php
```

## Ejemplo de Flujo de Trabajo

1. Un usuario QA de Ponche inicia sesión en el sistema de calidad
2. Por defecto, NO tiene ningún permiso
3. El administrador va a Usuarios → Permisos
4. Activa los permisos necesarios (ejemplo: ver evaluaciones, crear evaluaciones)
5. El usuario QA ahora puede acceder solo a las secciones autorizadas

## Seguridad

- Solo usuarios con permiso `users.create` pueden configurar permisos
- Los permisos se verifican en cada petición a través de `Auth::hasPermission()`
- Los usuarios sin permisos configurados no pueden acceder a ninguna funcionalidad
- Los agents de Ponche no pueden hacer login
