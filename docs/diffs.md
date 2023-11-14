# Diffs

Entries in the history return `Diff` objects that hold information about the changed fields.

```php
use App\Models\Project;
use Konekt\History\History;

$project = Project::create(['name' => 'Top Gun']);
$event = History::begin($project);
$event->diff()->changedFields();
// ['name']
$event->diff()->changeCount();
// 1
$event->diff()->changeOf('name');
// Konekt\History\Diff\Change
//  +old: Konekt\History\Diff\Undefined
//  +new: "Top Gun"
$event->diff()->new('name');
// "Top Gun"
$event->diff()->old('name');
// Konekt\History\Diff\Undefined
$event->isASingleFieldChange();
// true
```

## Undefined Values

The diff makes a difference between `NULL` values and `Undefined` values.
Undefined means, that a field doesn't have a value (eg. before creation),
or the value information was not available when the diff was created.

---

**Next**: [Customization](customization.md)
