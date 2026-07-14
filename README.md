# Salal Collection

E-commerce website with an admin panel for **Salal Collection** (Laravel 12, PHP 8.4, MySQL).
It is a website-only build of a shared retail platform: all point-of-sale, payroll,
cash, credit/khata, ledger, suppliers and purchase modules have been removed.

## What the admin manages
- **Inventory** — products, categories, units, stock overview / low-stock / logs
- **Orders** — online store orders (status, mark paid, dispatch slip, checklist)
- **Reports** — sales, profit / loss, top products, category & customer sales
- **Settings** — website settings, payment & dispatch methods, delivery slabs, and
  **Banners**, **Brands** and **Reviews** management

The public storefront is the site front door; the admin panel is at `/admin/...`
(login at `/login`).

## Local setup
    composer install
    npm install && npm run build          # only if you change CSS/JS
    cp .env.example .env                   # then set DB + APP details
    php artisan key:generate
    php artisan migrate --seed             # or import an existing DB dump
    php artisan storage:link
    php artisan serve

## Deployment
    git pull origin main
    composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan storage:link
    php artisan optimize:clear
    php artisan config:cache && php artisan route:cache && php artisan view:cache

public/build (compiled assets) is committed, so a plain git pull deploy works
without running npm run build on the server unless the front-end changed.
