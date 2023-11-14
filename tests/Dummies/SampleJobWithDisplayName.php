<?php

declare(strict_types=1);

/**
 * Contains the SampleJobWithDisplayName class.
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

class SampleJobWithDisplayName implements ShouldQueue
{
    use Dispatchable;

    public const NICE_NAME = 'I am a job';

    public function __construct(
        protected SampleTask $task,
    ) {
    }

    public function displayName(): string
    {
        return self::NICE_NAME;
    }

    public function handle(): void
    {
        History::begin($this->task);
    }
}
