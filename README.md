# GiftLoop (PHP Backend + Frontend)

This project was upgraded from static HTML/JS to a PHP-backed app with REST-style endpoints. Frontend UI remains as-is; JavaScript now talks to the backend under `api/`.

## Requirements
- XAMPP (Apache + PHP + MySQL)
- PHP 8.0+

## Project structure
- `api/` — PHP backend (routes, DB, session)
  - `index.php` — front controller with routes
  - `config.php` — DB/app config
  - `bootstrap.php` — common bootstrapping, PDO, session
  - `util.php` — helpers (JSON input, password, etc.)
  - `schema.sql` — MySQL schema
  - `seed_products.php` — seed initial products
  - `.htaccess` — Apache rewrite to route `/GiftLoop/api/*` to `index.php`
- `js/api.js` — browser API client used by pages
- Existing `*.php` and `*.js` — updated to call backend

## Quick start (XAMPP on Windows)
1) Move this folder to `C:\xampp\htdocs\GiftLoop`

2) Create database and tables:
- Open phpMyAdmin: http://localhost/phpmyadmin
- Create database `giftloop` (utf8mb4)
- Import file `api/schema.sql`

3) Configure backend (if needed):
- Edit `api/config.php` and confirm:
  - DB user/password (default XAMPP: user `root`, empty password)
  - `app.base_path` is `/GiftLoop` (matches `http://localhost/GiftLoop`)

4) Seed products (optional):
- Visit `http://localhost/GiftLoop/api/seed_products.php` in browser once, or run via CLI

5) Ensure Apache rewrite is enabled:
- In XAMPP Control Panel: Apache Config → `httpd.conf` → enable `LoadModule rewrite_module`
- In `httpd.conf`, allow `.htaccess` overrides (`AllowOverride All`) for your htdocs directory
- Restart Apache

6) Open the app:
- Visit `http://localhost/GiftLoop/index.php`
- Register a user on `auth.php` (password is stored hashed)
- Optionally mark a user admin in DB: `UPDATE users SET is_admin=1 WHERE email='you@example.com';`

## API endpoints (base: `/GiftLoop/api`)
- `POST /auth/register` { email, password, firstName, lastName, birthDate }
- `POST /auth/login` { email, password }
- `POST /auth/logout`
- `GET /auth/me`

- `GET /products`
- `POST /checkout` { items: [{ id, price, quantity }] }

- `GET /gifts`
- `GET /gifts/{id}`
- `POST /gifts/send` { name, description, price, category, isSuper, recipientName, recipientEmail }
- `POST /gifts/accept` { giftId }
- `POST /gifts/reject` { giftId }

- `POST /subscription` { plan: basic|premium|business, price }

- `GET /admin/users` (admin only)
- `POST /gifts/delete` (admin only) { giftId }
- `POST /gifts/toggle` (admin only) { giftId }

## Notes
- Frontend pages continue to work; now they use `js/api.js`.
- River page still uses demo data for streaming visuals. Receiving a real gift requires visiting `receive-gift.php?giftId=ID` for a gift created via `send-gift`.
- Shop checkout persists an order. You can extend it to also create gifts per order items.
- Sessions are cookie-based; same-origin access at `http://localhost/GiftLoop` is recommended. 