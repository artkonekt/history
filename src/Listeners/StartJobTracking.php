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
        if (!$event->job instanceof TrackableJob) {
            return;
        }

        if ($event->job->doesNotHaveTrackingIdYet()) {
            $event->job->generateJobTrackingId(); // In fact, it's not the best idea, because it won't be saved to the payload
        }

        if (!JobTracker::of($event->job)->hasExecutionEntry()) {
            $tracker = JobTracker::createFor($event->job);
            $tracker->logInfo(__('The job has been queued'));
        }
    }
}
