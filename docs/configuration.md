# Configuration

## Configuring The User Model

When adding a history event, the authenticated user gets stored along with the event.

The user model class, however, might change from application to application. By default,
this library uses the class returned by `Auth::getProvider()->getModel()`.

If for any reason this is not the class you'd like to use, set the `konekt.history.user_model`
configuration value.

Either in the `config/konekt.php` file

```php
return [
    'history' => [
        'model_class' => \App\Models\Officer::class,    
    ]
];
```

or in the `config/concord.php` file:

```php
'modules' => [
    // ...
    Konekt\History\Providers\ModuleServiceProvider::class => [
        'model_class' => \App\Models\Officer::class,
    ],
];
```

## Custom Models

This package is a Concord module, therefore it's models and enums can be replaced with your implementations if needed.

Read more about this at:

- [Customization](customization.md)
- https://konekt.dev/concord/1.x/models
- https://konekt.dev/concord/1.x/enums

---

**Next**: [Usage &raquo;](usage.md)
