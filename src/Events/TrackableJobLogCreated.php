<?php

declare(strict_types=1);

namespace Konekt\History\Events;

use Konekt\History\Contracts\JobExecutionLog;
use Konekt\History\Contracts\TrackableJob;

class TrackableJobLogCreated extends BaseTrackableJobEvent
{
    protected JobExecutionLog $log;

    public function __construct(TrackableJob $job, JobExecutionLog $log)
    {
        $this->log = $log;
        parent::__construct($job);
    }

    public function getLogEntry(): JobExecutionLog
    {
        return $this->log;
    }
}
