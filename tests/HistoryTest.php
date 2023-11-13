<?php

declare(strict_types=1);

/**
 * Contains the HistoryTest class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Tests;

use Konekt\History\History;
use Konekt\History\Models\ModelHistoryEvent;
use Konekt\History\Tests\Dummies\SampleTask;

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
    }

    /** @test */
    public function an_update_can_be_recorded()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        History::begin($task);
        $task->update(['status' => 'done']);
        $event = History::addUpdate($task);

        $this->assertTrue($event->isASingleFieldChange());
        $this->assertTrue($event->diff()->hasChanged('status'));
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
}
