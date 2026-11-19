# የየም ዞን ፍትህ መምሪያ የድጅታላይዜሽን ሥርዓት

Yem Zone Justice Department Digitalization System. Phase 1 is the MySQL schema, Eloquent models, and the demo seed for the zone department, its woreda and town offices, work units, and the eight staff roles.

The database uses `utf8mb4_unicode_ci` so Amharic names and register text are stored as entered. Ethiopian calendar dates stay as text (`*_ec` and combined paper-form columns). They are not coerced into Gregorian `DATE` columns.

## Stack

- PHP 8.3+
- Laravel 13 (Laravel 11 or newer)
- MySQL 8 with `utf8mb4` / `utf8mb4_unicode_ci`

## Local setup

Create the database, then install and migrate:

```sql
CREATE DATABASE yem_justice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'yem'@'localhost' IDENTIFIED BY 'yem_justice_local';
GRANT ALL PRIVILEGES ON yem_justice.* TO 'yem'@'localhost';
```

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
```

`.env.example` already points at that local database. Change the credentials if your MySQL user is different.

## What the seed creates

- Zone office: የየም ዞን ፍትህ መምሪያ (Yem Zone Justice Department)
- City office: Saja Town, the zone administrative center
- Woreda offices: Fofa, Deri Saja Zuria, and Toba
- Work units on the zone department and on every woreda and town office

Demo accounts all use the password `password`:

| Role | Email |
| --- | --- |
| super_admin | super.admin@yemjustice.test |
| zone_head | zone.head@yemjustice.test |
| zone_plan_expert | zone.plan@yemjustice.test |
| unit_coordinator | unit.coordinator@yemjustice.test |
| hr_expert | hr.expert@yemjustice.test |
| legal_expert | legal.expert@yemjustice.test |
| office_head | office.head@yemjustice.test |
| office_plan_expert | office.plan@yemjustice.test |

## Registers

Planning and community tables sit beside the paper registers:

| Code | Table | Court or subject |
| --- | --- | --- |
| 001 | `criminal_cases` | Woreda court (`court_level = woreda_court`) |
| 002 | `criminal_cases` | High court (`court_level = high_court`) |
| 003 | `criminal_appeals` | Criminal appeal |
| 004 | `civil_cases` | High court |
| 005 | `civil_cases` | Woreda court |
| 006 | `civil_appeals` | Civil appeal |
| 007 | `civil_executions` | Execution |
| 008 | `power_of_attorney_grants` | Power of attorney |
| 009 | `power_of_attorney_revocations` | Revocation |
| 010 | `contracts` | Contract registration |
| 011 | `legal_studies_disseminations` | Legal studies and dissemination |
| 012 | `civic_associations` | Civic associations |
| 013 | `attorneys` | Attorneys |
| 014 | `legal_secretaries` | Legal secretaries |

`reviewed_by`, `created_by`, `uploaded_by`, `updated_by`, `filled_by_expert`, `approved_by_expert`, and `served_by_expert` point at `users`. `assigned_prosecutor` stays a name, matching the paper form.

Plans, weekly reports, strategic-focus rows, and prosecutor weekly reports share the review status `draft`, `submitted`, `approved`, `rejected`. Gender columns use `male` and `female`. JSON columns (`salary_history`, `performance_evaluations`, `promotions`, `metrics`) cast to arrays. `is_active`, `is_published`, `is_renewed_this_year`, and `closed_indefinitely` cast to booleans.
