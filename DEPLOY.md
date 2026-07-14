# Deployment Guide: Local → Live Server

## Pre-Deployment Checklist

- [ ] Backup live database
- [ ] Backup live files
- [ ] Note down live `.env` values

## Step-by-Step

### 1. Backup Live Database (on server)
```bash
mysqldump -u root -p your_database_name > ~/backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Deploy Code (on server)
```bash
# Option A: If using Git
cd /path/to/pos
git pull origin main

# Option B: Upload files manually via FTP/SCP
# Upload all files EXCEPT: .env, storage/*, vendor/*
```

### 3. Install Dependencies (on server)
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

### 4. Keep Your Live .env
**DO NOT overwrite the live `.env` file.** It has your live database credentials.

### 5. Run the Safe Migration (on server)
This single migration handles EVERYTHING — creates new tables, adds new columns,
seeds lookup data, creates default branch, assigns all records to it.

```bash
php artisan migrate --path=database/migrations/2026_03_12_100000_safe_deploy_all_changes.php
```

### 6. Clear Caches (on server)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 7. Verify
- [ ] Visit the dashboard — should load with all existing data
- [ ] Check POS — payment and dispatch methods should appear
- [ ] Check Products — all existing products should be visible
- [ ] Check Customers — all existing customers should be visible
- [ ] Check Orders — all existing orders should be intact
- [ ] Check Settings page — payment/dispatch methods visible

## What This Migration Does

1. **Creates 14 new tables**: branches, payrolls, attendance_sessions, units,
   credit_ledgers, credit_transactions, ledger_entries, ledger_accounts,
   ledger_account_entries, branch_product_stock, supplier_payments,
   payment_methods, dispatch_methods

2. **Adds columns to existing tables**: orders (dispatch, delivery, balance),
   products (prices, weight, unit, branch), customers (type, credit, branch),
   payments (credit fields), users/employees/suppliers/categories (branch_id)

3. **Creates default branch** "Almufeed Saqafti Markaz" and assigns ALL
   existing records to it

4. **Seeds lookup tables**: payment methods, dispatch methods, units

5. **Adds permissions**: credit, branches, ledger management

## Rollback (if something goes wrong)
```bash
# Restore from backup
mysql -u root -p your_database_name < ~/backup_YYYYMMDD_HHMMSS.sql
# Revert code to previous version
```

## Important Notes

- All existing data (products, orders, customers, employees, suppliers) is PRESERVED
- The migration uses `hasTable` / `hasColumn` checks, so it's safe to run multiple times
- If a table or column already exists, it simply skips it
- No data is deleted or modified (only new columns/tables added)
