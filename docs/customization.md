# History Customization

## Model Customization

This package has one eloquent model, the `ModelHistoryEvent`. This can be extended or even replaced using
[Concord's mechanism](https://konekt.dev/concord/1.x/models#detailed-example):

Implement your own model class, eg `App\ModelHistoryEvent`.

And register the model from within your AppServiceProvider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Konekt\History\Contracts\ModelHistoryEvent as ModelHistoryEventContract;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->concord->registerModel(ModelHistoryEventContract::class, \App\ModelHistoryEvent::class);
    }
}
```

## Scene Customization

### Custom Via Values

The built-in `Via` enum comes with `web`, `cli` and `queue` values. If you'd like to add more via values, then
extend the Via Enum and add more constants to it:

```php
namespace App\Models;

class Via extends \Konekt\History\Models\Via
{
    public const ADMIN = 'admin';
}
```

Then, register the extended enum from within your AppServiceProvider:

```php
class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->concord->registerEnum(\Konekt\History\Contracts\Via::class, \App\Models\Via::class);
    }
}
```

### Custom Screen Resolution Logic

If you'd like to customize the screen resolution logic, then create your own class, that implements the `SceneResolver`
interface. Once that's done, tell the History class to use it, possibly in the AppServiceProvider's boot method:

```php
\Konekt\History\History::useSceneResolver(YourResolver::class);
// or
\Konekt\History\History::useSceneResolver(new YourResolver());
```
