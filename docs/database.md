# MediTrack Database Architecture & Schema

This document details the database schema, table structures, column definitions, indexing strategies, and entity relationships for the MediTrack Medical Case Management system (@Jajal Medical).

---

## 1. Entity-Relationship Diagram (ERD)

```
+-------------------------------------------------------+
|                        users                          |
+-------------------------------------------------------+
|  id                  : BIGINT UNSIGNED [PK, AUTO_INC] |
|  name                : VARCHAR(255)                   |
|  email               : VARCHAR(255) [UNIQUE]          |
|  email_verified_at   : TIMESTAMP [NULLABLE]           |
|  password            : VARCHAR(255)                   |
|  role                : VARCHAR(255) [INDEX]           |  (admin, viewer, engineer)
|  remember_token      : VARCHAR(100) [NULLABLE]        |
|  created_at          : TIMESTAMP                      |
|  updated_at          : TIMESTAMP                      |
+-------------------------------------------------------+
                           |
                           | 1 : N (One User creates Many Cases)
                           | Foreign Key: cases.created_by -> users.id
                           v
+-------------------------------------------------------+
|                        cases                          |
+-------------------------------------------------------+
|  id                  : BIGINT UNSIGNED [PK, AUTO_INC] |
|  case_number         : VARCHAR(255) [UNIQUE, INDEX]   |  (e.g., CASE-0001)
|  patient_reference   : VARCHAR(255)                   |  (e.g., PT-1004-9B)
|  surgeon_name        : VARCHAR(255)                   |  (e.g., Dr. Sarah Jenkins)
|  implant_type        : VARCHAR(255)                   |  (e.g., Cranial Titanium Mesh 3D)
|  status              : VARCHAR(255) [INDEX]           |  (Draft, Planning, In Review, Approved, Completed, Cancelled)
|  priority            : VARCHAR(255) [INDEX]           |  (Low, Medium, High)
|  surgery_date        : DATE [NULLABLE]                |
|  created_by          : BIGINT UNSIGNED [FK -> users]  |
|  created_at          : TIMESTAMP                      |
|  updated_at          : TIMESTAMP                      |
+-------------------------------------------------------+

+-------------------------------------------------------+
|               personal_access_tokens (Sanctum)        |
+-------------------------------------------------------+
|  id                  : BIGINT UNSIGNED [PK, AUTO_INC] |
|  tokenable_type      : VARCHAR(255)                   |
|  tokenable_id        : BIGINT UNSIGNED                |
|  name                : VARCHAR(255)                   |
|  token               : VARCHAR(64) [UNIQUE]           |
|  abilities           : TEXT [NULLABLE]                |
|  last_used_at        : TIMESTAMP [NULLABLE]           |
|  expires_at          : TIMESTAMP [NULLABLE]           |
|  created_at          : TIMESTAMP                      |
|  updated_at          : TIMESTAMP                      |
+-------------------------------------------------------+
```

---

## 2. Table Specifications

### 2.1 Table: `users`
Stores system accounts with assigned role-based access privileges.

| Column | Type | Nullable | Key / Index | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Primary Key | Auto-incrementing identifier. |
| `name` | `VARCHAR(255)` | No | None | Full name of the user. |
| `email` | `VARCHAR(255)` | No | Unique | Unique login email address. |
| `email_verified_at` | `TIMESTAMP` | Yes | None | Optional verification timestamp. |
| `password` | `VARCHAR(255)` | No | None | Bcrypt-hashed password. |
| `role` | `VARCHAR(255)` | No | Index | User role: `'admin'`, `'viewer'`, or `'engineer'`. Defaults to `'viewer'`. |
| `remember_token` | `VARCHAR(100)` | Yes | None | Session remember token. |
| `created_at` | `TIMESTAMP` | Yes | None | Creation timestamp. |
| `updated_at` | `TIMESTAMP` | Yes | None | Modification timestamp. |

### 2.2 Table: `cases`
Stores medical device implant tracking records across their clinical lifecycle.

| Column | Type | Nullable | Key / Index | Description |
| :--- | :--- | :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED` | No | Primary Key | Auto-incrementing identifier. |
| `case_number` | `VARCHAR(255)` | No | Unique Index | Unique clinical case identifier (e.g. `CASE-0001`). Indexed for fast lookup. |
| `patient_reference`| `VARCHAR(255)` | No | None | Anonymized patient reference code (e.g. `PT-1002-4A`). |
| `surgeon_name` | `VARCHAR(255)` | No | None | Name of the primary orthopedic / neuro surgeon. |
| `implant_type` | `VARCHAR(255)` | No | None | Specific implant hardware category or custom implant model. |
| `status` | `VARCHAR(255)` | No | Index | Lifecycle state (`Draft`, `Planning`, `In Review`, `Approved`, `Completed`, `Cancelled`). Indexed for filtering and dashboard metrics. |
| `priority` | `VARCHAR(255)` | No | Index | Clinical priority (`Low`, `Medium`, `High`). Indexed for triage and sorting. |
| `surgery_date` | `DATE` | Yes | None | Scheduled or completed surgery date. |
| `created_by` | `BIGINT UNSIGNED` | No | Foreign Key | References `users.id`. Restricted on deletion to preserve audit history. |
| `created_at` | `TIMESTAMP` | Yes | None | Record creation timestamp. |
| `updated_at` | `TIMESTAMP` | Yes | None | Record update timestamp. |

---

## 3. Relationships & Referential Integrity
1. **User &rarr; Cases (`1 : N`)**:
   - `User::hasMany(MedicalCase::class, 'created_by')`
   - `MedicalCase::belongsTo(User::class, 'created_by')`
   - Foreign Key constraint: `cases.created_by` references `users.id` with `onDelete('cascade')`.

2. **User &rarr; PersonalAccessTokens (`1 : N`)**:
   - Managed by Laravel Sanctum via `HasApiTokens` morph relationship for bearer token authentication.

---

## 4. Query Performance & Indexing Rationale
- **`case_number` Unique Index:** Ensures strict uniqueness across cases and guarantees $O(1)$ lookup times for search queries.
- **`status` Index:** Optimizes dashboard KPI aggregate queries (`SELECT count(*) WHERE status = ?`) and status filtering queries.
- **`priority` Index:** Accelerates priority filtering and urgency ordering.
- **`role` Index on `users`:** Optimizes role checks and user queries.
