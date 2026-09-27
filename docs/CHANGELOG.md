# Changelog

## 2026-08-30 — Phase 10

- Completed final regression, security/database/business-rule and N+1 review without redesign or feature expansion.
- Added idempotent final demo data for customers, coupons, shipping, settings, banners, banned words, representative orders/reviews, and local fallback product imagery.
- Fixed missing seeded product images so customer pages use a valid local fallback rather than a broken storage URL.
- Replaced the Laravel boilerplate README with Vietnamese setup/handoff documentation and added `docs/DEFENSE_GUIDE.md`.
- Audited `.env.example`, ignored runtime/secrets, route documentation, and final project state; added focused Phase 10 regression coverage.

## 2026-08-29 — Phase 9

- Added cached system settings with configured store information, announcement, default theme, and centralized low-stock threshold.
- Added responsive light/dark/system customer themes with flash-free localStorage and system preference handling.
- Added secure local-image banner administration, schedule/status filtering, ordered homepage carousel, and fallback display.
- Improved shared layout accessibility, validation feedback, mobile admin navigation, and converted Phase 1 tests to PHPUnit attributes.

## 2026-08-28 — Phase 8

- Added resilient post-transaction order placed, confirmed, shipping, completed, and cancellation emails.
- Added responsive Vietnamese SHIRT STORE templates using order snapshots and customer-owned order links.
- Customized Laravel's password reset notification while retaining 15-minute expiry, enumeration protection, and rate limits.
- Added 17 Phase 8 email/security/transaction tests; mail delivery failures are logged without reverting business state.

## 2026-08-27 — Phase 7

- Added one-per-product verified-purchase reviews with throttled, server-validated submissions and customer review history.
- Added approved-only public ratings/reviews, safe escaped rendering, and product rating aggregates.
- Added centralized review moderation transitions plus admin review search/filter/detail workflows.
- Added active banned-word management and server-side content filtering with 25 Phase 7 tests.

## 2026-08-26 — Phase 6

- Replaced dashboard placeholders with completed revenue, order/customer/product/inventory metrics, rankings, recent orders, and a seven-day chart.
- Added admin order search/detail and locked state-machine transitions with safe pending/confirmed cancellation.
- Added customer-only management with aggregates and lock/unlock controls.
- Added completed-only revenue, top-product, low-stock, status, and best-customer reports plus 25 Phase 6 tests.

## 2026-08-26 — Phase 5

- Added COD checkout with server-authoritative price, coupon, shipping, stock, and total calculation.
- Added transactional order snapshots, safe order codes, variant/coupon row locks, status logs, and checkout-token idempotency.
- Added customer order success/history/detail and pending cancellation with exact stock/coupon restoration.
- Added 25 Phase 5 checkout/order consistency tests; no Phase 6 admin processing added.

## 2026-08-25 — Phase 4

- Added persistent variant cart and unique customer wishlist with admin blocking.
- Added server-side coupon validation/calculation without recording usage.
- Added configurable shipping zones/rates, freeship thresholds, cart estimates, and admin management.
- Activated product cart/buy/wishlist controls and live header counts; no checkout or order behavior added.

## 2026-08-24 — Phase 3

- Added responsive customer header/footer, merchandising home page, and reusable product cards.
- Added shop search, catalog filters, four sort modes, and query-preserving pagination.
- Added product gallery/detail, scheduled-sale display, live color/size stock, size suggestion, and related products.
- Added Phase 3 customer feature tests; cart/buy controls remain non-functional placeholders.

## 2026-08-24 — Phase 2

- Added catalog schema/models, scheduled sale pricing, images, variants, stock and low-stock logic.
- Added admin CRUD for products, categories, colors, sizes, and inventory.
- Added secure product/category image validation and storage, product soft delete/restore, and `Phase2Seeder`.
- Added Phase 2 feature coverage; no Phase 3 commerce work included.
