# Despliegue en Render (Docker)

API Laravel 13 + PostgreSQL, lista para un **repositorio backend independiente** en GitHub.

## Requisitos

- Cuenta en [Render](https://render.com)
- Repositorio GitHub solo con el contenido de la carpeta `backend/`
- PostgreSQL (Render Postgres o externo)

## Archivos de despliegue

| Archivo | Uso |
|---------|-----|
| `Dockerfile` | Imagen PHP 8.3 con extensiones (pgsql, gd, zip) para PDF |
| `docker/entrypoint.sh` | Migraciones y caché al arrancar |
| `.dockerignore` | Excluye `vendor`, `.env`, tests, etc. |
| `render.yaml` | Blueprint opcional (Web Service + Postgres) |

## Opción A — Blueprint (recomendada)

1. Sube el backend a GitHub (raíz del repo = carpeta `backend` actual).
2. En Render: **New → Blueprint** → conecta el repo.
3. Render detecta `render.yaml` y crea API + base de datos.
4. En el servicio web, define variables **manuales** (sync: false en el blueprint):
   - `APP_KEY` — en local: `php artisan key:generate --show`
   - `APP_URL` — ej. `https://clinica-zarate-api.onrender.com`
   - `FRONTEND_URL` — URL del frontend en producción
   - `CORS_ALLOWED_ORIGINS` — mismo que `FRONTEND_URL` o lista separada por comas

5. **Deploy**. El entrypoint ejecuta `php artisan migrate --force` en cada arranque.

### Primera carga de datos (opcional)

En Render → Environment → `RUN_DB_SEED=true` → redeploy una vez → vuelve a `false`.

## Opción B — Web Service manual

1. **New → Web Service** → repo backend.
2. **Runtime:** Docker  
3. **Dockerfile path:** `./Dockerfile`  
4. **Health check path:** `/up`  
5. Crea **PostgreSQL** y enlaza `DB_URL` con la connection string (o usa `DATABASE_URL`; el entrypoint la copia a `DB_URL`).

### Variables de entorno mínimas

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...          # obligatorio
APP_URL=https://tu-api.onrender.com
LOG_CHANNEL=stderr

DB_CONNECTION=pgsql
DB_URL=<connection string de Render Postgres>
DB_SSLMODE=require

FRONTEND_URL=https://tu-frontend.com
CORS_ALLOWED_ORIGINS=https://tu-frontend.com

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
OLLAMA_ENABLED=false
```

## Health checks

- Laravel: `GET /up`
- API: `GET /api/health`

## Frontend

En el frontend (Vite), configura:

```env
VITE_API_URL=https://tu-api.onrender.com/api
```

## Notas

- **Ollama** no corre dentro del contenedor; en producción deja `OLLAMA_ENABLED=false` o apunta `OLLAMA_BASE_URL` a un servicio externo.
- Plan **free**: el servicio puede dormir; el primer request tarda más.
- Los PDFs y archivos en `storage/` son efímeros en Render; para persistencia usa un disco o S3 más adelante.
- Genera `APP_KEY` solo una vez y reutilízala entre redeploys.

## Build local (prueba)

```bash
cd backend
docker build -t clinica-api .
docker run --rm -p 8000:8000 \
  -e APP_KEY=base64:tu_clave \
  -e DB_URL="postgresql://..." \
  -e APP_URL=http://localhost:8000 \
  clinica-api
```
