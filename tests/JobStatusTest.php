<?php

declare(strict_types=1);

namespace Konekt\History\Tests;

use Konekt\History\Models\JobStatus;

class JobStatusTest extends TestCase
{
    /** @test */
    public function it_can_tell_whether_it_has_ended()
    {
        $completed = JobStatus::COMPLETED();
        $failed = JobStatus::FAILED();
        $queued = JobStatus::QUEUED();
        $processing = JobStatus::PROCESSING();

        $this->assertTrue($completed->hasEnded());
        $this->assertTrue($failed->hasEnded());
        $this->assertFalse($queued->hasEnded());
        $this->assertFalse($processing->hasEnded());
    }

    /** @test */
    public function it_can_tell_whether_it_is_active()
    {
        $queued = JobStatus::QUEUED();
        $processing = JobStatus::PROCESSING();
        $completed = JobStatus::COMPLETED();
        $failed = JobStatus::FAILED();

        $this->assertTrue($queued->isActive());
        $this->assertTrue($processing->isActive());
        $this->assertFalse($completed->isActive());
        $this->assertFalse($failed->isActive());
    }
}
