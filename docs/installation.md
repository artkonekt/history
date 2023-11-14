# Installation

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
