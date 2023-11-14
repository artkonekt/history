<?php

declare(strict_types=1);

/**
 * Contains the ViaProxy class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Models;

use Konekt\Concord\Proxies\EnumProxy;

/**
 * @method static Via WEB()
 * @method static Via CLI()
 * @method static Via QUEUE()
 * @method static Via UNKNOWN()
 */
class ViaProxy extends EnumProxy
{
}
