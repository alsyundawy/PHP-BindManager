# REST API Documentation

**Base URL:** `https://your-domain.com/api/v1`  
**Authentication:** Bearer Token  
**Content-Type:** `application/json`

---

## Authentication

```http
Authorization: Bearer YOUR_API_TOKEN
```

Tokens are generated from the web UI under **Settings → API Tokens**.

---

## Response Format

### Success
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "page": 1,
    "per_page": 25,
    "total": 100
  }
}
```

### Error
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The zone name field is required.",
    "details": { "zone_name": ["required"] }
  }
}
```

---

## HTTP Status Codes

| Code | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 204 | No Content (delete success) |
| 400 | Bad Request (validation error) |
| 401 | Unauthorized (no/invalid token) |
| 403 | Forbidden (insufficient scope) |
| 404 | Not Found |
| 409 | Conflict (duplicate) |
| 422 | Unprocessable Entity |
| 429 | Too Many Requests (rate limited) |
| 500 | Internal Server Error |

---

## Zones

### List Zones
```http
GET /api/v1/zones
```
Query params: `?page=1&per_page=25&search=example.com&type=MASTER`

### Get Zone
```http
GET /api/v1/zones/{id}
```

### Create Zone
```http
POST /api/v1/zones
Content-Type: application/json

{
  "name": "example.com",
  "type": "MASTER",
  "ttl": 3600,
  "soa": {
    "mname": "ns1.example.com",
    "rname": "admin.example.com",
    "refresh": 3600,
    "retry": 900,
    "expire": 604800,
    "minimum": 300
  }
}
```

### Update Zone
```http
PUT /api/v1/zones/{id}
```

### Delete Zone
```http
DELETE /api/v1/zones/{id}
```

### Export Zone
```http
GET /api/v1/zones/{id}/export
```
Returns zone file as plain text.

### Import Zone
```http
POST /api/v1/zones/import
Content-Type: multipart/form-data

file: <zone-file>
```

---

## DNS Records

### List Records
```http
GET /api/v1/zones/{zone_id}/records
```
Query params: `?type=A&name=www`

### Create Record
```http
POST /api/v1/zones/{zone_id}/records

{
  "name": "www",
  "type": "A",
  "ttl": 3600,
  "content": "1.2.3.4"
}
```

### Update Record
```http
PUT /api/v1/zones/{zone_id}/records/{id}
```

### Delete Record
```http
DELETE /api/v1/zones/{zone_id}/records/{id}
```

---

## Users (Admin only)

```http
GET    /api/v1/users
POST   /api/v1/users
GET    /api/v1/users/{id}
PUT    /api/v1/users/{id}
DELETE /api/v1/users/{id}
```

---

## System

```http
GET /api/v1/system/health
GET /api/v1/system/info
GET /api/v1/system/stats
```

---

## Rate Limiting

Default: **300 requests/minute** per token.  
Headers returned:
```
X-RateLimit-Limit: 300
X-RateLimit-Remaining: 299
X-RateLimit-Reset: 1722600000
```

---

## API Scopes

| Scope | Access |
|---|---|
| `zones:read` | List and view zones |
| `zones:write` | Create, update, delete zones |
| `records:read` | List and view records |
| `records:write` | Create, update, delete records |
| `users:read` | List users (admin) |
| `users:write` | Manage users (admin) |
| `system:read` | Read system info |
| `*` | Full access |
