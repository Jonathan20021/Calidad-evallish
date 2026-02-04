# 🚨 Solución: Error "GEMINI_API_KEY no configurada" en Producción

## Problema
Después de subir los cambios a producción, aparece el error:
```
No se pudo generar el analisis IA: GEMINI_API_KEY no configurada.
```

## Causa
El archivo `.env` no existe en el servidor de producción porque está en `.gitignore` y no se sube a GitHub.

## ✅ Solución

### Paso 1: Conectarse al servidor de producción
Usa SSH o el administrador de archivos de tu hosting (cPanel, etc.)

### Paso 2: Crear el archivo .env en producción
Navega a la raíz de tu proyecto (donde está `composer.json`) y crea el archivo `.env`:

```bash
# Si tienes acceso SSH:
cd /ruta/a/tu/proyecto/Calidad-evallish
nano .env
```

O usa el editor de archivos de tu hosting.

### Paso 3: Copiar el contenido del .env
Copia el siguiente contenido en el archivo `.env` (con TUS valores reales):

```env
# Configuración de Base de Datos
DB_HOST=192.185.46.27
DB_NAME=hhempeos_calidad
DB_USER=hhempeos_calidad
DB_PASS=Evallish.2026

# Configuración de Base de Datos Ponche
PONCHE_DB_HOST=192.185.46.27
PONCHE_DB_NAME=hhempeos_ponche
PONCHE_DB_USER=hhempeos_ponche
PONCHE_DB_PASS=Hugo##2025#

# Configuración de Gemini AI
GEMINI_API_KEY=AIzaSyAMUJNRxyD1QbwjpvkkJdrYSgv40YnPMMA
GEMINI_MODEL=gemini-3-flash-preview
GEMINI_CONNECT_TIMEOUT=10
GEMINI_TIMEOUT=60

# Configuración de la Aplicación
APP_NAME=Evallish BPO
BASE_URL=/
TIMEZONE=America/Santo_Domingo
```

### Paso 4: Verificar permisos del archivo
El archivo `.env` debe ser legible por el servidor web:

```bash
# En Linux:
chmod 640 .env
chown usuario:www-data .env
```

### Paso 5: Verificar la configuración
Accede a través del navegador o línea de comandos:

**Opción A - Navegador:**
```
https://tu-dominio.com/verify_production.php
```

**Opción B - SSH:**
```bash
php public/verify_production.php
```

Deberías ver:
- ✓ ENCONTRADO: .env
- ✓ GEMINI_API_KEY: AIzaSyAMUJNRxyD1Qbwj...
- ✓ GeminiService inicializado correctamente

### Paso 6: Probar la aplicación
Accede a la aplicación y prueba generar un análisis de IA. Debería funcionar correctamente.

## 🔒 Seguridad Importante

1. **NUNCA** subas el archivo `.env` a GitHub
2. El `.gitignore` ya está configurado para protegerlo
3. Cada servidor (local, staging, producción) debe tener su propio `.env`
4. Guarda una copia segura de tus credenciales en un gestor de contraseñas

## 📋 Estructura de archivos requerida

```
/ruta/proyecto/
├── .env                    ← DEBE existir (NO en GitHub)
├── .env.example            ← Plantilla (SÍ en GitHub)
├── .gitignore              ← Protege .env
├── composer.json
├── public/
│   ├── index.php
│   └── verify_production.php  ← Script de verificación
└── src/
    └── Config/
        └── Config.php
```

## 🆘 Solución Rápida (Una línea)

Si tienes acceso SSH al servidor:

```bash
cat > .env << 'EOF'
DB_HOST=192.185.46.27
DB_NAME=hhempeos_calidad
DB_USER=hhempeos_calidad
DB_PASS=Evallish.2026
PONCHE_DB_HOST=192.185.46.27
PONCHE_DB_NAME=hhempeos_ponche
PONCHE_DB_USER=hhempeos_ponche
PONCHE_DB_PASS=Hugo##2025#
GEMINI_API_KEY=AIzaSyAMUJNRxyD1QbwjpvkkJdrYSgv40YnPMMA
GEMINI_MODEL=gemini-3-flash-preview
GEMINI_CONNECT_TIMEOUT=10
GEMINI_TIMEOUT=60
APP_NAME=Evallish BPO
BASE_URL=/
TIMEZONE=America/Santo_Domingo
EOF
```

## 🐛 Troubleshooting

### Error persiste después de crear .env
1. Verifica la ruta del archivo (debe estar en la raíz del proyecto)
2. Verifica que el archivo se llame exactamente `.env` (sin extensión adicional)
3. Verifica permisos de lectura del archivo
4. Reinicia el servidor web: `sudo service apache2 restart` o `sudo service nginx restart`

### No puedes crear .env en cPanel
1. En el File Manager, asegúrate de mostrar archivos ocultos
2. Crea un archivo llamado `env.txt`
3. Pega el contenido
4. Renómbralo a `.env`

### El servidor no lee el .env
El código ahora busca el `.env` en múltiples ubicaciones:
- Raíz del proyecto
- Un nivel arriba de `public/`
- Relativo a `src/Config/`

---

**¿Necesitas ayuda?** Ejecuta `php public/verify_production.php` y comparte la salida.
