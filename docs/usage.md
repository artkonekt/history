# History Usage with Eloquent Models

You can use any existing eloquent model with this package without any modification on them.

## Record Events

### Model Creation

To record the created event use the `History::begin()` method:

```php
use App\Models\Project;
use Konekt\History\History;

$project = Project::create(['name' => 'Giga Project', 'category' => 'Construction']);
History::begin($project);
```

### Model Updates

Record updates can be stored as well:

```php
use Konekt\History\History;

$project->update(['category' => 'Investment']);
History::logRecentUpdate($project);
```

If you also would like to store the old values of the fields, use the `logUpdate()` method:

```php
use Konekt\History\History;

$before = $project->getAttributes();
$project->update(['...fields...']);

History::logUpdate($project, $before);
```

It is also possible to add comments to the events:

```php
use Konekt\History\History;

$project = Project::create(['name' => 'Giga Project', 'category' => 'Construction']);
History::begin($project, 'Belated creation from Q3');

$project->update(['category' => 'Investment']);
History::logRecentUpdate($project, "Reclassified as per the CEO's request");
```

### Comments Without Field Changes

Comment only entries can be added:

```php
History::addComment($project, 'Expiry in 10 days');
```

### Deletion Events

Model deletion can be logged as well:

```php
$project->delete();
History::logDeletion($project);
```

### Record Action Execution

It can happen that your model is doing "actions", like a report being executed,
or a notification being triggered, and you want to add this event to the history.

In such cases there are no field changes, and you just report the fact whether
the action has succeeded or failed:

```php
try {
    $report->execute();
    History::logActionSuccess($report);
} catch (\Throwable $e) {
    History::logActionFailure($report, $e->getMessage());
}
```

The generated action events have a `was_successful` boolean property which indicates whether the action ran
successfully or failed.

> For non-action events, the `was_successful` field is `null` indicating that there's no "success" information available

## Retrieve History Events

To get the list of event of a model use the following code:

```php
use App\Models\Project;
use Konekt\History\History;
use Konekt\History\Models\ModelHistoryEvent;

$project = Project::find(1);
$history = History::of($project)->get();

/** @var ModelHistoryEvent $modelEvent */
foreach ($history as $modelEvent) {
    echo $modelEvent->happened_at;
    // 2023-11-14 19:31:47
    echo $modelEvent->ip_address;
    // 127.0.0.1
    echo $modelEvent->operation;
    // create
    echo $modelEvent->scene;
    // http://app.url/admin/project/1/update
    echo $modelEvent->via
    // web
    echo $modelEvent->user
    // App\User:2
    echo $modelEvent->comment
    // I am an optional comment
    echo $modelEvent->diff()->changedFields()
    // array
}
```

By default, events are returned so that the newest events are on the top. If you'd like to
have them in a chronological order pass `false` to the get method:

```php
\Konekt\History\History::of($model)->get(latestOnTop: false);
```

---

**Next**: [Trackable Models &raquo;](trackables.md)
