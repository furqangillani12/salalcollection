# AL MUFEED TRADERS — Storefront

The public ecommerce storefront for `almufeed.com.pk` lives **inside this same Laravel app**.
It runs alongside the existing POS on `pos.almufeed.com.pk`, sharing one database.

> **Branch policy:** all storefront work is on **`feature/website`**.
> `main` stays as the POS-only backup branch.

---

## Running both apps locally

```bash
# from project root
php artisan serve           # serves both POS and storefront on http://127.0.0.1:8000
```

Then in your browser:

| URL                                                     | What you see                |
| ------------------------------------------------------- | --------------------------- |
| `http://127.0.0.1:8000/`                                | POS landing page (existing) |
| `http://127.0.0.1:8000/admin/branch/select`             | POS admin login flow        |
| `http://127.0.0.1:8000/shop`                            | Storefront home             |
| `http://127.0.0.1:8000/shop/shop`                       | Catalog                     |
| `http://127.0.0.1:8000/shop/product/<slug>`             | Product detail              |
| `http://127.0.0.1:8000/shop/cart`                       | Cart                        |
| `http://127.0.0.1:8000/shop/login`                      | Customer login              |
| `http://127.0.0.1:8000/shop/register`                   | Customer register           |
| `http://127.0.0.1:8000/shop/account` (logged in)        | Customer dashboard          |
| `http://127.0.0.1:8000/admin/brands` (POS staff only)   | Manage brands               |
| `http://127.0.0.1:8000/admin/banners` (POS staff only)  | Manage banners              |

The storefront mounts at `/shop/...` in dev because both apps share the same host.
In **production** set `SHOP_DOMAIN=almufeed.com.pk` in `.env` and the storefront will
auto-mount at the root of that domain (the `/shop` prefix drops away).

```dotenv
# .env (production)
SHOP_DOMAIN=almufeed.com.pk
SHOP_PREFIX=
```

---

## Hosting setup (production cPanel)

The new domain `almufeed.com.pk` should serve from the **same `public/`** folder as
`pos.almufeed.com.pk`. Two options:

1. **cPanel → Domains → Manage** → set `almufeed.com.pk`'s document root to
   `/home/almufeed/domains/pos.almufeed.com.pk/public_html/public`.
2. Or symlink: `ln -s ~/domains/pos.almufeed.com.pk/public_html/public/* ~/public_html/`

Then in `.env` set `SHOP_DOMAIN=almufeed.com.pk` and `SHOP_PREFIX=` (empty).

---

## Schema additions (idempotent migrations)

| Table | Extension |
| --- | --- |
| `customers` | `password`, `email_verified_at`, `remember_token`, `last_login_at`, `avatar` |
| `categories` | `slug`, `photo`, `parent_id`, `sort_order`, `is_active`, `is_featured` |
| `products` | `slug`, `summary`, `gallery` (JSON), `brand_id`, `is_featured`, `show_on_website`, `condition_label`, `meta_title`, `meta_description`, `avg_rating`, `review_count` |
| `orders` | `order_source` enum, `customer_email`, `shipping_*`, `coupon_code`, `coupon_discount`, `online_payment_status`, `online_payment_ref`, `order_notes_customer` |

New tables: `brands`, `banners`, `coupons`, `cart_items`, `wishlists`, `product_reviews`.

---

## Auth model

A second guard `customer` (in `config/auth.php`) sits beside the existing `web` guard.
Customers log in on the storefront; staff log in to the POS — sessions are isolated
unless explicitly merged.

When a guest with items in their cart logs in, `CartService::mergeGuestIntoCustomer()`
moves the rows under their `customer_id` automatically.

---

## What's wired so far (Phase 1 cut)

- ✅ Customer signup / login / logout / profile / change password
- ✅ Public catalog: home, shop, category, brand, search, product detail
- ✅ Cart (DB for customers, session for guests) with add / update / remove (AJAX)
- ✅ Mini-cart drawer in header
- ✅ Wishlist (toggle from product cards, dedicated page)
- ✅ Reviews (1-5 stars + comment, auto-aggregated `avg_rating`)
- ✅ Checkout (address, dispatch picker, COD or bank transfer, no online payment yet)
- ✅ My Orders + order detail with status timeline
- ✅ Admin: Brand CRUD + Banner CRUD (under sidebar **Storefront** group)
- ✅ Coupons honoured at cart + checkout (`WELCOME10` is seeded)
- ✅ AL MUFEED TRADERS logo from old site, navy/cyan/gold palette
- ✅ Animations: hero gradient drift, reveal-on-scroll, page-transition, hover lifts,
  prefetch-on-hover for snappy navigation, lazy images, View Transitions API

## Skipped on purpose (will be added later)

- Online payment gateways (JazzCash / Easypaisa) — currently COD + Bank Transfer only.
- Blog / CMS pages — old site had it; we're focusing on commerce.
- Social login — old credentials were stale and add maintenance burden.
- PWA / service worker — premature optimisation.

---

## Quick scripts

```bash
# seed sample brands, banners, coupon, flag products visible
php artisan db:seed --class=StorefrontSeeder --force

# clear caches when you change something
php artisan view:clear && php artisan route:clear && php artisan config:clear
```

---

## Branch operations

```bash
# all storefront work happens here
git checkout feature/website

# main stays as POS-only backup
git checkout main      # only switch back to merge or to roll back
```
