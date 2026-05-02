# Janeth's Business – Inventory & Sales System

A lightweight PHP + MySQL web app for managing daily inventory, distribution, and sales.

## 📁 Project Structure

```
janeth-chicken-business/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php       # Login, logout, register
│   │   ├── ProductController.php    # All inventory API logic (was janeth.php)
│   │   └── UserController.php       # Admin user management
│   ├── models/
│   │   ├── Database.php             # PDO singleton
│   │   ├── User.php                 # User + registration request queries
│   │   └── Product.php              # Products, inventory, analytics queries
│   └── helpers/
│       └── helpers.php              # send(), requireAuth(), validDate()
├── config/
│   └── database.php                 # Reads credentials from .env
├── database/
│   └── schema.sql                   # Full MySQL schema + sample data
├── public/                          # Web root (point Apache here)
│   ├── index.php                    # Front controller + autoloader
│   ├── login.php                    # Login page
│   ├── register.php                 # Registration request page
│   ├── logout.php                   # Session destroy + redirect
│   ├── janeth.php                   # Backward-compat alias → api.php
│   ├── api.php                      # Inventory REST API entry point
│   ├── janeth-input.php             # Daily entry page
│   ├── janeth-dashboard.php         # Dashboard + analytics
│   └── assets/
│       └── js/theme.js              # Light/dark mode toggle
├── admin/
│   ├── bootstrap.php                # Auth check + $pdo setup for admin pages
│   ├── products.php                 # Product management (admin only)
│   └── users.php                    # User management (admin only)
├── routes/
│   └── web.php                      # Route definitions
├── storage/                         # Logs, uploads (gitignored)
├── .env                             # DB credentials (never commit this)
├── .htaccess                        # Routing + security
└── README.md
```

## 🚀 Setup

### 1. Prerequisites
- XAMPP / WAMP / MAMP (Apache + MySQL + PHP 8.1+)

### 2. Installation

1. Copy folder to your web server root:
   - XAMPP: `C:\xampp\htdocs\janeth-chicken-business\`

2. Start Apache and MySQL.

3. Import the database:
   - Open phpMyAdmin → import `database/schema.sql`

4. Configure credentials in `.env`:
   ```
   DB_HOST=localhost
   DB_NAME=inventory_system
   DB_USER=root
   DB_PASS=
   ```

5. **Fix default passwords** — run once in your browser:
   ```
   http://localhost/janeth-chicken-business/fix_passwords.php
   ```
   Then delete `fix_passwords.php`.

### 3. Access

| Page | URL |
|------|-----|
| Login | `http://localhost/janeth-chicken-business/public/login.php` |
| Daily Entry | `http://localhost/janeth-chicken-business/public/janeth-input.php` |
| Dashboard | `http://localhost/janeth-chicken-business/public/janeth-dashboard.php` |
| Product Admin | `http://localhost/janeth-chicken-business/admin/products.php` |
| User Admin | `http://localhost/janeth-chicken-business/admin/users.php` |

**Default credentials after running fix_passwords.php:**
- `admin` / `admin123`
- `staff1` / `staff123`

## 🏗️ Architecture

The app uses a simple MVC-style layout without Composer or a framework:

- **Front controller** (`public/index.php`) registers a PSR-4-style autoloader and loads `routes/web.php`
- **Controllers** handle HTTP logic and delegate to models
- **Models** wrap all database queries (PDO)
- **Admin pages** use `admin/bootstrap.php` for auth + `$pdo` access
- **`public/janeth.php`** is a backward-compatible alias so existing fetch() calls don't break

## 🔄 API Endpoints

All via `public/api.php` (or the `janeth.php` alias). Requires session auth.

| Method | Query / Body | Description |
|--------|-------------|-------------|
| GET | `?products=1&page=input\|dashboard\|all` | List products |
| GET | `?date=YYYY-MM-DD&for=input\|dashboard` | Records + stock entries for date |
| GET | `?analytics=1&from=…&to=…` | Sales analytics |
| GET | `?expenses=YYYY-MM-DD` | Daily expenses |
| GET | `?liquidation=YYYY-MM-DD` | Liquidation record |
| POST | `{date, records}` | Save inventory records |
| POST | `{save_expense, …}` | Add expense |
| POST | `{save_liquidation, …}` | Save liquidation |
| POST | `{add_product, …}` | Add product (admin) |

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| 404 on API calls | Ensure `public/` is the web root, or adjust URL prefix in `.htaccess` |
| Database connection failed | Check `.env` credentials and that MySQL is running |
| Login fails | Run `fix_passwords.php` once to set correct bcrypt hashes |
| `require_once` errors | Check PHP version is 8.1+ (`str_starts_with` used in autoloader) |

## 📝 License

MIT – free for internal business use.
