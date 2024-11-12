<?php

declare(strict_types=1);

/**
 * Contains the JobExecutionLog interface.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History\Contracts;

use DateTimeInterface;

interface JobExecutionLog
{
    public function getExecution(): JobExecution;

    public function getMessage(): string;

    public function getHappenedAt(): DateTimeInterface;

    public function getLevel(): string;

    public function getContext(): array;
}
