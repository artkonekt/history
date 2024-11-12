<?php

declare(strict_types=1);

namespace Konekt\History\Models;

use Konekt\Concord\Proxies\EnumProxy;

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
class JobStatusProxy extends EnumProxy
{
}
