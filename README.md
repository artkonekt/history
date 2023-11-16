# Eloquent Model History

[![Tests](https://img.shields.io/github/actions/workflow/status/artkonekt/history/tests.yml?branch=master&style=flat-square)](https://github.com/artkonekt/history/actions?query=workflow%3Atests)
[![Packagist version](https://img.shields.io/packagist/v/konekt/history.svg?style=flat-square)](https://packagist.org/packages/konekt/history)
[![Packagist downloads](https://img.shields.io/packagist/dt/konekt/history.svg?style=flat-square)](https://packagist.org/packages/konekt/history)
[![StyleCI](https://styleci.io/repos/717756663/shield?branch=master)](https://styleci.io/repos/717756663)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

This package provides features to log changes, diff and comments to Eloquent models.

```php
$task = Task::create(['title' => 'Get milk', 'status' => 'todo']);
History::begin($task);

$task->update(['status' => 'done']);
History::logRecentUpdate($task);
```

## Features

- Record model creation, update, delete, and retrieval
- Add optional comments to events
- Add comment-only history events
- Automatically record the IP, URL, user agent and user ids when in an HTTP context
- Automatically detect the CLI, the command name when in an artisan command
- Automatically detect the queue and the job when running in a queued job
- Define included/excluded fields on a per-model basis
- Has a diff of the changed fields (old/new values)

## Documentation

For Installation and usage instruction see the Documentation; https://konekt.dev/history/master
