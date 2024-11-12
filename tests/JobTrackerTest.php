<?php

declare(strict_types=1);

namespace Konekt\History\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Konekt\History\JobTracker;
use Konekt\History\Models\JobExecution;
use Konekt\History\Models\JobStatus;
use Konekt\History\Tests\Dummies\SampleTask;
use Konekt\History\Tests\Dummies\SampleTrackableJob;

class JobTrackerTest extends TestCase
{
    /** @test */
    public function it_can_create_a_job_tracking()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();
        $queueDate = Carbon::now()->subSeconds(4)->startOfSecond();// DB can't store the milliseconds
        Carbon::setTestNow($queueDate);
        $execution = JobTracker::createFor($job);

        $this->assertInstanceOf(JobExecution::class, $execution);
        $this->assertInstanceOf(Carbon::class, $execution->queued_at);
        $this->assertTrue($execution->queued_at->eq($queueDate));
        $this->assertNull($execution->started_at);
        $this->assertNull($execution->completed_at);
        $this->assertNull($execution->failed_et);
        $this->assertTrue(JobStatus::QUEUED()->equals($execution->status()));
    }

    /** @test */
    public function it_can_create_a_job_tracking_with_a_custom_max_progress_value()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();
        $execution = JobTracker::createFor($job, 1341);

        $this->assertEquals(1341, $execution->progress_max);
    }

    /** @test */
    public function it_can_record_when_starting_to_work_on_a_job()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();
        $queueDate = Carbon::now()->subSeconds(50)->startOfSecond();// DB can't store the milliseconds
        Carbon::setTestNow($queueDate);
        $execution = JobTracker::createFor($job, 1341);
        $this->assertTrue(JobStatus::QUEUED()->equals($execution->status()));

        $startDate = Carbon::now()->subSeconds(5)->startOfSecond();// DB can't store the milliseconds
        Carbon::setTestNow($startDate);
        Bus::dispatchSync($job);

        $execution = $execution->fresh();
        $this->assertInstanceOf(Carbon::class, $execution->started_at);
        $this->assertTrue($execution->started_at->eq($startDate));
        $this->assertTrue(JobStatus::PROCESSING()->equals($execution->status()));
    }

    /** @test */
    public function it_can_record_a_successful_job_execution()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task, JobStatus::COMPLETED());
        $job->generateJobTrackingId();

        $execution = JobTracker::createFor($job);

        Bus::dispatchSync($job);

        $execution = $execution->fresh();
        $this->assertInstanceOf(Carbon::class, $execution->queued_at);
        $this->assertInstanceOf(Carbon::class, $execution->started_at);
        $this->assertInstanceOf(Carbon::class, $execution->completed_at);
        $this->assertNull($execution->failed_at);
        $this->assertTrue(JobStatus::COMPLETED()->equals($execution->status()));
    }

    /** @test */
    public function it_can_record_a_failed_job_execution()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task, JobStatus::FAILED());
        $job->generateJobTrackingId();

        $execution = JobTracker::createFor($job);
        $startDate = Carbon::now()->subSeconds(5)->startOfSecond();// DB can't store the milliseconds

        Bus::dispatchSync($job);

        $execution = $execution->fresh();
        $this->assertInstanceOf(Carbon::class, $execution->queued_at);
        $this->assertInstanceOf(Carbon::class, $execution->started_at);
        $this->assertInstanceOf(Carbon::class, $execution->failed_at);
        $this->assertNull($execution->completed_at);
        $this->assertTrue(JobStatus::FAILED()->equals($execution->status()));
    }
}
