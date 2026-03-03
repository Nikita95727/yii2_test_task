# Yii2 REST API (Backend Test Task)

Production-grade REST API on Yii2 Advanced: users, albums, photos. Bearer Token auth.

## Stack

- PHP 8.1+
- Yii2 (advanced template)
- MySQL 8+
- REST API only, JSON everywhere
- Auth: `Authorization: Bearer <token>` (HttpBearerAuth)
- Tokens: hash stored in `user_token`, plaintext only at creation (printed to console)

## Requirements

- PHP 8.1+
- Composer
- MySQL 8+
- (optional) Docker for running the database

## Installation

### 1. Dependencies

```bash
composer install
```

### 2. ENV Config

```bash
cp .env.example .env
```

Fill in `.env` (file is not committed):

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_DSN` | MySQL DSN | `mysql:host=127.0.0.1;port=3306;dbname=yii2api` |
| `DB_USER` | Database user | `root` or `yii2api` |
| `DB_PASSWORD` | Database password | — |
| `DB_CHARSET` | Charset | `utf8mb4` |
| `DEMO_USER_PASSWORD` | Password for seed (required, set locally) | — |
| `APP_BASE_URL` | Base URL for API (optional) | `http://localhost:8080` |

### 3. MySQL

```bash
# Docker
docker compose up -d mysql

# Or create DB manually:
# CREATE DATABASE yii2api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Migrations

```bash
php yii migrate --interactive=0
```

### 5. Seed

```bash
php yii seed/all
```

Or individually: `yii seed/users`, `yii seed/albums`, `yii seed/photos`.

**Token**: after `yii seed/users` or `yii seed/all`, a Bearer token for user #1 is printed to the console. Copy it for API testing.

### 6. Run API

```bash
php -S localhost:8080 -t api/web api/web/router.php
```

## API Endpoints

| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| GET | /health | no | Health check |
| GET | /users | yes | User list (paginated) |
| GET | /users/{id} | yes | User + albums[] |
| GET | /albums | yes | Album list (paginated) |
| GET | /albums/{id} | yes | Album: id, first_name, last_name (owner), photos[] |

**Aliases**: same routes available with `/v1/` prefix (e.g. `/v1/users`, `/v1/albums/1`).

### Pagination

- `?page=1` — page (default 1)
- `?per-page=20` — items per page (default 20, max 100)
- Ordering: `id ASC` for deterministic order

## curl Examples

```bash
# Health (no token)
curl -s http://localhost:8080/health

# No token -> 401
curl -s -w "\nHTTP: %{http_code}" http://localhost:8080/users

# With token
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8080/users
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8080/users/1
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8080/albums
curl -s -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8080/albums/1

# Not found -> 404
curl -s -w "\nHTTP: %{http_code}" -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8080/users/999999
```

### JSON Examples

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
    {"id": 1, "title": "...", "url": "http://localhost:8080/images/static/photo1.png"}
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

## Tests

```bash
# 1. Migrations and seed (for test data)
php yii migrate --interactive=0
php yii seed/all

# 2. Run
vendor/bin/codecept run -c api
```

Expected output:

```
ApiCest: Test 401 without token ..................... Ok
ApiCest: Test 401 with invalid token ................ Ok
ApiCest: Test 200 users with token .................. Ok
ApiCest: Test 200 users id with albums ............. Ok
ApiCest: Test 200 albums list with token ........... Ok
ApiCest: Test 200 albums id with first name last name photos ... Ok
ApiCest: Test pagination per page .................. Ok
ApiCest: Test 404 not found ........................ Ok
ApiCest: Test health public ........................ Ok
ApiCest: Test v1 routes work ....................... Ok
PhotoGetUrlTest: Test get url returns absolute url  Ok
PhotoGetUrlTest: Test get url field exists ......... Ok
```

## Static Images (Photo::getUrl)

- Folder: `api/web/images/static/`
- Files: `photo2.png` … `photo8.png`, `placeholder.png`
- `Photo::getUrl()` returns a random file from the list as an absolute URL
- Base host: from `APP_BASE_URL` or current request

## Design Decisions

- **Template**: yii2-app-advanced, API in `api/`, versioning in `api/modules/v1/`
- **Architecture**: UserReadService / AlbumReadService — read layer; UserResourceAssembler / AlbumResourceAssembler — mapping to API format
- **Routing**: `/users`, `/albums` and `/v1/users`, `/v1/albums` — both variants supported
- **Auth**: HttpBearerAuth in controller behaviors (BaseApiController), `/health` is public
- **FK**: CASCADE for album→user and photo→album
- **Tokens**: `user_token` with `token_hash` (SHA-256), plaintext only at creation
- **Seed**: DEMO_USER_PASSWORD set in `.env`, not in repository
- **N+1**: eager loading via `with()` in services
- **Errors**: JSON format via JsonErrorHandler

## Known Assumptions

- `Photo::getUrl()` returns a random file from the static folder (not stored in DB)
- Tokens have no expiry (revoked_at = null)
- Tests require pre-seeded data (migrate + seed)

## License

BSD-3-Clause
