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
- Sample staff profiles, the current budget year's plans and weekly reports, one row for each register 001–014, and prosecutor weekly matrices for Saja and Fofa. Toba has staff but no report for the current year, so the dashboard still shows an office that has not submitted.

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

## Staff workspace

Staff sign in at `/login`. Demo accounts use the password `password`. An inactive account is refused. After sign-in the sidebar shows only the modules that role may use.

| Page | Path | Who |
| --- | --- | --- |
| Dashboard | `/dashboard` | Every active staff role |
| Zone center | `/zone-center` | Zone roles |
| Woredas and towns | `/woredas` | Every active staff role, limited to the offices they may see |
| Registers 001–014, plans, staff, laws, community, and public case tracking | `/admin/...` | The role middleware on that module |

`office_head` and `office_plan_expert` see only their own organization. `zone_head` and `zone_plan_expert` see the zone and every woreda and town. Other staff based at the zone see that same family. The dashboard cards count prosecutors, public servants, their sum, and the woreda/city submission matrix for the current budget year, including the prosecutor weekly matrices. The laws total is the whole collection, because laws are not tied to one office.

`zone_head` and `office_head` approve or reject annual plans, weekly reports, strategic-focus rows, and the prosecutor weekly matrices. A form can only save a draft or a submission. Staff history stores `salary_history`, `performance_evaluations`, and `promotions` as rows that can be added and removed. Community posts and public case tracking can be published or hidden from the public portal.

## Prosecutor weekly matrices and export

The three prosecutor matrices live at `/admin/prosecutor-weekly`. Each section stores one row in `prosecutor_weekly_report_items.metrics`. Totals and the three rates (የማጥራት ምጣኔ, የመርታት ምጣኔ, የማስቀጣት ምጣኔ) are calculated in the browser as the numbers are typed, and the server stores the same result.

| Type | Sections |
| --- | --- |
| `civil` | መንግስት ከሳሽ, በፍ/ቤት ያሉ, ይግባኝ, አፈፃፀም, ውል ምዝገባ, የህግ ምክር, አቤቱታ |
| `criminal` | ም/መዝገብ ውሳኔ, በፍ/ቤት ያሉ, ይግባኝ, ማረፊያ ቤት 3x/ሳምንት እና ማረሚያ ቤት 1x/ወር, አቤቱታና ህግ ምክር |
| `document_registration` | የሰነዶች ህጋዊነት, ሲቪክ ማህበራት, ጠበቆችና ህግ ጉዳይ ፀሐፊዎች |

PDF export uses mPDF with `storage/fonts/NotoSansEthiopic-Regular.ttf`, so Ethiopic text stays readable. Excel export uses Laravel Excel. Both are available for staff profiles, registers 001–014, and the prosecutor matrices.

| Download | Path |
| --- | --- |
| One prosecutor matrix | `/admin/prosecutor-weekly/{report}/pdf` and `/excel` |
| The filtered prosecutor list | `/admin/prosecutor-weekly/export/pdf` and `/excel` |
| Staff profiles or a register | `/admin/exports/{key}/pdf` and `/excel` |

## Public portal

The public pages are Amharic, using Bootstrap 5.3 and Noto Sans Ethiopic. Dates use `Africa/Addis_Ababa` through `App\Helpers\EthiopianDateHelper`. The budget year runs from Hamle 1 to Sene 30.

| Page | Path |
| --- | --- |
| Home, with latest news, announcements, and a laws summary | `/` |
| Case tracking | `/cases-tracking` |
| Laws and download | `/laws`, `/laws/{law}/download` |
| Community, including public feedback | `/community`, `POST /community/feedback` |

Only published case trackings and community posts are shown. Staff routes can use the `role` middleware alias, which allows an active `super_admin` or an active user whose role is listed.
