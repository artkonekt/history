<?php

declare(strict_types=1);

namespace Konekt\History\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
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
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

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
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

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
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

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
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

        foreach (range(1, 10) as $i) {
            $job = new SampleTrackableJob(new SampleTask());
            $job->generateJobTrackingId();
            JobTracker::createFor($job);
        }

        $entries = JobExecution::byJobClass(SampleTrackableJob::class, false, 5);
        $this->assertCount(5, $entries);
    }

    /** @test */
    public function it_returns_the_queued_at_date_as_last_time_something_has_happened_to_it_when_there_are_no_logs_and_no_other_event_dates_recorded()
    {
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

        /** @var JobExecution $job */
        $job = JobExecution::create([
            'job_class' => SampleTrackableJob::class,
            'tracking_id' => Str::ulid()->toBase58(),
            'queued_at' => Carbon::parse('2024-11-27 11:35:27'),
        ]);

        $this->assertEquals('2024-11-27 11:35:27', $job->lastTimeSomethingHasHappenedWithIt()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_returns_the_started_at_date_as_last_time_something_has_happened_to_it_when_there_is_an_earlier_queued_at_date_but_there_are_no_logs_and_no_other_event_dates_recorded()
    {
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

        /** @var JobExecution $job */
        $job = JobExecution::create([
            'job_class' => SampleTrackableJob::class,
            'tracking_id' => Str::ulid()->toBase58(),
            'queued_at' => Carbon::parse('2024-11-27 11:35:27'),
            'started_at' => Carbon::parse('2024-11-27 11:35:35'),
        ]);

        $this->assertEquals('2024-11-27 11:35:35', $job->lastTimeSomethingHasHappenedWithIt()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_returns_the_completed_at_date_as_last_time_something_has_happened_to_it_when_there_are_earlier_queued_at_and_started_at_dates_and_there_are_no_logs()
    {
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

        /** @var JobExecution $job */
        $job = JobExecution::create([
            'job_class' => SampleTrackableJob::class,
            'tracking_id' => Str::ulid()->toBase58(),
            'queued_at' => Carbon::parse('2024-11-27 11:35:27'),
            'started_at' => Carbon::parse('2024-11-27 11:35:35'),
            'completed_at' => Carbon::parse('2024-11-27 11:37:02'),
        ]);

        $this->assertEquals('2024-11-27 11:37:02', $job->lastTimeSomethingHasHappenedWithIt()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_returns_the_failed_at_date_as_last_time_something_has_happened_to_it_when_there_are_earlier_queued_at_and_started_at_dates_and_there_are_no_logs()
    {
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

        /** @var JobExecution $job */
        $job = JobExecution::create([
            'job_class' => SampleTrackableJob::class,
            'tracking_id' => Str::ulid()->toBase58(),
            'queued_at' => Carbon::parse('2024-11-27 11:35:27'),
            'started_at' => Carbon::parse('2024-11-27 11:35:35'),
            'failed_at' => Carbon::parse('2024-11-27 11:38:18'),
        ]);

        $this->assertCount(0, $job->getLogs());
        $this->assertEquals('2024-11-27 11:38:18', $job->lastTimeSomethingHasHappenedWithIt()->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_returns_the_latest_log_date_when_any_of_the_log_entries_are_later_then_the_status_timestamp_fields()
    {
        JobExecution::ofJobClass(SampleTrackableJob::class)->delete();

        Carbon::setTestNow('2024-11-27 14:00:19');
        /** @var JobExecution $job */
        $job = JobExecution::create([
            'job_class' => SampleTrackableJob::class,
            'tracking_id' => Str::ulid()->toBase58(),
            'queued_at' => Carbon::parse('2024-11-27 14:00:01'),
            'started_at' => Carbon::parse('2024-11-27 14:00:05'),
            'failed_at' => Carbon::parse('2024-11-27 14:00:18'),
        ]);
        $job->logInfo('Some Happy Message');
        Carbon::setTestNow('2024-11-27 14:00:20');
        $job->logError('Another Error Message');

        $this->assertEquals('2024-11-27 14:00:20', $job->lastTimeSomethingHasHappenedWithIt()->format('Y-m-d H:i:s'));
    }
}
