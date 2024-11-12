<?php

declare(strict_types=1);

namespace Konekt\History\Contracts;

use Konekt\History\JobTracker;

interface TrackableJobEvent
{
    public function getJob(): TrackableJob;

    public function getTracker(): JobTracker;
}
