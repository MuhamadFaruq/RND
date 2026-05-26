# DUNIATEX RND System - Project Instructions

## 🏗 Architecture & Standards
- **Stack:** Laravel 12 + Livewire 4 (Volt) + TailwindCSS 4.
- **Pattern:** Pragmatic Clean Architecture (Service-Repository Pattern).
- **Service Layer:** `app/Services/ProductionService.php` contains all core business logic and status transitions.
- **Repository Layer:** `app/Repositories/` handles all Eloquent queries and data persistence.
- **Enums:** Use `App\Enums\OrderStatus` for all order status management.

## 🎨 UI/UX Guidelines
- **Primary Brand Color:** DUNIATEX Red (`#ED1C24`).
- **Responsive Strategy:** Use Mobile-First design. Prefer Card-Fallback layouts for tables on mobile/tablet.
- **Theme:** Supports Dark Mode via Alpine.js `themeManager`.
- **Status Colors:**
  - **Marketing:** Red (#ED1C24)
  - **Knitting:** Blue
  - **Dyeing/Warna:** Indigo
  - **Success/Completed:** Emerald Green

## ⚙️ Operational Workflows
- **Maintenance Mode:**
  - Activate via `php artisan down`.
  - Super Admins can bypass using the secret key in `bootstrap/app.php`.
  - Livewire bypass uses wildcard `/livewire-*/*` to ensure compatibility.
- **Session Management:** Automatically logs out after 2 hours (120 minutes) of inactivity.
- **Security:** Logout verification modal (SweetAlert2) is mandatory for all roles.

## 🚀 Performance
- **Anti N+1:** Always use Eager Loading (`with()`) in Repositories for relationships.
- **Build:** Always run `npm run build` after UI changes to update production assets.
