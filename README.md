## Contents

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                             Table of Contents                              │
├──────────────────────────┬─────────────────────────────────────────────────┤
│ Section                  │ Description                                     │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Overview                 │ Platform overview and key features              │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Local Setup              │ Requirements and steps to run it locally        │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ API Structure            │ Request flow, modules, and endpoints            │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Design Decisions         │ Architecture and key technical choices          │
├──────────────────────────┼─────────────────────────────────────────────────┤
│ Libraries & Technologies │ Tools and packages used per layer               │
└──────────────────────────┴─────────────────────────────────────────────────┘
```
================================================================================

# Accrual Hub

> A multi-tenant SaaS backend for subscription management, with correct double-entry accounting and deferred revenue handling.

Each company (tenant) manages its own plans, customers, subscriptions, invoices, and payments in complete isolation from other companies. Built on **Laravel 13 / PHP 8.3 / PostgreSQL** with **Laravel Sanctum** for the REST API, plus a full web **UI**.


## Key Features

- **Multi-tenancy** with strict data isolation between companies.
- **Subscriptions** management: plans, customers, and subscriptions (CRUD).
- **Automated billing**: monthly invoice generation (Cron simulation) and payment recording.
- **Double-entry accounting** with deferred revenue and revenue recognition.
- **Financial reports**: Income Statement and Balance Sheet.
- **Token-based REST API** (versioned `v1`) alongside a web dashboard.

================================================================================

## Local Setup

Get a local instance running in a few steps. The flow below shows the order; each step is detailed in its own card underneath.

### Requirements

```text
╔══════════════════════════════════════════════════════════╗
║                       Requirements                       ║
╠══════════════════════════════════════════════════════════╣
║ • PHP 8.3                   • Composer                   ║
║ • Node.js & npm             • PostgreSQL 16+             ║
╚══════════════════════════════════════════════════════════╝
```

### Setup Flow

```text
┌────────────┐   ┌──────────────┐   ┌───────────────┐   ┌────────────┐
│ Clone Repo │──►│ Install Deps │──►│ Configure Env │──►│ PostgreSQL │──┐
└────────────┘   └──────────────┘   └───────────────┘   └────────────┘  │
┌────────────┐   ┌──────────┐   ┌────────────┐                          │
│ Run Server │◄──│ Frontend │◄──│ Migrations │◄────────────────────────┘
└────────────┘   └──────────┘   └────────────┘
```

### 📦 Step 1 · Clone & Install

```text
┌──────────────────────────────────────────────────────────┐
│  Step 1 · Clone & Install                                │
└──────────────────────────────────────────────────────────┘
```

Clone the repository and install the PHP dependencies with Composer.

```bash
git clone <repository-url> accrual-hub
cd accrual-hub
composer install
```

### ⚙️ Step 2 · Configure Environment

```text
┌──────────────────────────────────────────────────────────┐
│  Step 2 · Configure Environment                          │
└──────────────────────────────────────────────────────────┘
```

Copy `.env.example` to `.env`, generate the app key, then open `.env` and set your PostgreSQL credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

```bash
cp .env.example .env
php artisan key:generate
```

### 🗄 Step 3 · Database & Migrations

```text
┌──────────────────────────────────────────────────────────┐
│  Step 3 · Database & Migrations                          │
└──────────────────────────────────────────────────────────┘
```

Create a PostgreSQL database named `accrual-hub`, then run the migrations to build the schema.

```bash
createdb accrual-hub
php artisan migrate
```

### 🎨 Step 4 · Frontend Assets

```text
┌──────────────────────────────────────────────────────────┐
│  Step 4 · Frontend Assets                                │
└──────────────────────────────────────────────────────────┘
```

Install the Node packages and build the frontend assets with Vite.

```bash
npm install
npm run build
```

### 🚀 Step 5 · Run the Server

```text
┌──────────────────────────────────────────────────────────┐
│  Step 5 · Run the Server                                 │
└──────────────────────────────────────────────────────────┘
```

Start the local development server; the app is served at `http://localhost:8000`.

```bash
php artisan serve
```

### 🧪 Step 6 · Run Tests

