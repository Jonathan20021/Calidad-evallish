# 🔐 Configuración Segura de API Keys

## ⚠️ IMPORTANTE: Protección de API Keys

Este proyecto ha sido configurado para **proteger las API keys** y evitar que se filtren en GitHub.

## 📋 Pasos de Configuración

### 1. Crear archivo de configuración local

Copia el archivo de ejemplo y crea tu archivo de configuración:

```bash
copy .env.example .env
```

o en Linux/Mac:

```bash
cp .env.example .env
```

### 2. Obtener una nueva API Key de Gemini

⚠️ **Tu API key anterior fue reportada como filtrada y bloqueada**. Necesitas obtener una nueva:

1. Ve a [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Inicia sesión con tu cuenta de Google
3. Haz clic en **"Get API Key"** o **"Create API Key"**
4. Copia la nueva API key generada

### 3. Configurar tu archivo .env

Abre el archivo `.env` que creaste y reemplaza los valores:

```env
# ⚠️ REEMPLAZA 'tu_api_key_aqui' con tu nueva API key de Gemini
GEMINI_API_KEY=tu_nueva_api_key_de_gemini_aqui
GEMINI_MODEL=gemini-3-flash-preview

# Configuración de base de datos
DB_HOST=localhost
DB_NAME=hhempeos_evallish
DB_USER=root
DB_PASS=

# Configuración de base de datos Ponche
PONCHE_DB_HOST=localhost
PONCHE_DB_NAME=hhempeos_ponche
PONCHE_DB_USER=root
PONCHE_DB_PASS=
```

### 4. Verificar que .env NO se suba a GitHub

El archivo `.gitignore` ya está configurado para ignorar `.env`, pero verifica:

```bash
git status
```

**⚠️ NUNCA DEBES VER `.env` en la lista de archivos a subir**

Si lo ves, NO HAGAS `git add .env`

### 5. Remover la API key antigua del historial de GitHub

Si tu repositorio ya está en GitHub con la API key expuesta, necesitas:

#### Opción A: Hacer el repositorio privado (Recomendado)
1. Ve a tu repositorio en GitHub
2. Settings → General → Danger Zone
3. Haz clic en "Change visibility" → "Make private"

#### Opción B: Limpiar el historial de Git (Avanzado)
```bash
# ⚠️ CUIDADO: Esto reescribe el historial
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch src/Config/Config.php" \
  --prune-empty --tag-name-filter cat -- --all

# Forzar push (esto sobrescribe el historial remoto)
git push origin --force --all
```

### 6. Actualizar el repositorio

Después de configurar todo:

```bash
git add .
git commit -m "chore: Implementar configuración segura con .env"
git push
```

## ✅ Verificación

Para verificar que la configuración funciona correctamente:

1. Accede a tu aplicación
2. Ve a la sección de análisis de llamadas con IA
3. Intenta generar un análisis
4. Si funciona correctamente, verás los resultados del análisis

## 🔒 Mejores Prácticas

- ✅ **SIEMPRE** usa `.env` para configuración sensible
- ✅ **NUNCA** hagas commit del archivo `.env`
- ✅ **Mantén** `.env.example` actualizado sin valores reales
- ✅ **Comparte** `.env.example` con tu equipo
- ✅ **Revoca** API keys filtradas inmediatamente
- ✅ **Considera** hacer el repositorio privado si contiene lógica de negocio sensible

## 🆘 Solución de Problemas

### Error: "GEMINI_API_KEY no configurada"

- Verifica que el archivo `.env` existe en la raíz del proyecto
- Verifica que `GEMINI_API_KEY` está configurada en `.env`
- Verifica que no hay espacios extras alrededor del `=`

### Error: "Gemini error (403): Your API key was reported as leaked"

- Necesitas crear una **nueva** API key en Google AI Studio
- La API key antigua está permanentemente bloqueada
- Actualiza el archivo `.env` con la nueva key

### El archivo .env no se lee

- Verifica que PHP tiene permisos para leer el archivo
- En Windows: clic derecho → Propiedades → Seguridad
- El archivo debe estar en: `c:\xampp\htdocs\Calidad-evallish\.env`

## 📚 Recursos Adicionales

- [Google AI Studio](https://makersuite.google.com/app/apikey)
- [Documentación de Gemini API](https://ai.google.dev/docs)
- [Mejores prácticas de seguridad para API keys](https://cloud.google.com/docs/authentication/api-keys)

---

**Última actualización:** Febrero 2026
**Autor:** Sistema de Seguridad Evallish BPO
