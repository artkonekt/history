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

---

**Next**: [Scenes &raquo;](scenes.md)
