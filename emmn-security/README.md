# EMMN Security Solutions – Supply Chain Risk Checker

Web application for assessing third-party supplier cyber security risk.

**Version:** 2.1.0 (upgraded – same design & purpose, made fully functional)

## What was upgraded

- Fixed broken config includes (filename case: `Database.php`, `helper.php`)
- Added proper bootstrap (`public_html/includes/config.php`)
- SQLite database by default (works without MySQL)
- Database schema + seed data installer
- Fixed header/footer HTML structure and navigation links
- Completed empty **Client** area (register, login, dashboard, logout)
- Fixed **Admin** login, dashboard, clients, messages, logout
- Contact form now saves messages to the database
- Homepage stats and high-risk list load from the database
- Renamed / completed JS (`assets/js/main.js`)
- Default admin account seeded

## Requirements

- PHP 8.0+ with PDO SQLite (or PDO MySQL)
- No separate database server needed when using SQLite

## Quick start (local)

```bash
cd "EMMN Security/public_html"
php -S localhost:8080
```

Open: http://localhost:8080

### Administrator access

The installer creates the initial administrator account from environment variables when available. Set `EMMN_ADMIN_USERNAME` and `EMMN_ADMIN_PASSWORD` before running `php config/install_db.php`; never use shared or default credentials in production.

### Client

1. Register at `/client/register.php`
2. Login at `/client/login.php`

## Database

- Default: SQLite file at `/home/workdir/artifacts/emmn_security.db`  
  (path set in `config/Database.php` → `SQLITE_PATH`)
- Re-run installer anytime:

```bash
php config/install_db.php
```

### Switch to MySQL

In `config/Database.php`:

1. Set `USE_SQLITE` to `false`
2. Set `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
3. Create empty database `emmn_security_db`
4. Run `php config/install_db.php`

## Project structure

```
EMMN Security/
├── config/           # App config, DB, auth, helpers
├── data/             # (optional local data)
├── public_html/      # Web root – point your server here
│   ├── admin/        # Admin panel
│   ├── client/       # Client portal
│   ├── assets/       # CSS, JS, images
│   └── includes/     # Header, footer, bootstrap
└── README.md
```

## Deploy notes

- Document root must be **`public_html`**
- If the site lives in a subfolder, set `SITE_URL` in `config/constants.php`  
  e.g. `define('SITE_URL', '/EMMN%20Security/public_html/');`
- Change the default admin password after first login
- Set `DEBUG_MODE` to `false` in production (`config/constants.php`)
