# Trackable Models

It is not required, but you can optionally implement the `Trackable` interface on your models, that you can use for customizing
the history of the given model.

The Trackable interface has 3 methods:

```php
class Ticket extends Model implements \Konekt\History\Contracts\Trackable
{
    // [...]
    
    public function generateHistoryEventSummary(ModelHistoryEvent $event): ?string
    {
    }

    public function includeAttributesInHistory(): ?array
    {
    }

    public function excludeAttributesFromHistory(): ?array
    {
    }
}
```

## Attributes To Include

If the `includeAttributesInHistory()` method returns an array of fields, then only changes to those fields will be
recorded in the history:

```php
class Ticket extends Model implements Trackable
{
    //...
    public function includeAttributesInHistory(): ?array
    {
        return ['title', 'status', 'description', 'assignee'];
    }
}
```

## Attributes To Exclude

If you want to specify which fields to EXCLUDE from the history diffs, then return those fields in the
`excludeAttributesFromHistory` method:

```php
class User extends Model implements Trackable
{
    //...
    public function excludeAttributesFromHistory(): ?array
    {
        return ['api_key', 'password'];
    }
    
    public function includeAttributesInHistory(): ?array
    {
        return null;
    }
}
```

**IMPORTANT!**

- The exclude list only works if the `includeAttributesInHistory()` returns NULL!
- The `id`, `created_at` and `updated_at` fields are excluded by default


## Attributes To Redact

There are cases where you want track the fact that a certain field has changed, however,
you do not want to store the actual content of the fields with the history.

For that, beginning with version 2.0. you can redact certain fields from the history diffs.

To achieve that, the `redactInHistory()` method can be used:

```php
class User extends Model implements Trackable
{
    //...    
    public function redactInHistory(string $field, mixed $value): bool|\Closure
    {
        return match($field) {
            'api_key' => true,
            default => false,
        };
    }
}
```

If the method returns `false` the field will be included in the history diffs as-is.
If the method returns `true`, the default redactor will be used, which replaces non-null values with `******`.

If you want to implement a custom redaction logic, make the method to return a closure, which will be called upon redaction:

```php
class User extends Model implements Trackable
{
    //...
    public function redactInHistory(string $field, mixed $value): bool|\Closure
    {
        return match($field) {
            'some_sensitive_field' => fn ($v) => '42' === $v ? 'The meaning of life' : '[REDACTED]',
            default => false,
        };
    }
```

The code above will replace the value of the `some_sensitive_field` with "[REDACTED]", unless it's value is "42",
because then it will save it as "The meaning of life". Yeah, very funny.

---

**Next**: [Scenes &raquo;](scenes.md)
