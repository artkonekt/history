<?php

declare(strict_types=1);

/**
 * Contains the JobExecution interface.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History\Contracts;

use Illuminate\Support\Collection;

interface JobExecution
{
    public static function findByTrackingId(string $id): ?self;

    public function status(): JobStatus;

    public function setProgressMax(int $max): void;

    public function getProgressMax(): int;

    public function advance(int $steps = 1): void;

    public function getProgress(): int;

    public function getProgressAsPercent(): float;

    public function logEmergency(string $message, array $context = []): JobExecutionLog;

    public function logAlert(string $message, array $context = []): JobExecutionLog;

    public function logCritical(string $message, array $context = []): JobExecutionLog;

    public function logError(string $message, array $context = []): JobExecutionLog;

    public function logWarning(string $message, array $context = []): JobExecutionLog;

    public function logNotice(string $message, array $context = []): JobExecutionLog;

    public function logInfo(string $message, array $context = []): JobExecutionLog;

    public function logDebug(string $message, array $context = []): JobExecutionLog;

    /** @return Collection|JobExecutionLog[] */
    public function getLogs(): Collection;
}
