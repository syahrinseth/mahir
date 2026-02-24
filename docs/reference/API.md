# API Reference

Mahir REST API endpoint reference. Full interactive docs will be available via Scramble.

---

## Base URL

```
https://{slug}.mahir.test/api/v1
```

All routes are prefixed with `/api/v1` (configured in `bootstrap/app.php`).

---

## Authentication

Token-based via Laravel Sanctum. Include the token in the `Authorization` header:

```
Authorization: Bearer {token}
```

Obtain a token by calling the login or register endpoint.

---

## Response Format

All responses follow this structure:

```json
{
    "message": "Human-readable message.",
    "data": { ... }
}
```

| Status Code | Meaning |
|-------------|---------|
| `200` | Success (read/update/delete) |
| `201` | Created |
| `401` | Unauthenticated |
| `404` | Not found |
| `422` | Validation error |

---

## Endpoints

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/auth/register` | No | Register a new tenant user |
| POST | `/auth/login` | No | Login, receive bearer token |
| POST | `/auth/logout` | Yes | Revoke current token |
| GET | `/auth/user` | Yes | Get authenticated user |

### Tenants

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/tenants` | Yes | List all tenants |
| POST | `/tenants` | Yes | Create a tenant |
| GET | `/tenants/{id}` | Yes | Show a tenant |
| PUT | `/tenants/{id}` | Yes | Update a tenant |
| DELETE | `/tenants/{id}` | Yes | Delete a tenant |

### Subscriptions

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/subscriptions` | Yes | List all subscriptions |
| POST | `/subscriptions` | Yes | Create a subscription |
| GET | `/subscriptions/{id}` | Yes | Show a subscription |
| PUT | `/subscriptions/{id}` | Yes | Update a subscription |
| DELETE | `/subscriptions/{id}` | Yes | Delete a subscription |

### Utility

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/ping` | No | Health check |
