<?php

declare(strict_types=1);

/**
 * Contains the HistoryTest class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Tests;

use Konekt\History\History;
use Konekt\History\Models\ModelHistoryEvent;
use Konekt\History\Models\Operation;
use Konekt\History\Models\Via;
use Konekt\History\Tests\Dummies\SampleJob;
use Konekt\History\Tests\Dummies\SampleJobWithDisplayName;
use Konekt\History\Tests\Dummies\SampleSceneResolver;
use Konekt\History\Tests\Dummies\SampleTask;
use Konekt\History\Tests\Dummies\SampleTrackableClient;

class HistoryTest extends TestCase
{
    /** @test */
    public function it_can_record_if_a_model_was_created()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        History::begin($task);

        $history = History::of($task)->get();
        $this->assertCount(1, $history);
        $event = $history->first();
        $this->assertInstanceOf(ModelHistoryEvent::class, $event);
        $this->assertCount(3, $event->diff()->changes());
        $this->assertEquals(Operation::CREATE, $event->operation->value());
    }

    /** @test */
    public function a_recent_update_can_be_recorded()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        History::begin($task);
        $task->update(['status' => 'done']);
        $event = History::logRecentUpdate($task);

        $this->assertTrue($event->isASingleFieldChange());
        $this->assertTrue($event->diff()->hasChanged('status'));
        $this->assertEquals(Operation::UPDATE, $event->operation->value());
    }

    /** @test */
    public function non_diff_changes_can_be_manually_recorded()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        History::begin($task);
        $event = History::addComment($task, 'Has timed out waiting');

        $this->assertTrue($event->isACommentOnlyEntry());
        $this->assertEmpty($event->diff()->changes());
        $this->assertEquals('Has timed out waiting', $event->comment());
    }

    /** @test */
    public function deletion_can_be_recorded()
    {
        $client = SampleTrackableClient::create(['name' => 'X Ltd.', 'country' => 'Denmark', 'api_key' => 'xxx']);
        History::begin($client);

        $client->delete();

        $deleteEvent = History::logDeletion($client);
        $this->assertEquals(Operation::DELETE, $deleteEvent->operation->value());
    }

    /** @test */
    public function it_detects_if_it_was_run_via_cli()
    {
        $task = SampleTask::create(['title' => 'Detect Via', 'status' => 'todo']);

        $event = History::begin($task);

        $this->assertInstanceOf(Via::class, $event->via);
        $this->assertEquals(Via::CLI(), $event->via);
    }

    /** @test */
    public function it_detects_if_it_was_run_in_a_queued_job()
    {
        $task = SampleTask::create(['title' => 'In A Queue', 'status' => 'in-progress']);
        SampleJob::dispatch($task);

        $event = History::of($task)->get()->first();

        $this->assertEquals(Via::QUEUE(), $event->via);
        $this->assertEquals(SampleJob::class, $event->scene);
    }

    /** @test */
    public function it_uses_the_display_name_of_a_queued_job_if_available()
    {
        $task = SampleTask::create(['title' => 'In A Nice Queue Job', 'status' => 'in-progress']);
        SampleJobWithDisplayName::dispatch($task);

        $event = History::of($task)->get()->first();

        $this->assertEquals(Via::QUEUE(), $event->via);
        $this->assertEquals(SampleJobWithDisplayName::NICE_NAME, $event->scene);
    }

    /** @test */
    public function it_can_use_a_custom_scene_resolver()
    {
        History::useSceneResolver(SampleSceneResolver::class);
        $task = SampleTask::create(['title' => 'Custom Scene', 'status' => 'in-progress']);

        $event = History::begin($task);

        $this->assertEquals(Via::QUEUE(), $event->via);
        $this->assertEquals('Meh meh', $event->scene);
    }
}
