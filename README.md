# Yii2 REST API (Backend Test Task)

Production-grade REST API on Yii2 Advanced: users, albums, photos. Bearer Token auth.

## Table of Contents

- [Stack](#stack)
- [Quick Start (Docker)](#quick-start-docker)
- [Manual Installation](#manual-installation)
- [API Endpoints](#api-endpoints)
- [Request Examples](#request-examples)
- [Tests](#tests)
- [Additional](#additional)

---

## Stack

- PHP 8.2
- Yii2 (advanced template)
- MySQL 8.0
- Nginx
- REST API, JSON everywhere
- Auth: `Authorization: Bearer <token>` (HttpBearerAuth)

---

## Quick Start (Docker)

### Requirements

- Docker
- Docker Compose

### Launch

```bash
docker compose up -d --build
```

### What Happens on Startup

1. **MySQL** — starts `yii2api-mysql` container (port 3306)
2. **App** — `yii2api-app` container (PHP-FPM):
   - Waits for MySQL to be ready
   - `composer install` (if `vendor` folder is missing)
   - `php init --env=Production`
   - Database migrations
   - Seeders (10 users, 100 albums, 1000 photos)
3. **Web** — `yii2api-web` container (Nginx) proxies requests to PHP-FPM

### Getting the Bearer Token

The token for user #1 is printed in the logs on first run:

```bash
docker compose logs app
```

Look for output like:

```
=== Use this token for API testing (user #1) ===
uOjsaEaS0addEBo95Sfn8eLjODv04iDp
==============================================
```

Copy the token and use it in the `Authorization: Bearer <token>` header.

### API Access

- **Base URL:** `http://localhost:8088`
- **Important:** use `/users`, `/albums` — not `/api/users` — the app root already points to the API

### Verify It Works

```bash
# Health check (no token)
curl -s http://localhost:8088/health

# User list (with token)
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8088/users
```

### Stop

```bash
docker compose down
```

MySQL data is persisted in volume `yii2api_mysql_data`.

---

## Manual Installation

### Requirements

- PHP 8.1+
- Composer
- MySQL 8+

### 1. Dependencies

```bash
composer install
```

### 2. Init

```bash
php init --env=Development --overwrite=n
```

### 3. Config

```bash
cp .env.example .env
```

Fill in `.env`:

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_DSN` | MySQL DSN | `mysql:host=127.0.0.1;port=3306;dbname=yii2api` |
| `DB_USER` | Database user | `root` or `yii2api` |
| `DB_PASSWORD` | Database password | — |
| `DB_CHARSET` | Charset | `utf8mb4` |
| `DEMO_USER_PASSWORD` | Password for seeders (required) | — |
| `APP_BASE_URL` | API base URL (optional) | `http://localhost:8088` |

### 4. Database

```sql
CREATE DATABASE yii2api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Optional: MySQL only via Docker: `docker compose up -d mysql` (credentials: `yii2api`/`secret`).

### 5. Migrations and Seeders

```bash
php yii migrate --interactive=0
php yii seed/all
```

### 6. Generate Token (if needed)

```bash
php yii token/generate 1
```

### 7. Run the API Server

```bash
php -S localhost:8088 -t api/web api/web/router.php
```

---

## API Endpoints

| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| GET | `/health` | no | Health check |
| GET | `/users` | yes | User list (paginated) |
| GET | `/users/{id}` | yes | User and their albums |
| GET | `/albums` | yes | Album list (paginated) |
| GET | `/albums/{id}` | yes | Album with owner and photos |

**Aliases:** same routes available with `/v1/` prefix (e.g. `/v1/users`, `/v1/albums/1`).

### Pagination

- `?page=1` — page (default 1)
- `?per-page=20` — items per page (default 20, max 100)

---

## Request Examples

```bash
# Health (no token)
curl -s http://localhost:8088/health

# No token -> 401
curl -s -w "\nHTTP: %{http_code}" http://localhost:8088/users

# With token
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8088/users
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8088/users/1
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8088/albums
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8088/albums/1

# 404
curl -s -w "\nHTTP: %{http_code}" -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8088/users/999999
```

### JSON Response Examples

**GET /users** (200):

```json
{
  "items": [
    {"id": 1, "first_name": "First1", "last_name": "Last1"}
  ],
  "_links": {...},
  "_meta": {"totalCount": 10, "pageCount": 1, "currentPage": 1, "perPage": 20}
}
```

**GET /users/1** (200):

```json
{
  "id": 1,
  "first_name": "First1",
  "last_name": "Last1",
  "albums": [
    {"id": 1, "title": "..."},
    {"id": 2, "title": "..."}
  ]
}
```

**GET /albums/1** (200):

```json
{
  "id": 1,
  "first_name": "First1",
  "last_name": "Last1",
  "photos": [
    {"id": 1, "title": "...", "url": "http://localhost:8088/images/static/photo1.png"}
  ]
}
```

**401 without token:**

```json
{
  "name": "Unauthorized",
  "message": "Your request was made with invalid credentials."
}
```

---

## Tests

```bash
# 1. Create test DB
mysql -uroot -e "CREATE DATABASE yii2api_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Migrations and seed for main DB
php yii migrate --interactive=0
php yii seed/all

# 3. Migrations and seed for test DB
php yii_test migrate --interactive=0
php yii_test seed/all

# 4. Run
vendor/bin/codecept run -c api
```

---

## Additional

### Static Images (Photo::getUrl)

- Folder: `api/web/images/static/`
- Files: `photo2.png` … `photo8.png`, `placeholder.png`
- `Photo::getUrl()` returns a random file from the list as an absolute URL
- Host is taken from `APP_BASE_URL` or the current request

### Architecture

- **Template:** yii2-app-advanced, API in `api/`, versioning in `api/modules/v1/`
- **Services:** UserReadService, AlbumReadService — read layer; UserResourceAssembler, AlbumResourceAssembler — mapping to API format
- **Routing:** `/users`, `/albums` and `/v1/users`, `/v1/albums` — both variants supported
- **Auth:** HttpBearerAuth in BaseApiController, `/health` is public
- **Tokens:** `user_token` with `token_hash` (SHA-256), plaintext only at creation

### Troubleshooting (Docker)

| Issue | Solution |
|-------|----------|
| `SQLSTATE[HY000] [2002] No such file or directory` | App was connecting via Unix socket (localhost). Fixed: entrypoint now writes `.env` with `host=mysql` for Docker. Run `docker compose down`, `docker compose up -d --build` |
| 502 Bad Gateway | Wait 15–30 sec after `up` — the app container runs migrations and seeders |
| Port 8088 in use | Change in `docker-compose.yml`: `ports: "8089:80"` |
| DB connection error | Ensure MySQL is healthy: `docker compose ps` |

### License

BSD-3-Clause
