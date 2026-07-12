# AssetFlow — Enterprise Asset & Resource Management System

Built from the **AssetFlow problem statement** for the Odoo Hackathon 2026.

## Features

- **Auth** — Employee signup only (no self-assigned admin). Admin promotes roles from Organization Setup.
- **Dashboard** — KPI cards, overdue returns, quick actions
- **Organization Setup** (Admin) — Departments, asset categories, employee directory & role promotion
- **Assets** — Register assets (auto tag AF-0001), search/filter, lifecycle status, history
- **Allocation & Transfer** — Conflict prevention, transfer workflow, returns
- **Resource Booking** — Time-slot booking with overlap validation
- **Maintenance** — Approval workflow, auto status updates
- **Audit Cycles** — Assign auditors, verify/missing/damaged, close cycle
- **Reports** — Utilization, maintenance frequency, department allocations, booking heatmap
- **Notifications & Activity Logs**

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP 8+ (REST API)
- **Database:** MySQL

## Requirements

1. [XAMPP](https://www.apachefriends.org/) (includes Apache, MySQL, PHP) — **recommended on Windows**
2. Or: PHP 8+ and MySQL installed separately

## Setup (XAMPP)

1. Copy the `AssetFlow` folder to `C:\xampp\htdocs\AssetFlow`
   - Or keep it in your project and configure Apache virtual host
2. Start **Apache** and **MySQL** from XAMPP Control Panel
3. Open in browser: `http://localhost/AssetFlow/install.php`
4. Click **Install Database** (default: host `127.0.0.1`, user `root`, no password)
5. Login at `http://localhost/AssetFlow/login.php`

### Default Admin Account

- **Email:** `admin@assetflow.com`
- **Password:** `admin123`

## Setup (PHP built-in server)

If PHP is in your PATH:

```powershell
cd "c:\Users\Mr. Joshi\Documents\Myyyy\hackathon-2026\AssetFlow"
php -S localhost:8080
```

Then open `http://localhost:8080/install.php`

> Note: PHP was not detected in your PATH during setup. Install XAMPP first.

## User Roles

| Role | Capabilities |
|------|-------------|
| **Admin** | Organization setup, audit cycles, full analytics |
| **Asset Manager** | Register/allocate assets, approve maintenance & transfers |
| **Department Head** | Department assets, approve transfers, book resources |
| **Employee** | View assigned assets, book resources, raise maintenance |

## Project Structure

```
AssetFlow/
├── api/index.php          # REST API (all routes)
├── assets/css/style.css   # UI styles + navbar
├── assets/js/             # api.js, app.js
├── config/database.php    # DB connection
├── database/schema.sql    # MySQL schema
├── includes/              # auth, helpers, layout
├── install.php            # One-click DB setup
├── login.php / signup.php
├── dashboard.php
├── organization.php
├── assets.php
├── allocation.php
├── booking.php
├── maintenance.php
├── audit.php
├── reports.php
└── notifications.php
```

## API Routes

All routes: `api/index.php?route=<route>`

Examples: `auth/login`, `dashboard`, `assets`, `allocations`, `bookings`, `maintenance`, `audits`, `reports`, `notifications`

## Hackathon Notes

- Acquisition cost is stored for reports only (not linked to accounting)
- Asset lifecycle states: Available, Allocated, Reserved, Under Maintenance, Lost, Retired, Disposed
- Booking overlap rule: `start_time < existing_end AND end_time > existing_start` → rejected
- Allocation conflict: cannot double-allocate; offers transfer request instead
