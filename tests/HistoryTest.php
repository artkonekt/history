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

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $this->assertNull($event->comment());
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
        $this->assertNull($event->comment());
        $this->assertEquals(Operation::UPDATE, $event->operation->value());
    }

    /** @test */
    public function update_with_explicit_before_fields_can_be_recorded()
    {
        $task = SampleTask::create(['title' => 'Go There', 'status' => 'todo']);

        $before = $task->getAttributes();
        $task->update(['status' => 'done', 'description' => 'Been there']);

        $event = History::logUpdate($task, $before);

        $this->assertEquals(Operation::UPDATE, $event->operation->value());
        $this->assertEquals(2, $event->diff()->changeCount());
        $this->assertTrue($event->diff()->hasChanged('status'));
        $this->assertTrue($event->diff()->hasChanged('description'));
        $this->assertEquals('todo', $event->diff()->old('status'));
        $this->assertEquals('done', $event->diff()->new('status'));
        $this->assertEquals('Been there', $event->diff()->new('description'));
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
    public function it_can_record_a_successful_action()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        $event = History::logActionSuccess($task);

        $this->assertEquals(Operation::ACTION, $event->operation->value());
        $this->assertTrue($event->was_successful);
        $this->assertTrue($event->diff()->isEmpty());
        $this->assertEmpty($event->diff()->changes());
        $this->assertNull($event->comment());
        $this->assertNull($event->details());
        $this->assertNull($event->actionName());
    }

    /** @test */
    public function it_can_record_a_successful_action_with_details()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        $event = History::logActionSuccess($task, '124 entries updated');

        $this->assertEquals(Operation::ACTION, $event->operation->value());
        $this->assertTrue($event->was_successful);
        $this->assertEquals('124 entries updated', $event->details());
        $this->assertNull($event->comment());
    }

    /** @test */
    public function it_can_record_a_failed_action()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        $event = History::logActionFailure($task);

        $this->assertEquals(Operation::ACTION, $event->operation->value());
        $this->assertFalse($event->was_successful);
        $this->assertTrue($event->diff()->isEmpty());
        $this->assertEmpty($event->diff()->changes());
        $this->assertNull($event->comment());
        $this->assertNull($event->details());
        $this->assertNull($event->actionName());
    }

    /** @test */
    public function it_can_record_a_failed_action_with_details()
    {
        $task = SampleTask::create(['title' => 'Hello', 'description' => 'Make me', 'status' => 'todo']);

        $event = History::logActionFailure($task, 'Error connecting to the Remote API');

        $this->assertEquals(Operation::ACTION, $event->operation->value());
        $this->assertFalse($event->was_successful);
        $this->assertEquals('Error connecting to the Remote API', $event->details());
        $this->assertNull($event->comment());
    }

    /** @test */
    public function it_can_record_a_successful_action_with_name()
    {
        $task = SampleTask::create(['title' => 'Hello 40', 'description' => 'Make me 41', 'status' => 'todo']);

        $event = History::logActionSuccess($task, actionName: 'completion');

        $this->assertEquals(Operation::ACTION, $event->operation->value());
        $this->assertTrue($event->was_successful);
        $this->assertTrue($event->diff()->isEmpty());
        $this->assertEmpty($event->diff()->changes());
        $this->assertNull($event->comment());
        $this->assertNull($event->details());
        $this->assertEquals('completion', $event->actionName());
    }

    /** @test */
    public function it_can_record_a_failed_action_with_name()
    {
        $task = SampleTask::create(['title' => 'Hello 50', 'description' => 'Make me 51', 'status' => 'todo']);

        $event = History::logActionFailure($task, actionName: 'drop');

        $this->assertEquals(Operation::ACTION, $event->operation->value());
        $this->assertFalse($event->was_successful);
        $this->assertTrue($event->diff()->isEmpty());
        $this->assertEmpty($event->diff()->changes());
        $this->assertNull($event->comment());
        $this->assertNull($event->details());
        $this->assertEquals('drop', $event->actionName());
    }

    /** @test */
    public function it_can_limit_the_number_of_entries_returned()
    {
        $task = SampleTask::create(['title' => 'Hello 60', 'description' => 'Make me 61', 'status' => 'todo']);

        for ($i = 0; $i < 5; $i++) {
            History::logActionSuccess($task);
        }

        $this->assertCount(5, History::of($task)->get());
        $this->assertCount(3, History::of($task)->get(limit: 3));
    }

    /** @test */
    public function it_can_paginate_the_entries_returned()
    {
        $task = SampleTask::create(['title' => 'Hello 70', 'description' => 'Make me 71', 'status' => 'todo']);

        for ($i = 0; $i < 20; $i++) {
            History::logActionSuccess($task);
        }

        $this->assertCount(20, History::of($task)->get());
        $paginatedResults = History::of($task)->paginate(10);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedResults);
        $this->assertCount(10, $paginatedResults->items());
        $this->assertEquals(1, $paginatedResults->currentPage());
        $this->assertTrue($paginatedResults->hasMorePages());

        $secondPage = History::of($task)->paginate(10, 2);
        $this->assertCount(10, $secondPage->items());
        $this->assertEquals(2, $secondPage->currentPage());
        $this->assertFalse($secondPage->hasMorePages());
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

    /** @test */
    public function it_can_return_the_user_that_made_the_change()
    {
        $user = $this->createUser();
        Auth::login($user);
        $task = SampleTask::create(['title' => 'User Obtain', 'status' => 'in-progress']);
        $event = History::begin($task);

        $this->assertEquals($user->id, $event->user_id);
        $this->assertInstanceOf(Auth::getProvider()->getModel(), $event->user);
    }

    /** @test */
    public function it_can_return_the_user()
    {
        $user = $this->createUser();
        Auth::login($user);
        $task = SampleTask::create(['title' => 'Eager Load User', 'status' => 'in-progress']);
        History::begin($task);
        History::addComment($task, 'Hello');
        History::addComment($task, 'Yellow');

        foreach (History::of($task)->get() as $event) {
            $this->assertInstanceOf(Auth::getProvider()->getModel(), $event->user);
        }
    }

    /** @test */
    public function if_there_was_no_recent_update_then_nothing_gets_logged()
    {
        $id = Str::ulid()->toBase58();
        SampleTask::create(['title' => $id, 'status' => 'todo']);

        $task = SampleTask::where('title', $id)->first();
        $this->assertNull(History::logRecentUpdate($task));
    }

    private function createUser(): Authenticatable
    {
        $userClass = Auth::getProvider()->getModel();
        $user = new $userClass();
        $user->name = 'Giovanni Gatto';
        $user->email = Str::ulid()->toBase58() . '@gatto.it';
        $user->password = Hash::make('passw...1234..eer');
        $user->save();

        return $user;
    }
}
