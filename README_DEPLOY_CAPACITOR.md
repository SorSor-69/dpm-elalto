# Deploy en Railway y empaquetado con Capacitor (guía rápida)

Resumen: esta guía asume que ya tienes creado el repo en GitHub y una cuenta en Railway.

1) Preparar repo y subir el código a GitHub

PowerShell (ejecutar en la raíz del proyecto `c:\xampp\htdocs\AAAA\dpm.elalto`):

```powershell
git init
git add .
git commit -m "Prepare project for deploy: add Dockerfile and capacitor config"
git branch -M main
# reemplaza <URL_REPO> por la URL que te da GitHub
git remote add origin <URL_REPO>
git push -u origin main
```

2) Configurar Railway

- En Railway crea un nuevo proyecto -> "Deploy from GitHub" -> selecciona tu repo.
- En el dashboard del servicio, añade el plugin "MongoDB" (Marketplace) y copia las credenciales.
- En Settings -> Environment variables, añade las variables que tu `.env` requiere (mapea las claves):
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://<tu-proyecto>.up.railway.app` (luego de deploy, Railway te dará la URL)
  - `DB_CONNECTION=mongodb`
  - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (usando los valores del plugin MongoDB)

3) Verificar que la app cargue

- Abre la URL pública que Railway te entrega (https). Si ves errores, revisa logs en Railway.

4) Configurar CORS en Laravel (si usas llamadas AJAX desde dominios distintos)

- Edita `config/cors.php`: en `paths` agrega `api/*` si usas API; en `allowed_origins` pon la URL de tu app o `[]` temporalmente `['*']` para pruebas.

5) Preparar Capacitor y generar APK

- En tu máquina local (no en Railway), instala Capacitor y configura:

```powershell
# desde la raíz del proyecto
npm install @capacitor/core @capacitor/cli --save
npx cap init dpm-elalto com.dpm.elalto
```

- Edita `capacitor.config.json` y reemplaza `server.url` por la URL pública de Railway (ej: `https://mi-proyecto.up.railway.app`).

```json
{
  "appId": "com.dpm.elalto",
  "appName": "DPM El Alto",
  "webDir": "public",
  "server": { "url": "https://mi-proyecto.up.railway.app", "cleartext": false }
}
```

- Agrega la plataforma Android y abre Android Studio:

```powershell
npx cap add android
npx cap open android
```

- En Android Studio: Build -> Build Bundle(s) / APK(s) -> Build APK(s). El APK se generará para instalar en dispositivos.

6) Notas sobre sesiones y autenticación

- Si la app carga la URL remota en la WebView (server.url), las cookies de Laravel funcionan con HTTPS y dominio correcto.
- Si usas API separada y el origen difiere, configura CORS y considera usar tokens (JWT) o Laravel Sanctum correctamente.

7) Si quieres, te preparo el PR o los commits

- Puedo crear los archivos (ya están creados en tu workspace) y te doy los comandos para hacer commit/push y cómo conectar a Railway.

Si quieres que arme los comandos exactos con tu URL de GitHub, respóndeme con la URL del repo (o dime si quieres que te guíe a crear el repo desde la web).
