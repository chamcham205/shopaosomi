# Phase 2 Admin Routes

- `/admin/products`: product CRUD, status toggle, soft delete, and restore.
- `/admin/categories`: category CRUD and status toggle.
- `/admin/colors`: color CRUD and status toggle.
- `/admin/sizes`: size CRUD.
- `/admin/inventory`: product inventory list, variant matrix, and stock update.

All routes require `auth`, `admin`, and active-user middleware.

## Phase 3 Customer Routes

- `GET /`: home with categories, new, featured, and active-sale products.
- `GET /products`: shop search, filters, sorting, and pagination.
- `GET /products/{slug}`: active product detail, variant stock, size guide, and related products.

## Phase 4 Routes

- `/cart`: customer cart add, update, remove, clear, coupon, and shipping estimate actions.
- `/wishlist`: customer wishlist list/add/remove actions.
- `/admin/coupons`: coupon CRUD and status toggle.
- `/admin/shipping`: shipping zone/rate configuration.

## Phase 5 Customer Routes

- `GET|POST /checkout`: COD checkout and transactional place-order flow.
- `GET /checkout/success/{order}`: customer-owned order confirmation.
- `GET /account/orders` and `GET /account/orders/{order}`: customer-owned history/detail.
- `POST /account/orders/{order}/cancel`: transactional pending-order cancellation.

## Phase 6 Admin Routes

- `/admin/orders` and `/admin/orders/{order}`: searchable order management, detail, timeline, and valid status transitions.
- `/admin/customers` and `/admin/customers/{user}`: customer-only aggregates and lock/unlock.
- `/admin/reports`: completed-revenue, status, top-product, low-stock, and best-customer reporting.
- `/admin/dashboard`: live operational metrics and seven-day completed revenue.

## Phase 7 Review Routes

- `POST /products/{product}/reviews`: throttled verified-purchase review submission.
- `GET /account/reviews`: customer-owned review history.
- `/admin/reviews`: search, filter, inspect, and moderate reviews through valid status transitions.
- `/admin/banned-words`: banned-word CRUD and active/inactive controls.

## Phase 8 Email Notifications

- No new HTTP routes; order mail is dispatched after successful existing checkout/cancellation/admin status actions.
- Existing `POST /forgot-password` uses the branded Laravel reset-password notification and existing reset routes.

## Phase 9 Admin Routes

- `GET|PUT /admin/settings`: view and update validated general, contact, inventory, and appearance settings.
- `/admin/banners`: banner list/create/update/delete and status toggle with scheduled homepage visibility.

## Phase 10 Route Summary

- Authentication: `GET|POST /register`, `GET|POST /login`, `POST /logout`, forgot/reset-password routes.
- Public storefront: `GET /`, `GET /products`, `GET /products/{slug}`.
- Customer commerce routes require `auth`, active account, and `prevent.admin.shopping` middleware.
- Admin routes use prefix `/admin`, name prefix `admin.`, and `auth`, `admin`, active-account middleware.
- Phase 10 adds no HTTP routes; `php artisan route:list` reports 96 routes.
