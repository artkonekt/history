<?php

declare(strict_types=1);

/**
 * Contains the JobInfo class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Queue;

class JobInfo
{
    public const SERVICE_NAME = 'konektHistoryJobInfo';

    public function __construct(
        public readonly string $job,
        public readonly string $queue,
    ) {
    }
}