```text
┌──────────────────────────────────────────────────────────┐
│  Step 6 · Run Tests                                      │
└──────────────────────────────────────────────────────────┘
```

Run the full test suite (Feature & Unit) to verify the installation.

```bash
php artisan test
```

> **Getting started with the API:** All routes live under `/api/v1`. Call `POST /api/v1/register` (to create a company) or `POST /api/v1/login` to obtain a token, then send it as `Authorization: Bearer <token>`. Import [accrual-hub.postman_collection.json](accrual-hub.postman_collection.json) into Postman/Apidog to try every endpoint.

================================================================================

## API Structure

All routes start with the `/api/v1` prefix. There are only two public routes (register and login); everything else is protected by an authentication layer, after which the Tenant Scope layer is applied before reaching the system modules. The diagram below shows the request flow from top to bottom.

### Request Flow

```text
                  ┌───────────────────────────┐
                  │        API Client         │
                  └─────────────┬─────────────┘
                                │  HTTPS · JSON · Bearer Token
                                ▼
                  ┌───────────────────────────┐
                  │      Prefix: /api/v1      │
                  └─────────────┬─────────────┘
                                │
                                ├───────────────►  PUBLIC  (no auth)
                                │                    • POST /register
                                │                    • POST /login
                                ▼
                  ┌───────────────────────────┐
                  │   Middleware Guard        │
                  │   auth:sanctum            │
                  │   role:company            │
                  └─────────────┬─────────────┘
                                ▼
                  ┌───────────────────────────┐
                  │   Tenant Scope            │
                  │   (Data Isolation)        │
                  │   404 on cross-tenant     │
                  └─────────────┬─────────────┘
                                ▼
                  ┌───────────────────────────┐
                  │   Application Modules     │
                  └─────────────┬─────────────┘
                                ▼
 ┌──────────────────────────────────────────────────────────────────┐
 │  plans          customers       subscriptions       payments       │
 │  invoices (CRUD + generate + void)                                 │
 │  revenue-recognition            accounts            journal-entries │
 │  reports (income-statement + balance-sheet)                        │
 │  dashboard      notifications   settings            activity-log   │
 └──────────────────────────────────────────────────────────────────┘
```

### Modules & Endpoints

| Module | Key Endpoints | Description |
| --- | --- | --- |
| **Auth** | `POST /register` · `POST /login` · `GET /me` · `POST /logout` | Register a new company, log in, and manage the token |
| **plans** | `GET/POST /plans` · `GET/PUT/DELETE /plans/{id}` | Manage subscription plans (CRUD) |
| **customers** | `GET/POST /customers` · `GET/PUT/DELETE /customers/{id}` | Manage customers (CRUD) |
| **subscriptions** | `GET/POST /subscriptions` · `GET/PUT/DELETE /subscriptions/{id}` | Link a customer to a plan (CRUD) |
| **invoices** | `GET/POST /invoices` · `GET/DELETE /invoices/{id}` · `POST /invoices/generate` | Invoices + automatic monthly generation + void |
| **payments** | `GET/POST /payments` · `GET/DELETE /payments/{id}` | Record payments against invoices |
| **revenue-recognition** | `GET /revenue-recognition` · `POST /revenue-recognition/recognize` | Recognize revenue (move deferred to earned) |
| **accounts** | `GET /accounts` | Chart of accounts and balances |
| **journal-entries** | `GET /journal-entries` · `GET /journal-entries/{id}` | Journal entries and their lines |
| **reports** | `GET /reports/income-statement` · `GET /reports/balance-sheet` | Income Statement and Balance Sheet |
| **dashboard** | `GET /dashboard` | Company KPIs and statistics |
| **notifications** | `GET /notifications` · `POST /notifications/read-all` · `GET /notifications/{id}` | Notifications |
| **settings** | `GET /settings` · `PUT /settings/{company,profile,password}` · `DELETE /settings/deactivate` | Company and account settings |
| **activity-log** | `GET /activity-log` | Company activity log |

### Request Flow Explained

