<?php

declare(strict_types=1);

/**
 * Contains the JobStatus enum class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History\Models;

use Konekt\Enum\Enum;
use Konekt\History\Contracts\JobStatus as JobStatusContract;

/**
 * @method static JobStatus QUEUED()
 * @method static JobStatus PROCESSING()
 * @method static JobStatus COMPLETED()
 * @method static JobStatus FAILED()
 *
 * @method bool isQueued()
 * @method bool isProcessing()
 * @method bool isCompleted()
 * @method bool isFailed()
 *
 * @property-read bool $is_queued
 * @property-read bool $is_processing
 * @property-read bool $is_completed
 * @property-read bool $is_failed
 */
class JobStatus extends Enum implements JobStatusContract
{
    public const __DEFAULT = self::QUEUED;

    public const QUEUED = 'queued';
    public const PROCESSING = 'processing';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';

    public function isActive(): bool
    {
        return $this->isAnyOf(self::QUEUED(), self::PROCESSING());
    }

    public function hasEnded(): bool
    {
        return $this->isAnyOf(self::COMPLETED(), self::FAILED());
    }
}
