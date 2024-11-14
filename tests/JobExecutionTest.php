<?php

declare(strict_types=1);

namespace Konekt\History\Tests;

use Illuminate\Support\Facades\Bus;
use Konekt\History\JobTracker;
use Konekt\History\Models\JobExecution;
use Konekt\History\Models\JobStatus;
use Konekt\History\Tests\Dummies\SampleTask;
use Konekt\History\Tests\Dummies\SampleTrackableJob;

class JobExecutionTest extends TestCase
{
    /** @test */
    public function it_can_find_and_entry_by_tracking_id()
    {
        $job = new SampleTrackableJob(new SampleTask());
        $job->generateJobTrackingId();
        JobTracker::createFor($job);

        $execution = JobExecution::findByTrackingId($job->getJobTrackingId());

        $this->assertInstanceOf(JobExecution::class, $execution);
        $this->assertEquals($job->getJobTrackingId(), $execution->tracking_id);
    }

    /** @test */
    public function it_can_return_the_entries_of_a_given_job_class()
    {
        foreach (range(1, 10) as $i) {
            $job = new SampleTrackableJob(new SampleTask());
            $job->generateJobTrackingId();
            JobTracker::createFor($job);
        }

        $entries = JobExecution::byJobClass(SampleTrackableJob::class);
        $this->assertCount(10, $entries);
    }

    /** @test */
    public function it_can_return_the_active_entries_of_a_given_job_class()
    {
        foreach (range(1, 4) as $i) {
            $job = new SampleTrackableJob(new SampleTask(), JobStatus::COMPLETED());
            $job->generateJobTrackingId();
            JobTracker::createFor($job);

            Bus::dispatch($job);
        }

        foreach (range(1, 2) as $i) {
            $job = new SampleTrackableJob(new SampleTask());
            $job->generateJobTrackingId();
            JobTracker::createFor($job);

            Bus::dispatch($job);
        }

        $entries = JobExecution::byJobClass(SampleTrackableJob::class, true);
        $this->assertCount(2, $entries);
    }

    /** @test */
    public function it_can_return_the_entries_of_a_given_job_class_and_limit_the_record_count()
    {
        foreach (range(1, 10) as $i) {
            $job = new SampleTrackableJob(new SampleTask());
            $job->generateJobTrackingId();
            JobTracker::createFor($job);
        }

        $entries = JobExecution::byJobClass(SampleTrackableJob::class, false, 5);
        $this->assertCount(5, $entries);
    }
}
