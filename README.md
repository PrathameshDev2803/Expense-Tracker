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

The repository includes automated CI/CD deployment configured at `.github/workflows/deploy.yml`.

### Deployment Pipeline Workflow

```
Push to master / Manual trigger
            │
            ▼
    Checkout Repository
            │
            ▼
    Setup PHP 8.3 & Composer
            │
            ▼
   Validate composer.json
            │
            ▼
 Install Composer Dependencies
   (--no-dev --optimize-autoloader)
            │
            ▼
    Run PHP Syntax Checks
 (All project PHP files, excluding vendor)
            │
            ▼
   Deploy via FTPS (Port 21)
   (airvzxf/ftp-deployment-action@v4.0.1)
```

### Triggers

- **Push to `master` branch**: Automatically validates and deploys updates to production.
- **Manual dispatch (`workflow_dispatch`)**: Trigger deployment on-demand from the GitHub Actions tab.

---

## Required GitHub Repository Secrets

Configure the following encrypted secrets in your GitHub repository under **Settings > Secrets and variables > Actions**:

| Secret Name | Description | Example Value |
| :--- | :--- | :--- |
| `INFINITYFREE_FTP_HOST` | FTP Hostname from InfinityFree FTP Details | `ftpupload.net` |
| `INFINITYFREE_FTP_USERNAME` | FTP Username from InfinityFree vPanel | `if0_12345678` or `epiz_12345678` |
| `INFINITYFREE_FTP_PASSWORD` | FTP / vPanel account password | *(Your hosting password)* |
| `INFINITYFREE_FTP_REMOTE_DIR` | Destination path on remote server | `/htdocs/` |

> **Note**: For addon or custom subdomains, set `INFINITYFREE_FTP_REMOTE_DIR` to your domain folder, e.g., `/yourdomain.com/htdocs/`.

---

## Security & Deployment Safeguards

1. **Production `.env` Protection**:
   - The deployment workflow excludes `.env*` from file transfers. Your live `.env` on InfinityFree is never overwritten during deployment.
2. **Non-Destructive Synchronization**:
   - `delete: "false"` is enforced in the FTP action. Existing server-side configuration, logs, and uploads are preserved and never deleted.
3. **Vendor Directory Deployed**:
   - Composer dependencies are generated during CI and deployed directly to `htdocs/vendor/`, eliminating the need for SSH or Composer on shared hosting.
4. **Excluded Assets**:
   - The transfer automatically excludes development clutter: `.git*`, `.github*`, `.vscode*`, `node_modules*`, `README.md`, `sql*`, and logs.
5. **Explicit FTPS (TLS)**:
   - File transfer connects via explicit FTPS on port 21 using `ftp:ssl-force true` to encrypt authentication and data channels.
6. **Concurrency Locks**:
   - `concurrency.group: production-deployment` prevents overlapping or conflicting simultaneous deployments.
7. **Branch Protection**:
   - Deployments only trigger from the authoritative `master` branch. Pull requests from forks are strictly prevented from triggering deployment.

---

## Author

**Prathamesh Jadhav**  
Full-Stack Developer focused on PHP/Laravel, React and practical web applications.
