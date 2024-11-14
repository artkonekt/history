<?php

declare(strict_types=1);

namespace Konekt\History\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\JobQueued;
use Konekt\History\Listeners\StartJobTracking;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        JobQueued::class => [
            StartJobTracking::class,
        ],
    ];
}
