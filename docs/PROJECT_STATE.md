# Project State

- Phase 1: complete (authentication and admin access).
- Phase 2: complete (catalog and inventory).
- Phase 2 includes Category, Product, ProductImage, Color, Size, ProductVariant, inventory/low-stock handling, scheduled sale pricing, product image upload, product soft delete/restore, admin CRUD, and `Phase2Seeder`.
- Verification: migrations and Phase 2 seed run successfully; all five catalog/inventory admin pages render for an admin; 41 tests / 106 assertions pass.
- Phase 3: complete (responsive customer header/footer, home merchandising, searchable/filterable/sortable shop, paginated results, product gallery/detail, variant stock selection, size guide/suggestion, and related products).
- Phase 4: complete (server-priced variant cart, customer wishlist, coupon validation/calculation, configurable shipping, cart summary, and admin coupon/shipping management).
- Phase 4 coupon validation is retained; coupon usage is now recorded only after a successful Phase 5 order.
- Phase 5: complete (COD checkout, authoritative OrderService transactions, row locking, idempotent order creation, snapshots, stock/coupon commit and rollback, customer cancellation, success page, and order history).
- Orders start at `pending`; customer cancellation remains limited to pending orders, while Phase 6 admin transitions follow the locked state machine.
- Phase 6: complete (real-data admin dashboard, locked order state transitions/cancellation, customer lock management, revenue/order/product/inventory/customer reports, and seven-day charts).
- Revenue and selling rankings use completed orders only.
- Phase 7: complete (verified-purchase reviews, pending moderation, approved-only public ratings, banned-word filtering, customer review history, and admin moderation tools).
- Phase 8: complete (post-commit order placed/status/cancellation emails, Vietnamese responsive templates, resilient mail failure handling, and branded password reset notification).
- Password reset expiry remains 15 minutes; local delivery works with Laravel's `log` mailer.
- Phase 9: complete (cached system settings, configurable low-stock threshold, light/dark/system customer theme, scheduled local-image banners, configured announcement/footer, and responsive UI refinements).
- Phase 10: complete (final regression, security/database/business-rule audit, idempotent demo data, local image fallback, Vietnamese README, defense guide, and cleanup).
- Final verification: all migrations ran; 96 routes; 183 tests / 371 assertions pass; `git diff --check` passes.
- No known automated-test failures. Manual final-demo review should use `MAIL_MAILER=log` and confirm XAMPP/MySQL plus the public storage link are running on the presentation machine.
