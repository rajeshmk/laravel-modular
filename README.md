# Laravel Modular

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hatchyu/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/hatchyu/laravel-modular)
[![Total Downloads](https://img.shields.io/packagist/dt/hatchyu/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/hatchyu/laravel-modular)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

A high-performance, zero-boilerplate **Modular Monolith** architecture and Pragmatic CQRS generator CLI for Laravel applications.

---

## Key Features

- 🚀 **Zero-Boilerplate Auto-Discovery:** Automatically discovers module service providers, routes (`web.php` & `api.php`), database migrations, namespaced views, and configs.
- ⚡ **Production Performance Caching:** Built-in `php artisan module:cache` eliminates runtime filesystem scans in production for 0ms overhead.
- 🛠️ **Pragmatic CQRS Generators:** Dedicated Artisan CLI to scaffold standard enterprise modules, Actions (writes), Queries (reads), DTOs, Controllers, and Requests.
- 🏭 **Model Factory Guesser:** Automatically maps Eloquent model factories located inside `Modules\{Module}\Database\Factories`.
- 🧩 **Non-Invasive:** No custom repository overhead, no `module.json` manifest requirements. Clean standard Laravel conventions.

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
    ],
    'cache_path' => base_path('bootstrap/cache/modules.php'),
];
```

---

## Artisan CLI Commands

### Scaffolding a New Module
Scaffold an entire enterprise module conforming to the Modular Monolith blueprint:

```bash
php artisan module:make Order
```

This generates:
```text
modules/Order/
├── Actions/
├── Queries/
│   ├── Filters/
│   └── Searches/
├── Controllers/
│   ├── Api/V1/
│   └── Admin/
├── Requests/
├── Resources/
├── Models/
├── Exceptions/
├── Contracts/
├── DTOs/
├── Enums/
├── Events/
├── Listeners/
├── Middleware/
├── Jobs/
├── Console/
├── config/config.php
├── OrderServiceProvider.php
├── database/
│   ├── migrations/
│   └── factories/
├── resources/views/
└── routes/
    ├── api.php
    └── web.php
```

### Generator Commands
Generate individual components inside any module:

```bash
# Generate a Write Action
php artisan module:make-action Order CreateOrderAction

# Generate a Read Query
php artisan module:make-query Order GetOrderListQuery

# Generate a Readonly DTO
php artisan module:make-dto Order OrderData

# Generate a Controller (Web, API, or Admin)
php artisan module:make-controller Order OrderController
php artisan module:make-controller Order OrderController --api
php artisan module:make-controller Order OrderController --admin

# Generate FormRequest & JsonResource
php artisan module:make-request Order StoreOrderRequest
php artisan module:make-resource Order OrderResource

# Generate Model (with optional migration & factory)
php artisan module:make-model Order Order -m -f

# Generate a Migration
php artisan module:make-migration Order create_orders_table
```

### Inspection & Optimization Commands

```bash
# List all detected modules and their status
php artisan module:list

# Compile module discovery manifest for production
php artisan module:cache

# Clear compiled module discovery cache
php artisan module:clear
```

---

## Production Deployment Optimization

In production environments, add `php artisan module:cache` to your deployment script:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan module:cache
```

This compiles all module paths and configurations into `bootstrap/cache/modules.php`, eliminating filesystem scanning completely.

---

## Testing

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
