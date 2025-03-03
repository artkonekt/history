# Eloquent Model History

[![Tests](https://img.shields.io/github/actions/workflow/status/artkonekt/history/tests.yml?branch=master&style=flat-square)](https://github.com/artkonekt/history/actions?query=workflow%3Atests)
[![Packagist version](https://img.shields.io/packagist/v/konekt/history.svg?style=flat-square)](https://packagist.org/packages/konekt/history)
[![Packagist downloads](https://img.shields.io/packagist/dt/konekt/history.svg?style=flat-square)](https://packagist.org/packages/konekt/history)
[![StyleCI](https://styleci.io/repos/717756663/shield?branch=master)](https://styleci.io/repos/717756663)
[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

This package provides features to:

1. Log changes, diff and comments to **Eloquent Models**;
2. Track and log the execution history of Laravel **Background Jobs**;

## Model History

**Features**:

- Record model creation, update, delete, and retrieval
- Add optional comments to events
- Add comment-only history events
- Automatically record the IP, URL, user agent and user ids when in an HTTP context
- Automatically detect the CLI, the command name when in an artisan command
- Automatically detect the queue and the job when running in a queued job
- Define included/excluded fields on a per-model basis
- Has a diff of the changed fields (old/new values)

```php
$task = Task::create(['title' => 'Get milk', 'status' => 'todo']);
History::begin($task);

$task->update(['status' => 'done']);
History::logRecentUpdate($task);
```

## Job History

The goal of tracking a job is to be able to show the status and updates of a background job on frontends.
It is similar to logging, but is always scoped to a given job execution, and adds state to each job execution.

**Features**:

- Track the execution status of Laravel Jobs
- Set and read the completion % of a job execution
- Write logs for job executions
- Detect the user that has executed the job


```php
class MyJob implements \Konekt\History\Contracts\TrackableJob
{
    use \Konekt\History\Concerns\CanBeTracked;
    
    public function __construct(private array $dataToProcess)
    {        
    }
    
    public function handle()
    {
        $tracker = $this->jobTracker();
        $tracker->setProgressMax(count($this->dataToProcess));
        $tracker->started();
        try {
            foreach ($this->dataToProcess as $data) {
                Do::something()->withThe($data);
                $tracker->advance();
                $tracker->logInfo('An entry was processed');
            }
            $tracker->completed();
        } catch (\Throwable $e) {
            $tracker->failed($e->getMessage());
        }
    }
}

MyJob::dispatch($myDataToProcess);
```

## Requirements

It requires PHP 8.1+ and Laravel 10, 11 or 12.

It has been tested with:
- PHP 8.1, 8.2, 8.3 & 8.4
- SQLite,
- MySQL 5.7, 8.0, 8.2 & 8.4,
- PostgreSQL 12, 16 & 17.

It is known that this library, **Laravel 11.0/12.0 and PostgreSQL 11 don't work together**, therefore it is
recommended to use at least Postgres version 12 or higher in case your DB engine is Postgres.

## Documentation

For Installation and usage instruction see the Documentation; https://konekt.dev/history/1.x
