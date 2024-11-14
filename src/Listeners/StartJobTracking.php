<?php

declare(strict_types=1);

namespace Konekt\History\Listeners;

use Illuminate\Queue\Events\JobQueueing;
use Konekt\History\Contracts\TrackableJob;
use Konekt\History\JobTracker;

class StartJobTracking
{
    public function handle(JobQueueing $event)
    {
        if ($event->job instanceof TrackableJob && $event->job->doesNotHaveTrackingIdYet()) {
            $event->job->generateJobTrackingId();
            JobTracker::createFor($event->job);
        }
    }
}
