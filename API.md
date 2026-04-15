# Quickserve API Contract

Single source of truth for Flutter clients (`web`, `windows`, `android`) and backend integration.

Base URL in development:

- `http://127.0.0.1:8000/api`

Authentication strategy:

- Sanctum bearer token
- Send header: `Authorization: Bearer <token>`

---

## Public Endpoints

### `GET /ping`

Health check endpoint.

Response example:

```json
{
  "ok": true,
  "service": "quickserve-api",
  "timestamp": "2026-04-14T17:12:35+02:00"
}
```

### `POST /auth/login`

Login and issue bearer token.

Request body:

```json
{
  "email": "user@example.com",
  "password": "secret",
  "device_name": "flutter-web"
}
```

Success response:

```json
{
  "token": "<plain_text_token>",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

Error response (`422`):

```json
{
  "message": "Credenciales incorrectas."
}
```

---

## Protected Endpoints (`auth:sanctum`)

### `GET /auth/me`

Get current authenticated user.

Response:

```json
{
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com"
  }
}
```

### `POST /auth/logout`

Revoke current access token.

Response:

```json
{
  "message": "Sesion cerrada."
}
```

### `GET /catalog`

Get active categories and products for TPV.

Response shape:

```json
{
  "categories": [
    { "id": 1, "name": "Pollastres", "color": "#ffcc00" }
  ],
  "products": [
    {
      "id": 10,
      "name": "Pollastre",
      "price": 12.5,
      "stock": 8,
      "image_path": "storage/products/pollastre.jpg",
      "category_ids": [1],
      "category_names": ["Pollastres"]
    }
  ]
}
```

Notes:

- `price` includes IVA (same behavior as current web TPV).
- `stock` can be `null`.

### `GET /workers`

Get active workers for sale/preorder assignment.

Response:

```json
{
  "workers": [
    { "id": 1, "name": "Meri" },
    { "id": 2, "name": "Roc" }
  ]
}
```

### `POST /orders`

Create a paid order or preorder.

Request body:

```json
{
  "worker_id": 1,
  "payment_method": "Efectiu",
  "total_price": 14.9,
  "cart": [
    { "id": 10, "name": "Pollastre", "price": 12.5, "quantity": 1, "notes": null }
  ],
  "is_preorder": false,
  "pickup_time": null,
  "customer_name": null
}
```

Preorder variant:

- `is_preorder: true`
- `payment_method: "Pendent"`
- optional `pickup_time`, `customer_name`

Success response (`201`):

```json
{
  "success": true,
  "message": "Venda realitzada amb èxit!",
  "order_id": 123,
  "pickup_number": null,
  "fiscal_full_number": "2026-000123"
}
```

Preorder success example:

```json
{
  "success": true,
  "message": "Encàrrec #7 guardat!",
  "order_id": 130,
  "pickup_number": 7,
  "fiscal_full_number": null
}
```

### `GET /orders/pending`

Get pending preorders for current day.

Response shape:

```json
{
  "orders": [
    {
      "id": 130,
      "worker_id": 1,
      "total_price": "19.90",
      "payment_method": "Pendent",
      "status": "Pendent",
      "is_preorder": 1,
      "pickup_number": 7,
      "pickup_time": "13:30",
      "customer_name": "Carla",
      "items": [
        {
          "id": 500,
          "order_id": 130,
          "product_id": 10,
          "quantity": 1,
          "price_at_sale": "12.50",
          "notes": null,
          "product": {
            "id": 10,
            "name": "Pollastre"
          }
        }
      ]
    }
  ]
}
```

---

## Implementation Pointers

Backend route definitions:

- `routes/api.php`

Backend controllers currently used:

- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Controllers/Api/CatalogController.php`
- `app/Http/Controllers/OrderController.php`

Flutter centralized endpoint constants:

- `flutter_app/lib/core/network/api_endpoints.dart`

