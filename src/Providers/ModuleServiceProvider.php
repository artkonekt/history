<?php

declare(strict_types=1);

/**
 * Contains the ModuleServiceProvider class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-12
 *
 */

namespace Konekt\History\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Queue;
use Konekt\Concord\BaseModuleServiceProvider;
use Konekt\History\Models\ModelHistoryEvent;
use Konekt\History\Models\Via;
use Konekt\History\Queue\JobInfo;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        ModelHistoryEvent::class,
    ];

    protected $enums = [
        Via::class,
    ];

    public function boot(): void
    {
        parent::boot();
        App::bind(JobInfo::SERVICE_NAME, fn () => null);

        Queue::before(function (JobProcessing $event) {
            App::instance(
                JobInfo::SERVICE_NAME,
                new JobInfo($event->job->resolveName(), $event->job->getQueue()),
            );
        });

        Queue::after(function (JobProcessed $event) {
            App::bind(JobInfo::SERVICE_NAME, fn () => null);
        });
    }
}
