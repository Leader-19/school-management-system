# Student Management System

A school management web application built with **plain PHP** (no framework) using a light MVC pattern: Controllers, Services, Repositories, Models, and plain PHP views. Data is stored in **MySQL/MariaDB** via PDO with prepared statements.

## Features

- **Dashboard** — overview after sign-in
- **Students** — CRUD, search, pagination, and bulk import from Excel/CSV
- **Classes** — create/edit/delete classes, assign a homeroom teacher, view class students & subjects
- **Teachers** — admin management of teacher accounts
- **Attendance** — take attendance per class per session, view a student's history
- **Assignments** — create assignments per class/subject, file attachments, student submissions with uploads, grading & feedback
- **Grades** — record and view scores per student/subject/semester
- **Roles & permissions** — admin / teacher / student roles, permission-checked routes
- **Security** — bcrypt passwords, CSRF tokens on POST forms, security headers middleware, PDO prepared statements, uploads served through a permission-checked route (never directly)

## Requirements

- PHP 8+ with the `pdo_mysql` extension
- MySQL 5.7+ / MariaDB 10.3+
- Apache with `mod_rewrite` (XAMPP/WAMP), **or** the PHP built-in server

## Quick Start

### 1. Get the code

Clone or copy the project into your web root, e.g. XAMPP:

```
C:\xampp\htdocs\student-management-system
```

### 2. Create the database

Run the SQL scripts **in this order** (phpMyAdmin → SQL tab, or the `mysql` CLI):

```bash
mysql -u root < database/schema.sql              # creates school_management + schema + seed data
mysql -u root school_management < database/migrate_attendance.sql   # attendance tables
mysql -u root school_management < database/migrate_fixes.sql        # repair/upgrade fixes
```

> `schema.sql` already includes seed data (roles, permissions, 3 users, 2 classes, 3 subjects, 1 student).

### 3. Create the login accounts

```bash
mysql -u root school_management < database/create_users.sql
```

Default accounts (password for all: **`12345678`**):

| Username   | Role     | Email                   |
|------------|----------|-------------------------|
| `admin`    | Admin    | admin@school.local      |
| `teacher1` | Teacher  | teacher1@school.local   |
| `student1` | Student  | student1@school.local   |

> Passwords are stored as **bcrypt hashes** — inserting plain text into `users.password` will never allow sign-in. Generate your own hash with:
>
> ```bash
> php -r "echo password_hash('mypassword', PASSWORD_DEFAULT), PHP_EOL;"
> ```

### 4. Configure the database connection

Edit `config/Database.php` to match your environment (defaults are for a stock XAMPP):

```php
private $host = 'localhost';
private $dbName = 'school_management';
private $username = 'root';
private $password = '';
```

### 5. Run the app

**Option A — XAMPP/Apache:** start Apache + MySQL, then open:

```
http://localhost/student-management-system/
```

**Option B — PHP built-in server** (no Apache needed):

```bash
php -S localhost:8000
```

Then open <http://localhost:8000> and sign in as `admin` / `12345678`.

### 6. (Optional) Debug mode

Errors are hidden in the browser and written to the PHP error log. To show them while developing, set the environment variable before starting the server:

```bash
APP_DEBUG=1 php -S localhost:8000
```

## Project Structure

```
├── index.php               # Front controller / bootstrap + autoloader
├── .htaccess               # Apache rewrite: all requests → index.php
├── Core/                   # Framework base classes
│   ├── App.php             #   Boots router & resolves the URL
│   ├── Router.php          #   Route matching + middleware pipeline
│   ├── Controller.php      #   view(), redirect(), checkPermission()
│   ├── Model.php           #   Base model (query(), findAll(), ...)
│   ├── Database (config/)  #   PDO singleton
│   ├── Auth.php            #   Session auth helpers
│   ├── Session.php         #   Flash messages, session helpers
│   ├── ViewHelper.php      #   CSRF field, empty states, formatting
│   └── MiddlewarePipeline.php
├── Routers/index.php       # The route table (method, path, controller, action, middleware)
├── Controllers/            # HTTP layer (ClassController, StudentController, ...)
├── Services/               # Business rules & validation
├── Repository/             # Data access (repositories wrap models)
├── Models/                 # Table models with SQL
├── Middleware/             # AuthMiddleware, PermissionMiddleware, CsrfMiddleware, SecurityHeadersMiddleware
├── Views/                  # Plain PHP templates (layouts/, partials/, per-module folders)
├── public/                 # CSS / JS assets
├── config/Database.php     # DB credentials
├── database/               # schema.sql + migrations + seed users
├── storage/                # Uploaded assignment/submission files (not public)
└── tools/                  # CLI smoke tests & verification scripts
```

**Request flow:** `index.php` → `App` → `Router` → middleware (`Auth` → `Permission` → `CSRF`) → `Controller` → `Service` → `Repository` → `Model` → view.

## Routing

All routes live in one table — `Routers/index.php`:

```php
['GET', '/class', 'ClassController', 'index', [AuthMiddleware::class]],
['GET', '/class/show/{id}', 'ClassController', 'show', [AuthMiddleware::class]],
['POST', '/class/create', 'ClassController', 'create', [AuthMiddleware::class, [PermissionMiddleware::class, ['manage_classes']], CsrfMiddleware::class]],
```

`{placeholder}` values are passed to the controller action in order. To add a page: add a route entry, a controller action, and a view under `Views/`.

## Roles & Permissions

Permissions are seeded by `schema.sql` and checked in routes with `PermissionMiddleware`, or in code with `Auth::hasPermission()`:

| Permission            | Admin | Teacher | Student |
|-----------------------|:-----:|:-------:|:-------:|
| manage_users          | ✅    |         |         |
| manage_students       | ✅    | ✅      |         |
| manage_classes        | ✅    | ✅      |         |
| create_assignments    | ✅    |         |         |
| view_assignments      | ✅    | ✅      | ✅      |
| submit_assignments    |       |         | ✅      |
| manage_grades         | ✅    | ✅      |         |
| view_own_grades       |       |         | ✅      |

## Tools (CLI checks)

With the dev server running (`php -S 127.0.0.1:8765 -t .`):

```bash
php tools/smoke_routes.php        # every route resolves, no 404/500
php tools/verify_login.php        # sign-in flow works
php tools/verify_middleware.php   # auth/permission/CSRF middleware
php tools/verify_student_create.php
php tools/verify_student_import.php
php tools/verify_teacher_attendance.php
php tools/verify_upload.php
php tools/verify_e2e.php          # end-to-end walkthrough
```

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Page is blank / shows nothing | Set `APP_DEBUG=1` and reload; check the PHP error log |
| "Database connection failed" | Check credentials in `config/Database.php`; confirm MySQL is running |
| 404 on every page (Apache) | Ensure `mod_rewrite` is enabled and `AllowOverride All` is set for the folder |
| 404 with PHP built-in server | Run the server from the project root: `php -S localhost:8000 -t .` |
| Cannot sign in | Re-run `database/create_users.sql` (resets the 3 accounts to password `12345678`) |
| Missing tables (attendance, uploads) | Run `database/migrate_attendance.sql` and `database/migrate_fixes.sql` |
