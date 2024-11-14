<?php

declare(strict_types=1);

namespace Konekt\History\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\Events\JobQueueing;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Konekt\History\Events\TrackableJobCompleted;
use Konekt\History\Events\TrackableJobCreated;
use Konekt\History\Events\TrackableJobFailed;
use Konekt\History\Events\TrackableJobLogCreated;
use Konekt\History\Events\TrackableJobStarted;
use Konekt\History\JobTracker;
use Konekt\History\Listeners\StartJobTracking;
use Konekt\History\Models\JobExecution;
use Konekt\History\Models\JobStatus;
use Konekt\History\Tests\Dummies\SampleTask;
use Konekt\History\Tests\Dummies\SampleTrackableJob;
use Psr\Log\LogLevel;

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
        $this->assertNull($execution->failed_at);
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

    /** @test */
    public function it_can_log_during_execution()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task, JobStatus::FAILED());
        $job->generateJobTrackingId();
        $job->plantLogForTesting(LogLevel::INFO, 'Hey hello, we are starting');
        $job->plantLogForTesting(LogLevel::DEBUG, 'It is deep, man');
        $job->plantLogForTesting(LogLevel::ERROR, 'Fell on its own ass');

        $execution = JobTracker::createFor($job);
        Bus::dispatchSync($job);

        $execution = $execution->fresh();
        $logs = $execution->getLogs();
        $this->assertCount(3, $logs);
    }

    /** @test */
    public function the_start_job_tracking_listener_is_active()
    {
        // I wanted to test here whether the listener works well
        // and that the tracking id was initialized correctly
        // but during tests queue listeners aren't invoked
        Event::fake();
        Event::assertListening(JobQueueing::class, StartJobTracking::class);
    }

    /** @test */
    public function it_emits_a_created_event_when_creating_via_the_tracker()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();

        Event::fake();
        JobTracker::createFor($job);

        Event::assertDispatched(TrackableJobCreated::class);
    }

    /** @test */
    public function it_emits_a_started_event_when_starting_via_the_tracker()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();

        Event::fake();
        JobTracker::createFor($job);

        Bus::dispatchSync($job);

        Event::assertDispatched(TrackableJobStarted::class);
    }

    /** @test */
    public function it_emits_a_failed_event_when_failing_via_the_tracker()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task, JobStatus::FAILED());
        $job->generateJobTrackingId();

        Event::fake();
        JobTracker::createFor($job);

        Bus::dispatchSync($job);

        Event::assertDispatched(TrackableJobFailed::class);
    }

    /** @test */
    public function it_emits_a_completed_event_when_completing_via_the_tracker()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task, JobStatus::COMPLETED());
        $job->generateJobTrackingId();

        Event::fake();
        JobTracker::createFor($job);

        Bus::dispatchSync($job);

        Event::assertDispatched(TrackableJobCompleted::class);
    }

    /** @test */
    public function it_emits_a_log_created_event_when_logging_via_the_tracker()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);
        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();
        $job->plantLogForTesting(LogLevel::INFO, 'Hey hello, we are starting');

        Event::fake();

        JobTracker::createFor($job);
        Bus::dispatchSync($job);

        Event::assertDispatched(TrackableJobLogCreated::class);
    }

    /** @test */
    public function it_records_the_authenticated_user()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        $user = $this->createUser();
        Auth::login($user);

        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();

        $execution = JobTracker::createFor($job);

        $this->assertEquals($user->getAuthIdentifier(), $execution->user_id);
        $this->assertInstanceOf(Auth::getProvider()->getModel(), $execution->user);
        $this->assertInstanceOf(Auth::getProvider()->getModel(), $execution->getUser());
    }

    /** @test */
    public function the_user_is_null_when_created_as_unauthenticated()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        $job = new SampleTrackableJob($task);
        $job->generateJobTrackingId();

        $execution = JobTracker::createFor($job);

        $this->assertNull($execution->user_id);
        $this->assertNull($execution->user);
        $this->assertNull($execution->getUser());
    }

    private function createUser(): Authenticatable
    {
        $userClass = Auth::getProvider()->getModel();
        $user = new $userClass();
        $user->name = 'Fritz Teufel';
        $user->email = Str::ulid()->toBase58() . '@teufel.de';
        $user->password = Hash::make('qwerty123...what else?');
        $user->save();

        return $user;
    }
}
