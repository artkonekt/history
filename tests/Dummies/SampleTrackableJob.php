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

    private array $logs = [];

    public function __construct(
        protected SampleTask $task,
        protected ?JobStatus $finalStatus = null,
    ) {
    }

    /**
     * This method will prepare a list of logs that will be created during the handling of the job
     */
    public function plantLogForTesting(string $level, string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }

    public function handle(): void
    {
        $tracker = JobTracker::of($this);
        $tracker->started();

        foreach ($this->logs as $log) {
            $tracker->log($log['message'], $log['level'], $log['context']);
        }

        if (null !== $this->finalStatus) {
            if ($this->finalStatus->is_completed) {
                $tracker->completed();
            } elseif ($this->finalStatus->is_failed) {
                $tracker->failed();
            }
        }
    }
}