1. **API Client:** The client sends the request over HTTPS in JSON, attaching the auth token in the `Authorization: Bearer <token>` header (except for register and login).
2. **Prefix `/api/v1`:** The unified entry point for all API routes, with explicit versioning (v1) to keep the interface stable and easy to evolve in the future.
3. **PUBLIC (no auth):** Only two routes are publicly available: `POST /register` to create a company and its owner, and `POST /login` to obtain a token.
4. **Middleware Guard:** All other routes pass through `auth:sanctum` (token verification) and `role:company` (restricting access to company accounts).
5. **Tenant Scope (Data Isolation):** After authentication, every query is scoped to the current user's tenant, so no company can see another company's data, and any attempt to access a resource outside the tenant returns `404`.
6. **Application Modules:** After clearing the previous layers, the request reaches the requested module (plans, customers, invoices, accounting, reports, etc.).

The full route definitions live in [routes/api.php](routes/api.php).

================================================================================

## Design Decisions

- **Service layer (Services):** Business logic is placed in dedicated services to reuse it across the web and the API without duplication: [LedgerService](app/Services/LedgerService.php), [BillingService](app/Services/BillingService.php), [RecognitionService](app/Services/RecognitionService.php), [ReportService](app/Services/ReportService.php).
- **Multi-Tenancy isolation:** Every query flows through the tenant relationships (`$tenant->plans()->findOrFail()`) from the base controller [ApiController](app/Http/Controllers/Api/V1/ApiController.php), so no company can see another company's data (accessing a resource outside the tenant returns 404).
- **Double-entry & chart of accounts:** A base chart of accounts is seeded automatically when a company registers: Cash (1000), Accounts Receivable (1100), Deferred Revenue (2000 - liability), Subscription Revenue (4000 - revenue), and every entry is verified to balance before posting.
- **Deferred revenue & recognition:** On invoice issuance: Dr Accounts Receivable / Cr Deferred Revenue. On payment receipt: Dr Cash / Cr Accounts Receivable. On revenue recognition (period end): Dr Deferred Revenue / Cr Subscription Revenue. Recognition is independent of payment status and is based on the end of the service period.
- **Authentication & versioning:** Authentication via Sanctum tokens, API versioning under `v1`, consistent responses through API Resources, and reuse of Form Requests for validation.
- **Reversibility:** Invoice and payment entries are reversible (void / reverse) to preserve ledger integrity on cancellation or deletion.

================================================================================

## Libraries & Technologies

The main libraries and technologies used in the project, grouped by layer:

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                          Core Technologies (Backend)                       │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PHP                  │ ^8.3          │ Core programming language           │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Framework    │ ^13.8         │ Core framework                      │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PostgreSQL           │ 16+           │ Database (required)                 │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Eloquent ORM         │ (in Laravel)  │ Database access and relationships   │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Sanctum      │ ^4.0          │ API authentication via tokens       │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Tinker       │ ^3.0          │ REPL for testing and debugging code │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                            Dev Tools                                       │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ PHPUnit              │ ^12.5         │ Testing framework (Feature & Unit)  │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Pint         │ ^1.27         │ Code style formatter                │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Pail         │ ^1.2          │ Tailing logs during development     │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Laravel Boost        │ ^2.4          │ Helper tooling for Laravel dev      │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ FakerPHP             │ ^1.23         │ Fake data for factories and tests   │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Mockery              │ ^1.6          │ Mocking objects in tests            │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Nuno Collision       │ ^8.6          │ Clear error reporting in terminal   │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```

```text
┌────────────────────────────────────────────────────────────────────────────┐
│                          Frontend                                          │
├──────────────────────┬───────────────┬─────────────────────────────────────┤
│ Technology / Library │ Version       │ Purpose                             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Vite                 │ ^8.0          │ Asset build and bundling tool       │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Tailwind CSS         │ ^4.0          │ Styling framework (Utility-First)   │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ laravel-vite-plugin  │ ^3.1          │ Vite integration with Laravel       │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ Chart.js             │ ^4.5          │ Charts in the dashboard             │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ GSAP                 │ ^3.15         │ Animations and transitions          │
├──────────────────────┼───────────────┼─────────────────────────────────────┤
│ concurrently         │ ^9.0          │ Running dev commands in parallel    │
└──────────────────────┴───────────────┴─────────────────────────────────────┘
```
