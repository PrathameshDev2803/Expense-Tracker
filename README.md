# Expense Tracker

A modern, responsive, and secure PHP & MySQL personal financial management application. Tracks expenses and income, analyzes spending patterns, monitors recurring subscriptions, visualizes financial goals, and manages personalized user settings.

---

## Table of Contents

1. [Features](#features)
2. [Local Setup Instructions](#local-setup-instructions)
3. [Environment Configuration (.env)](#environment-configuration-env)
4. [InfinityFree Hosting Setup](#infinityfree-hosting-setup)
5. [GitHub Actions CI/CD Deployment](#github-actions-cicd-deployment)
6. [Required GitHub Repository Secrets](#required-github-repository-secrets)
7. [Security & Deployment Safeguards](#security--deployment-safeguards)
8. [Author](#author)

---

## Features

- **Transaction Management**: Add, categorize, filter, and track daily income and expense records.
- **Dynamic Categories / Genres**: Customize genres and types for comprehensive categorization.
- **Financial Goals Tracker**: Set target amounts, track progress visualizers, and monitor deadlines.
- **Subscription Detection**: Automatically identify and monitor recurring payment commitments.
- **Analytics & Summary**: Interactive dashboard displaying financial metrics and date-filtered summaries.
- **Account Security**: Session authentication, password strength analysis (via `zxcvbn-php`), and remember-me token handling.
- **Centralized Database Engine**: Single environment-aware MySQLi layer supporting strict error handling, `utf8mb4` charset, and `Asia/Kolkata` (+05:30) timezone.

---

## Local Setup Instructions

### Prerequisites

- **PHP 8.2 or 8.3**
- **Composer 2.x**
- **MySQL / MariaDB** (e.g., via XAMPP, WampServer, or native service)
- **Apache Web Server** with `mod_rewrite` enabled

### 1. Clone the Repository

```bash
git clone https://github.com/PrathameshDev2803/Expense-Tracker.git
cd Expense-Tracker
```

### 2. Install Composer Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Initialize Local Database

1. Start Apache and MySQL in your XAMPP Control Panel.
2. Open phpMyAdmin (`http://localhost/phpmyadmin/`) or MySQL CLI:
   ```sql
   CREATE DATABASE expensetracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the database schemas in order:
   - `sql/schema.sql`
   - `sql/goals_schema.sql`

### 4. Create Local `.env` File

Copy the template:

```bash
cp .env.example .env
```

*(On Windows PowerShell: `Copy-Item .env.example .env`)*

Verify your local configuration values match your local database settings (defaults match standard XAMPP):

```ini
APP_ENV=development

DB_HOST_DEVELOPMENT=localhost
DB_PORT_DEVELOPMENT=3306
DB_NAME_DEVELOPMENT=expensetracker
DB_USER_DEVELOPMENT=root
DB_PASS_DEVELOPMENT=
```

### 5. Run the Application

Place the project in your web directory (e.g., `C:\xampp\htdocs\expensetracker`) and access:

```
http://localhost/expensetracker/index.php
```

---

## Environment Configuration (.env)

The application uses an environment-aware database configuration in `php/db.php`. A single `.env` file handles both local development and live production via the `APP_ENV` variable.

### Supported Environments

- `APP_ENV=development`: Selects `DB_*_DEVELOPMENT` variables.
- `APP_ENV=live`: Selects `DB_*_LIVE` variables.

### Environment Variable Reference

```ini
# Application Environment (exactly: development | live)
APP_ENV=development

# Development Database (Local XAMPP / MariaDB defaults)
DB_HOST_DEVELOPMENT=localhost
DB_PORT_DEVELOPMENT=3306
DB_NAME_DEVELOPMENT=expensetracker
DB_USER_DEVELOPMENT=root
DB_PASS_DEVELOPMENT=

# Live Database (InfinityFree / Production credentials)
DB_HOST_LIVE=
DB_PORT_LIVE=3306
DB_NAME_LIVE=
DB_USER_LIVE=
DB_PASS_LIVE=
```

> **Security Rule**: The `.env` file contains sensitive credentials and must **never** be committed to Git. `.gitignore` is preconfigured to exclude `.env` and all `.env.*` files while preserving `.env.example`.

---

## InfinityFree Hosting Setup

1. **Log in to InfinityFree**: Open your **Client Area** and navigate to your account's **Control Panel (vPanel)**.
2. **Create MySQL Database**:
   - Go to **MySQL Databases** in vPanel.
   - Create a database (e.g., `expensetracker`).
   - Note the generated details:
     - **MySQL Host Name**: (e.g., `sqlXXX.infinityfree.com` or `sqlXXX.epizy.com`)
     - **MySQL Database Name**: (e.g., `if0_XXXXXXXX_expensetracker`)
     - **MySQL User Name**: (e.g., `if0_XXXXXXXX`)
     - **MySQL Password**: (Your vPanel account password)
3. **Import Database Schema**:
   - Open **phpMyAdmin** from vPanel.
   - Select your new database.
   - Import `sql/schema.sql` and `sql/goals_schema.sql`.
4. **Create Live `.env` on InfinityFree**:
   - Using the vPanel **File Manager**, navigate into `/htdocs/`.
   - Create a file named `.env`.
   - Fill in your live production credentials:
     ```ini
     APP_ENV=live

     DB_HOST_LIVE=sqlXXX.infinityfree.com
     DB_PORT_LIVE=3306
     DB_NAME_LIVE=if0_XXXXXXXX_expensetracker
     DB_USER_LIVE=if0_XXXXXXXX
     DB_PASS_LIVE=your_vpanel_password
     ```
   - Save the file.
5. **Security**: The included `.htaccess` file automatically prevents direct HTTP access to `.env`, `.git`, `sql/`, and Composer metadata.

---

## GitHub Actions CI/CD Deployment

The repository includes `.github/workflows/deploy.yml`. It validates the PHP project on every push to `master` and supports optional automatic deployment to InfinityFree using explicit FTPS.

InfinityFree staff have confirmed that GitHub Actions-based uploads are allowed, provided they do not create unreasonable FTP-server load. See the [InfinityFree support confirmation](https://forum.infinityfree.com/t/using-github-actions-for-automatic-upload-to-infinityfree/111008/3).

### Pipeline

1. Check out the `master` branch.
2. Set up PHP 8.3, required extensions, and Composer.
3. Validate Composer configuration and install production dependencies.
4. Fail if a real `.env` file is tracked in Git.
5. Run PHP syntax checks on application files.
6. Deploy over explicit FTPS only when the deployment gate is enabled.

### First-time deployment gate

The workflow intentionally skips the FTP upload until the GitHub repository variable `INFINITYFREE_DEPLOY_ENABLED` is set to `true`. Keep it unset or set it to `false` while the custom domain and FTP destination folder are being configured. CI validation still runs normally.

### Required GitHub Actions secrets

Configure these under **Settings → Secrets and variables → Actions → Secrets**:

| Secret | Purpose |
|---|---|
| `INFINITYFREE_FTP_HOST` | FTP hostname from InfinityFree FTP Details (normally `ftpupload.net`) |
| `INFINITYFREE_FTP_USERNAME` | FTP username shown in FTP Details |
| `INFINITYFREE_FTP_PASSWORD` | FTP password shown in FTP Details |
| `INFINITYFREE_FTP_REMOTE_DIR` | Exact remote document root for the custom domain, ending in `/` |

Configure the repository variable under **Settings → Secrets and variables → Actions → Variables**:

| Variable | Value |
|---|---|
| `INFINITYFREE_DEPLOY_ENABLED` | `false` until the FTP destination is confirmed; then `true` |

Do not use the account's temporary-domain directory by assumption. Confirm the custom domain's document root in InfinityFree File Manager before enabling uploads.

### Deployment safeguards

- Explicit FTPS on port 21 with strict certificate validation.
- `.env*`, `.git*`, `.github/`, editor files, SQL scripts, README, tests, logs, and the InfinityFree backup folder are excluded.
- Composer dependencies are generated in the CI runner and the `vendor/` directory is deployed.
- `dangerous-clean-slate` is disabled; the workflow will not wipe the whole remote document root.
- A concurrency lock prevents overlapping production deployments.
- Only pushes to `master` trigger the deployment workflow; pull requests do not deploy.

## Author

**Prathamesh Jadhav**  
Full-Stack Developer focused on PHP/Laravel, React and practical web applications.
