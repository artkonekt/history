<?php

declare(strict_types=1);

/**
 * Contains the SampleJob class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Tests\Dummies;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Konekt\History\History;

class SampleJob implements ShouldQueue
{
    use Dispatchable;

    public function __construct(
        protected SampleTask $task,
    ) {
    }

    public function handle(): void
    {
        History::begin($this->task);
    }
}
