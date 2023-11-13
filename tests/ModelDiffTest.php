<?php

declare(strict_types=1);

/**
 * Contains the ModelDiffTest class.
 *
 * @copyright   Copyright (c) 2023 Vanilo UG
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Tests;

use Konekt\History\Diff\Diff;
use Konekt\History\Diff\Undefined;
use Konekt\History\Tests\Dummies\SampleTask;

class ModelDiffTest extends TestCase
{
    /** @test */
    public function it_can_process_the_changes_of_an_eloquent_model()
    {
        $task = SampleTask::create(['title' => 'This is a task', 'status' => 'backlog']);

        $task->description = 'Do this';
        $task->status = 'to-do';
        $task->assigned_to = 27;
        $task->save();

        $diff = Diff::fromModel($task);

        $this->assertEmpty(
            array_diff(
                ['description', 'status', 'assigned_to'],
                array_keys($diff->changes()
                )
            )
        );
        $this->assertEquals('Do this', $diff->new('description'));
        $this->assertInstanceOf(Undefined::class, $diff->old('description'));
        $this->assertEquals('to-do', $diff->new('status'));
        $this->assertEquals(27, $diff->new('assigned_to'));
    }
}
