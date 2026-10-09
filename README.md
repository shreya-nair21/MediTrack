# MediTrack — Medical Implant Case Management System

> **Practical Assessment for Software Developer Intern**  
> **Company:** @Jajal Medical  
> **Design Theme:** Coinbase Institutional Design System ([DESIGN.md](DESIGN.md))  
> **Tech Stack:** Laravel 11, PHP 8.3, MySQL / SQLite, Blade, Laravel Sanctum, PHPUnit

---

## 1. Project Overview

**MediTrack** is a medical device implant tracking web application built for clinical teams, biomedical CAD engineers, and administrative leads. It provides full lifecycle visibility for patient-specific implants—from `Draft` and `Planning` through `In Review`, `Approved`, and `Completed` (or `Cancelled`).

### Key Features
- **Role-Based Access Control (RBAC):** Three distinct roles (`Admin`, `Viewer`, and bonus `Engineer`) enforced via native Laravel Policies (`CasePolicy`) on both Web UI and REST API.
- **5 Responsive Blade Screens:** Login/Logout, Metric KPI Dashboard, Case Directory (Search, Filter, Sort, Pagination), Reusable Case Form (with inline validation), and Comprehensive Case Details.
- **Coinbase Institutional Design Theme:** Minimalist pure-white canvas, soft-gray elevation bands, 24px container cards, 12px form inputs, and signature **Coinbase Blue (`#0052ff`)** 100px pill buttons.
- **Sanctum REST API:** Token-based API with standardized JSON envelopes (`{ success, data, message }`), strict status codes (`200`, `201`, `401`, `403`, `404`, `422`), and Postman/Thunder Client export.
- **Public Holiday API (Bonus):** Nager.Date API integration showing surgery date holiday alerts with graceful fallback.
- **Medical Compliance Traceability:** IEC 62304 / ISO 13485 compliant traceability matrix ([docs/traceability.md](docs/traceability.md)).

---

## 2. Test Login Credentials (Dummy Accounts)

The database seeder automatically provisions test credentials for all user roles:

| Role | Email Address | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@jajalmedical.com` | `Password123!` | Full permissions (View, Create, Edit, Status changes, Delete cases) |
| **Viewer** | `viewer@jajalmedical.com` | `Password123!` | Read-only access (View case directory and details; deletion and edits blocked with 403) |
| **Engineer** *(Bonus)* | `engineer@jajalmedical.com` | `Password123!` | Biomedical CAD access (Create, Edit, Change status; deletion blocked with 403) |

> [!NOTE]  
> The login screen also features 1-click credential auto-fill buttons to allow reviewers to immediately test role permissions.

---

## 3. Local Setup Instructions

### Prerequisites
- PHP 8.2 or 8.3 (with `pdo_sqlite`, `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`)
- Composer 2.x
- Node.js & npm (optional for asset rebuilds)

### Step-by-Step Installation
```bash
# 1. Clone repository
git clone https://github.com/your-username/meditrack.git
cd meditrack

# 2. Install PHP dependencies
composer install

# 3. Setup environment configuration
cp .env.example .env
php artisan key:generate

# 4. Run database migrations and seed dummy cases (22 cases + 3 users)
php artisan migrate:fresh --seed

# 5. Start the local development server
php artisan serve
```

Access the application in your browser at: **`http://localhost:8000`**

---

## 4. How to Run Automated Tests

The application features an automated test suite verifying all 6 mandatory requirements, bonus roles, and API authentication flows:

```bash
# Run all tests
php artisan test

# Run specifically the medical case management feature tests
php artisan test --filter=MedicalCaseManagementTest
```

### Verified Test Cases
1. `test_valid_login_succeeds` — Valid credentials authenticate and redirect to dashboard.
2. `test_invalid_login_is_rejected` — Incorrect credentials display clear error messages.
3. `test_guest_cannot_open_case_pages_or_apis` — Unauthenticated requests receive 302 redirect (web) or 401 Unauthorized (API).
4. `test_admin_can_create_a_case` — Admin creates case via web form and API.
5. `test_creating_a_case_with_duplicate_case_number_fails` — Duplicate case number rejected with 422 validation error.
6. `test_viewer_cannot_delete_a_case` — Server strictly blocks Viewer DELETE attempts with HTTP 403 Forbidden.
7. `test_engineer_can_update_but_cannot_delete` *(Bonus)* — Engineer can modify cases but cannot delete.
8. `test_api_login_and_logout_flow` — Bearer token issuance, API access, and token revocation.

---

## 5. REST API Documentation

All API responses follow the consistent JSON envelope:
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation description."
}
```

### Endpoints Summary

| Method | Endpoint | Authorization | Description | Status Codes |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/login` | Public | Obtain Sanctum Bearer token | `200`, `401`, `422` |
| `POST` | `/api/logout` | Bearer Token | Revoke active Sanctum token | `200`, `401` |
| `GET` | `/api/cases` | Bearer Token | Paginated case listing (`?page=1&status=Approved&search=Cranial`) | `200`, `401`, `403` |
| `POST` | `/api/cases` | Bearer Token (Admin / Engineer) | Create new case | `201`, `401`, `403`, `422` |
| `GET` | `/api/cases/{id}` | Bearer Token | Fetch single case details | `200`, `401`, `403`, `404` |
| `PUT` | `/api/cases/{id}` | Bearer Token (Admin / Engineer) | Update case details (ignores current ID for uniqueness) | `200`, `401`, `403`, `404`, `422` |
| `DELETE` | `/api/cases/{id}` | Bearer Token (Admin Only) | Permanently delete case | `200`, `401`, `403`, `404` |

### API Postman / Thunder Client Collection
Import the ready-to-test collection located at:  
👉 [`docs/postman_collection.json`](docs/postman_collection.json)

---

## 6. Assumptions and Known Limitations

1. **Patient Privacy & Anonymization:** In adherence to HIPAA / GDPR regulations, no real patient identifiers (PHI) are stored. Patient references use pseudo-anonymized codes (e.g. `PT-1002-4A`).
2. **Third-Party Holiday API Resilience:** The public holiday feature queries `https://date.nager.at/api/v3/PublicHolidays`. If the external API times out (3-second limit) or is unreachable, the system gracefully logs a warning and proceeds without breaking the case details view.
3. **Database Portability:** Configured with SQLite by default for instant zero-dependency testing, and completely compatible with MySQL 8.0+ by setting `DB_CONNECTION=mysql` in `.env`.
4. **Soft Deletes vs Hard Deletes:** Clinical records in this assignment undergo hard delete per the prompt specification; in an FDA Class III production device environment, soft deletes (`SoftDeletes`) and change-audit tables would be retained.

---

## 7. Medical Documentation & Architecture Links
- 🗄️ **Database Architecture & ERD:** [`docs/database.md`](docs/database.md)
- 📋 **Traceability Matrix (IEC 62304):** [`docs/traceability.md`](docs/traceability.md)
- ☁️ **AWS Cloud Deployment Guide:** [`docs/deployment.md`](docs/deployment.md)
- 🎨 **Coinbase Design System Specification:** [`DESIGN.md`](DESIGN.md)
- 📮 **REST API Collection:** [`docs/postman_collection.json`](docs/postman_collection.json)
