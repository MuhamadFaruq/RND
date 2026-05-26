# DUNIATEX RND Production Management System

![DUNIATEX Red](https://img.shields.io/badge/Brand-DUNIATEX%20Red-%23ED1C24)
![Laravel](https://img.shields.io/badge/Laravel-12.x-red)
![Livewire](https://img.shields.io/badge/Livewire-4.x-blue)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-38B2AC)
![Status](https://img.shields.io/badge/Status-Complete-green)

A comprehensive production management and monitoring system designed specifically for DUNIATEX RND. This application streamlines the logging process across 11 production divisions and provides management with real-time monitoring of order progress and technical deviations.

---

## 🚀 Key Features

### 1. Universal LogBook Form
A dynamic entry point for operators to log production data.
- **Division-Specific Fields**: Inputs adapt automatically to the selected division (e.g., Knitting, Dyeing, Stenter).
- **SAP Integration**: Real-time lookup of order details (Customer, Color, Targets) directly via SAP Number.
- **Marketing Targets Reference**: Displays read-only target values (Width, GSM) for operator comparison.
- **Verification Modal**: Built-in confirmation step to ensure data accuracy before submission.

### 2. Master Monitoring Dashboard
A high-density command center for managers and admins.
- **Real-Time Tracking**: Visualizes the status of every order using color-coded badges.
- **Auto-Deviation Detection**: Automatically highlights technical values (Lebar/GSM) in **RED** if they deviate by more than 5% from the target.
- **Lead Time Analysis**: Tracks aging of orders and flags overdue items (>3 days).
- **Smart Filters**: Search by SAP Number, Customer Name, or Article Number with status-based filtering.
- **Auto-Refresh**: Live data updates every 10 seconds for a "Live Board" experience.

### 3. Professional UI/UX
- **Branding**: Fully customized with DUNIATEX Red (`#ED1C24`) identity.
- **Responsive Design**: Optimized for Desktop and Tablet usage in production environments.
- **Security**: Mandatory SweetAlert2 logout verification and 2-hour session timeout.

---

## 🛠 Tech Stack

- **Backend**: Laravel 12 (PHP 8.3+)
- **Frontend**: Livewire 4 (Volt) & Alpine.js
- **Styling**: TailwindCSS 4
- **Architecture**: Service-Repository Pattern
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **Icons**: Heroicons & Emoji-based Statuses

---

## 📂 Project Structure

- `app/Services/ProductionService.php`: Core business logic and status transition management.
- `app/Repositories/`: Optimized data queries with anti-N+1 eager loading.
- `app/Livewire/`: UI components for Admin, Marketing, and Operator roles.
- `app/Enums/OrderStatus.php`: Centralized management of production statuses.
- `resources/css/mkt-theme.css`: Custom DUNIATEX branding and theme styles.

---

## ⚙️ Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-repo/rnd-final.git
   cd rnd-final
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate --seed
   ```

5. **Build Assets**
   ```bash
   npm run build
   ```

6. **Run Server**
   ```bash
   php artisan serve
   ```

---

## 📊 Supported Divisions

The system manages production logs across the following stages:
1. **Marketing** (Order Entry)
2. **Knitting** (Rajutan)
3. **SCR/Dyeing** (Pewarnaan)
4. **Relax Dryer**
5. **Compactor**
6. **Heat Setting**
7. **Stenter**
8. **Tumbler**
9. **Fleece**
10. **Pengujian**
11. **QE** (Final Quality Evaluation)

---

## 🛡 Security & Maintenance

- **Maintenance Mode**: Accessible via `php artisan down`. Super Admins can bypass using the secret key configured in `bootstrap/app.php`.
- **Session**: Automatic expiration after 120 minutes of inactivity.
- **Audit Logs**: All production activities are logged for traceability.

---

© 2026 DUNIATEX RND System. All Rights Reserved.
