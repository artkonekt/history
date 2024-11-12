<?php

declare(strict_types=1);

namespace Konekt\History\Events;

use Konekt\History\Contracts\TrackableJob;
use Konekt\History\Contracts\TrackableJobEvent;
use Konekt\History\JobTracker;

abstract class BaseTrackableJobEvent implements TrackableJobEvent
{
    protected ?JobTracker $tracker = null;
    public function __construct(
        protected TrackableJob $job,
    ) {
    }

    public function getJob(): TrackableJob
    {
        return $this->job;
    }

    public function getTracker(): JobTracker
    {
        return $this->tracker ??= JobTracker::of($this->job);
    }
}
