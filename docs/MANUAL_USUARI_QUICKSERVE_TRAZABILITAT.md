# Annex intern: Traçabilitat funcional del manual (QuickServe)

Aquest annex es per manteniment intern del manual. No esta pensat per al lliurable final a client.

## 1. Operativa TPV

### 1.1 Inici de sessio

- Pantalla/servei:
  - `flutter_app/lib/features/auth/data/auth_service.dart`
- Endpoints:
  - `POST /auth/login`
  - `GET /auth/me`
- Regla:
  - Acces via token Sanctum.
- Missatges/validacions clau:
  - "Credenciales incorrectas."

### 1.2 Venda directa

- Pantalla:
  - `flutter_app/lib/features/tpv/presentation/tpv_page_modern.dart`
  - `flutter_app/lib/features/tpv/presentation/payment_page.dart`
- Endpoint:
  - `POST /orders`
- Controller:
  - `app/Http/Controllers/OrderController.php`
- Regles:
  - En venda normal, estat final pagat.
  - Reduccio de stock per cada linea.

### 1.3 Encarrec (alta)

- Pantalla:
  - `flutter_app/lib/features/tpv/presentation/tpv_page_modern.dart`
- Endpoint:
  - `POST /orders` (`is_preorder=true`)
- Controller:
  - `app/Http/Controllers/OrderController.php`
- Regles:
  - Estat inicial pendent.
  - Assignacio de numero de recollida diari.

### 1.4 Encarrecs pendents (consulta)

- Pantalla:
  - `flutter_app/lib/features/tpv/presentation/pending_preorders_page.dart`
- Endpoint:
  - `GET /orders/pending`
- Controller:
  - `app/Http/Controllers/OrderController.php`

### 1.5 Cobrament encarrec

- Pantalla:
  - `flutter_app/lib/features/tpv/presentation/tpv_page_modern.dart`
- Endpoint:
  - `POST /orders/{id}/charge`
- Controller:
  - `app/Http/Controllers/OrderController.php`
- Regles:
  - Passa a estat pagat.
  - Pot afegir bosses (`bag_count`).
  - Assigna numeracio fiscal.
- Missatges/validacions:
  - Error si no es troba producte de bossa.

### 1.6 Anulacio encarrec

- Pantalla:
  - `flutter_app/lib/features/tpv/presentation/tpv_page_modern.dart`
- Endpoint:
  - `POST /orders/{id}/cancel`
- Controller:
  - `app/Http/Controllers/OrderController.php`
- Regla:
  - Restaura stock i elimina comanda.

## 2. Administracio

### 2.1 Acces per PIN

- Pantalla/servei:
  - `flutter_app/lib/features/admin/data/admin_service.dart`
  - `flutter_app/lib/features/tpv/presentation/tpv_page_modern.dart`
- Endpoint:
  - `POST /admin/verify-pin`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`

### 2.2 Resum (dashboard)

- Pantalla:
  - `flutter_app/lib/features/admin/presentation/sections/dashboard_section.dart`
- Endpoint:
  - `GET /admin/dashboard`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`
- Regla:
  - KPI calculats principalment sobre comandes pagades.

### 2.3 Tancament de caixa

- Pantalla:
  - `flutter_app/lib/features/admin/presentation/sections/caixa_section.dart`
- Endpoint:
  - `GET /admin/caixa`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`
- Regla:
  - Desglossament fiscal i totals de caixa.

### 2.4 Categories

- Pantalla:
  - `flutter_app/lib/features/admin/presentation/sections/categories_section.dart`
- Endpoints:
  - `GET /admin/categories`
  - `POST /admin/categories`
  - `PUT /admin/categories/{id}`
  - `DELETE /admin/categories/{id}`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`

### 2.5 Productes

- Pantalla:
  - `flutter_app/lib/features/admin/presentation/sections/products_section.dart`
- Endpoints:
  - `GET /admin/products`
  - `POST /admin/products`
  - `PUT /admin/products/{id}`
  - `DELETE /admin/products/{id}`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`
- Notes:
  - Gestio de stock, atributs i actiu/inactiu.

### 2.6 Treballadors

- Pantalla:
  - `flutter_app/lib/features/admin/presentation/sections/workers_section.dart`
- Endpoints:
  - `GET /admin/workers`
  - `POST /admin/workers`
  - `PUT /admin/workers/{id}`
  - `DELETE /admin/workers/{id}`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`
- Regla critica:
  - Unic PIN admin actiu simultani.

### 2.7 Historial de vendes

- Pantalla:
  - `flutter_app/lib/features/admin/presentation/sections/history_section.dart`
- Endpoints:
  - `GET /admin/orders`
  - `GET /admin/orders/{id}`
  - `DELETE /admin/orders/{id}`
- Controller:
  - `app/Http/Controllers/Api/AdminController.php`

## 3. Setup, configuracio i advertencies per al manual

- Contracte API:
  - `API.md`
- Variables d'entorn ticket/fiscal:
  - `.env.example`
  - `config/ticket.php`
- Arrencada automatitzada:
  - `composer.json` (scripts `setup`, `dev`)
- Config Flutter:
  - `flutter_app/lib/core/config/app_config.dart`
- Dades demo:
  - `database/seeders/UserSeeder.php`

Advertencies incloses al manual:

- `composer setup` no fa `db:seed`.
- Si no es defineix `API_BASE_URL`, Flutter usa default remot.
- Convivencia amb fluxos legacy web.
