# Laravel Modular

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hatchyu/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/hatchyu/laravel-modular)
[![Total Downloads](https://img.shields.io/packagist/dt/hatchyu/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/hatchyu/laravel-modular)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

### The Architectural Backbone for Enterprise Laravel Applications

**Laravel Modular** is a high-performance, zero-boilerplate **Domain-Driven Design (DDD) 4-Layer Modular Architecture** and **Pragmatic CQRS** framework engineered for senior developers and enterprise teams building large-scale, mission-critical software.

Whether you are architecting an **ERP, CRM, Banking platform, Healthcare system, or Multi-domain enterprise SaaS**, this package delivers the strict architectural guardrails, automated boundary enforcement, and production-optimized tooling needed to scale your modular monolith with total confidence.

> [!TIP]
> **Looking for a Simpler Modular Setup?**  
> If you are building standard CRUD apps, MVPs, small-to-medium SaaS, or websites and want feature modularity without the ceremony of DDD layers, CQRS actions, and repository interfaces, check out our lightweight Laravel-native companion package:  
> 👉 **[`hatchyu/laravel-modular-lite`](https://github.com/rajeshmk/laravel-modular-lite)** (`composer require hatchyu/laravel-modular-lite`)

---

## Key Features

- 🚀 **Zero-Boilerplate Auto-Discovery:** Automatically discovers module configs, service providers, routes (`web.php` & `api.php`), database migrations, and namespaced views.
- 🏛️ **DDD 4-Layer Architecture:** Cleanly partitions modules into `Domain/`, `Application/`, `Interface/`, `Infrastructure/`, and `Database/`.
- ⚡ **Zero Runtime Filesystem Scanning:** Built-in `php artisan module:cache` compiles full discovery manifests, completely eliminating runtime filesystem syscalls in production.
- 🛠️ **Full-Featured Artisan CLI:** Generators for Domain Models, Policies, Enums, Events, CQRS Actions (writes), Queries (reads), DTOs (`Data`), Rules, Services, Thin Controllers, Requests, Resources, Jobs, Tests, Migrations, and Seeders.
- 🗂️ **Nested Sub-Namespace Support:** Seamlessly generate components into subdirectories (e.g. `V1/CreateOrderAction`, `Api/V2/OrderController`, `Relations/OrderItem`).
- 🔄 **Overwrite Protection & `--force`:** Standard `--force` option across all generator commands.
- 🏭 **Smart Factory Guesser:** Automatically resolves Eloquent model factories located inside `Modules\{Module}\Database\Factories`.
- 🩺 **Diagnostic Health Checks:** `php artisan module:check` validates PSR-4 mappings, directory permissions, and service provider readiness.
- 🔌 **Module Enable / Disable & Manifests:** Toggle individual modules on or off dynamically (`php artisan module:enable` / `module:disable`) via local `module.json` manifests.
- 🕸️ **Topological Dependency Boot Graph:** Declare module dependencies (`"dependencies": ["Billing"]`). Boots modules in strict topological order (Kahn's algorithm), guaranteeing dependencies are ready before dependents boot.
- 🛡️ **Fail-Safe Dependency Guardrails:** Runtime protection and CLI guardrails prevent broken states when dependencies are disabled or missing, with actionable repair instructions.
- 🔄 **Safe Module Renaming Propagation:** `php artisan module:rename` refactors directories, namespaces, providers, seeders, views, and updates dependency declarations across all other modules.
- 🧩 **Non-Invasive & Standards-Compliant:** Adheres to modern PHP 8.4+ and strict typing standards without vendor lock-in.

---

## Architecture Blueprint (DDD 4-Layer)

Each module (`modules/{ModuleName}/`) is structured into four explicit architectural layers plus database and delivery files:

```text
modules/{ModuleName}/
├── Domain/                         # Pure Ubiquitous Business Logic & Invariants
│   ├── Models/                     # Eloquent Entities & Models
│   ├── ValueObjects/               # Domain Value Objects
│   ├── Enums/                      # Domain Enums
│   ├── Events/                     # Domain Events (e.g., CustomerRegisteredEvent)
│   ├── Policies/                   # Authorization Rules & Gate Policies
│   └── Observers/                  # Model Observers
│
├── Application/                    # Use-Case Orchestration & CQRS
│   ├── Actions/                    # CQRS Write Commands (e.g., CreateCustomerAction)
│   ├── Queries/                    # CQRS Read Queries (e.g., ListCustomersQuery)
│   ├── Data/                       # Application Data Transfer Objects (e.g., CreateCustomerData)
│   ├── Services/                   # Application Orchestration Services
│   └── Rules/                      # Payload & Input Validation Rules
│
├── Interface/                      # External Delivery Channels
│   ├── Controllers/                # Ultra-Thin HTTP API & Web Controllers
│   │   ├── Api/V1/
│   │   └── Admin/
│   ├── Requests/                   # HTTP Form Request Validation & Query Params
│   ├── Resources/                  # JSON:API & Response Transformers
│   └── Console/Commands/           # Module-Specific Artisan CLI Commands
│
├── Infrastructure/                 # Message Services & External Integrations
│   ├── Jobs/                       # Asynchronous Queue Jobs
│   ├── Mails/                      # Mailables
│   └── Notifications/              # Channel Notifications
│
├── Database/                       # Migrations, Factories & Seeders
│   ├── Migrations/                 # Module Migrations
│   ├── Factories/                  # Model Factories
│   └── Seeders/                    # Module Seeders
│
├── tests/                          # Module Tests
│   ├── Feature/                    # Feature & Integration Tests
│   └── Unit/                       # Unit Tests
│
├── routes/                         # Module Route Definitions
│   ├── api.php                     # API routes
│   └── web.php                     # Web routes
│
├── config/                         # Module Configuration
│   └── config.php
│
├── resources/views/                # Optional Blade Views
└── {ModuleName}ServiceProvider.php # Module Service Provider
```

---

## Standard Module Namespaces

| Component | Target Namespace Example |
| :--- | :--- |
| **Eloquent Model** | `Modules\Customer\Domain\Models\Customer` |
| **Domain Policy** | `Modules\Customer\Domain\Policies\CustomerPolicy` |
| **Domain Enum** | `Modules\Customer\Domain\Enums\CustomerStatus` |
| **Domain Event** | `Modules\Customer\Domain\Events\CustomerRegisteredEvent` |
| **CQRS Write Action**| `Modules\Customer\Application\Actions\CreateCustomerAction` |
| **CQRS Read Query** | `Modules\Customer\Application\Queries\ListCustomersQuery` |
| **Application DTO** | `Modules\Customer\Application\Data\CreateCustomerData` |
| **App Service** | `Modules\Customer\Application\Services\CustomerPricingService` |
| **App Rule** | `Modules\Customer\Application\Rules\ValidCustomerTaxIdRule` |
| **HTTP Controller** | `Modules\Customer\Interface\Controllers\Api\V1\CustomerController` |
| **Form Request** | `Modules\Customer\Interface\Requests\UpdateCustomerRequest` |
| **Resource** | `Modules\Customer\Interface\Resources\CustomerResource` |
| **Queue Job** | `Modules\Customer\Infrastructure\Jobs\SyncCustomerToCrmJob` |
| **Model Factory** | `Modules\Customer\Database\Factories\CustomerFactory` |
| **Database Seeder**| `Modules\Customer\Database\Seeders\CustomerSeeder` |
| **Service Provider**| `Modules\Customer\CustomerServiceProvider` |

---

## Installation

Install the package via Composer:

```bash
composer require hatchyu/laravel-modular
```

### 1. Configure PSR-4 Autoloading

Add `"Modules\\": "modules/"` to your application's `composer.json` file under `autoload.psr-4`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "modules/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

Then regenerate the Composer autoload files:

```bash
composer dump-autoload
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=modular-config
```

This creates `config/modular.php`:

```php
return [
    'path' => base_path('modules'),
    'namespace' => 'Modules\\',
    'layout' => 'ddd',
    'autodiscover' => [
        'providers' => true,
        'routes' => true,
        'migrations' => true,
        'views' => true,
        'configs' => true,
        'factories' => true,
    ],
    'routing' => [
        'web_middleware' => ['web'],
        'api_middleware' => ['api'],
        'api_prefix' => 'api',
    ],
    'cache_path' => base_path('bootstrap/cache/modules.php'),
    'stubs_path' => null,
];
```

---

## Module Lifecycle & Dependency Management

Every module can contain a local `module.json` manifest located at `modules/{ModuleName}/module.json` (automatically generated when running `php artisan module:make`):

```json
{
    "name": "Order",
    "description": "Order processing domain module",
    "version": "1.0.0",
    "enabled": true,
    "dependencies": [
        "Billing",
        "Customer"
    ],
    "priority": 0
}
```

### Enabling & Disabling Modules
You can toggle modules on or off without deleting files or altering git history:

```bash
# Enable a module
php artisan module:enable Billing

# Disable a module
php artisan module:disable Billing
```

Disabled modules are completely excluded from service provider registration, route loading, database migrations, command discovery, view namespaces, and configuration merging.

### Dependency Resolution & Topological Booting
When modules declare dependencies in `module.json`:
- **Topological Boot Order:** The system resolves the dependency graph using Kahn's algorithm so prerequisite modules (e.g. `Billing`) always boot **before** dependent modules (e.g. `Order`).
- **Circular Dependency Detection:** Detects any circular dependency loops (e.g. `A -> B -> A`) and throws an explicit `ModuleDependencyException` identifying the loop.

### How Disabled or Missing Dependencies Are Handled
When a module depends on another module that is disabled or missing, the system handles it with clarity and safety:

1. **At Application Boot Time:**
   If `Order` is enabled but depends on disabled `Billing`, `ModuleRegistry::enabled()` halts execution with an actionable exception:
   ```
   ModuleDependencyException: Module [Order] depends on module [Billing], but [Billing] is currently disabled. Enable it using: php artisan module:enable Billing
   ```
   If the dependency does not exist in the codebase:
   ```
   ModuleDependencyException: Module [Order] requires module [Billing], but it was not found in the application.
   ```

2. **At CLI Level (Safe Protection):**
   - **Preventing Accidental Breakage:** If you attempt to disable a module that other active modules depend on, the command halts:
     ```bash
     $ php artisan module:disable Billing
     Active module(s) [Order] depend on [Billing].
     ERROR: Cannot disable module [Billing] because active module [Order] depends on it. Use --force to disable anyway.
     ```
   - **Enabling Prerequisite Validation:** If you attempt to enable a module whose prerequisites are disabled, it guides you to enable the dependencies first:
     ```bash
     $ php artisan module:enable Order
     WARN: Module [Order] depends on disabled module(s): Billing.
     ERROR: Please enable prerequisite modules first or use --force to override.
     ```

3. **In Health Diagnostics (`php artisan module:doctor`):**
   Displays the status of every module and flags any missing or disabled dependencies in the health inspection table.

### Safe Module Renaming
Rename any module safely across your entire codebase:

```bash
php artisan module:rename Order Sales
```

This single command:
1. Moves the directory from `modules/Order` to `modules/Sales`.
2. Renames `OrderServiceProvider.php` to `SalesServiceProvider.php` and `OrderDatabaseSeeder.php` to `SalesDatabaseSeeder.php`.
3. Refactors PHP namespaces, class names, view namespaces (`order::` to `sales::`), and route prefixes across all module files.
4. Updates `"name": "Sales"` in the module's `module.json`.
5. **Cross-module propagation:** Scans all other modules in `modules/` and updates any `"dependencies"` arrays containing `"Order"` to `"Sales"`.
6. Flushes and rebuilds the module discovery cache.

---

## Artisan CLI Commands

All generator commands support nested sub-namespaces (e.g. `V1/CreateOrderAction`) and `{--force}` to overwrite existing files.

### Scaffolding a Complete Module
Scaffold an entire DDD 4-layer module:

```bash
php artisan module:make Order
```

### Domain Layer Generators
```bash
# Eloquent Model (with optional migration & factory)
php artisan module:make-model Order Order -m -f
php artisan module:make-model Order Relations/OrderItem -m -f

# Authorization Policy
php artisan module:make-policy Order OrderPolicy --model=Order

# Backed Enum
php artisan module:make-enum Order OrderStatus

# Domain Event
php artisan module:make-event Order OrderPlacedEvent
```

### Application Layer Generators (CQRS & Use-Cases)
```bash
# CQRS Write Action
php artisan module:make-action Order CreateOrderAction
php artisan module:make-action Order V1/CreateOrderAction --force

# CQRS Read Query
php artisan module:make-query Order GetOrderListQuery

# Application Data Transfer Object (DTO)
php artisan module:make-data Order CreateOrderData
php artisan module:make-dto Order OrderData

# Validation Rule
php artisan module:make-rule Order ValidOrderTotalRule

# Application Service
php artisan module:make-service Order OrderCalculationService
```

### Interface Layer Generators (Delivery)
```bash
# Controllers (Web, API V1, or Admin)
php artisan module:make-controller Order OrderController
php artisan module:make-controller Order OrderController --api
php artisan module:make-controller Order OrderController --admin
php artisan module:make-controller Order V2/OrderController --api

# Form Request
php artisan module:make-request Order StoreOrderRequest

# JSON:API / JsonResource
php artisan module:make-resource Order OrderResource
```

### Infrastructure Layer Generators
```bash
# Asynchronous Queue Job
php artisan module:make-job Order SyncOrderToErpJob

# Synchronous Job
php artisan module:make-job Order ProcessOrderJob --sync
```

### Database Layer Generators
```bash
# Database Migration
php artisan module:make-migration Order create_orders_table

# Database Seeder
php artisan module:make-seeder Order OrderSeeder
```

### Test Generators
```bash
# Pest Feature Test (default)
php artisan module:make-test Order OrderApiTest

# Pest Unit Test
php artisan module:make-test Order CalculateTotalTest --unit

# PHPUnit Test
php artisan module:make-test Order OrderApiTest --phpunit
```

### Inspection, Diagnostics & Optimization Commands

```bash
# Verify PSR-4 mappings, permissions, and module health
php artisan module:check

# List all detected modules and their status
php artisan module:list

# Compile module discovery manifest for 0ms production performance
php artisan module:cache

# Clear compiled module discovery cache
php artisan module:clear
```

---

## Production Deployment Optimization

In production environments, add `php artisan module:cache` to your deployment pipeline:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan module:cache
```

This compiles all module discovery paths, routes, and presence flags into `bootstrap/cache/modules.php`, eliminating filesystem scanning completely.

---

## Testing & Quality

Run the test suite using Pest:

```bash
composer test
```

Check code formatting using Pint:

```bash
composer format:check
```

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
