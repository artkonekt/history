<?php

declare(strict_types=1);

namespace Konekt\History\Tests\Dummies;

use Illuminate\Foundation\Bus\Dispatchable;
use Konekt\History\Concerns\CanBeTracked;
use Konekt\History\Contracts\TrackableJob;
use Konekt\History\JobTracker;
use Konekt\History\Models\JobStatus;

class SampleTrackableJob implements TrackableJob
{
    use CanBeTracked;
    use Dispatchable;

    public function __construct(
        protected SampleTask $task,
        protected ?JobStatus $finalStatus = null,
    ) {
    }

    public function handle(): void
    {
        $tracker = JobTracker::of($this);
        $tracker->started();
        if (null !== $this->finalStatus) {
            if ($this->finalStatus->is_completed) {
                $tracker->completed();
            } elseif ($this->finalStatus->is_failed) {
                $tracker->failed();
            }
        }
    }
}
