# Installation

## Prerequisites

PHP 8.1+ and Laravel 10 or 11.

!> **Avoid using PostgresSQL 11** or older since it is known to have bugs when using together with this library and Laravel 11!

You can install the package via composer:

``` bash
composer require konekt/history
```

Now add the module in `config/concord.php` file:

```php
'modules' => [
    // ...
    Konekt\History\Providers\ModuleServiceProvider::class,
];
```

The package contains some migrations that you can run as usual by:

```bash
php artisan migrate
```

---

**Next**: [Configuration &raquo;](configuration.md)
