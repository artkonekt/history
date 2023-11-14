<?php

declare(strict_types=1);

/**
 * Contains the Via class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Models;

use Konekt\Enum\Enum;
use Konekt\History\Contracts\Via as ViaContract;

class Via extends Enum implements ViaContract
{
    public const __DEFAULT = self::UNKNOWN;
    public const UNKNOWN = null;
    public const WEB = 'web';
    public const CLI = 'cli';
    public const QUEUE = 'queue';
}
