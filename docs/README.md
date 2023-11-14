# Eloquent Model History Documentation

History is a Laravel package to manage the history of changes of Eloquent models.
This library DOES NOT automatically hook into the model's lifecycle events, but
allows the developer to explicitly register history events at arbitrary places
in the application code.

## Features

- Record model creation, update, delete, and retrieval
- Add optional comments to events
- Add comment-only history events
- Automatically record the IP, URL, user agent and user ids when in an HTTP context
- Automatically detect the CLI, the command name when in an artisan command
- Automatically detect the queue and the job when running in a queued job
- Define included/excluded fields on a per-model basis
- Has a diff of the changed fields (old/new values)

## Alternatives

If you need a strict audit tool for Laravel, that can automatically record changes, wherever they happen,
then check out the [Laravel Auditing Package](https://laravel-auditing.com/)

Other alternatives:
- https://github.com/rudashi/laravel-history/
- https://github.com/seancheung/history
- https://github.com/spatie/laravel-activitylog
- https://github.com/VentureCraft/revisionable

## Changelog

See the [Changelog](https://github.com/artkonekt/history/blob/master/Changelog.md) for more information about what has changed recently.

---

**Next**: [Installation &raquo;](installation.md)
