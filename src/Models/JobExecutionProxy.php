<?php

declare(strict_types=1);

/**
 * Contains the JobExecutionProxy class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History\Models;

use Konekt\Concord\Proxies\ModelProxy;

/**
 * @method static JobExecution|null findByTrackingId(string $id)
 */
class JobExecutionProxy extends ModelProxy
{
}
