<?php

declare(strict_types=1);

/**
 * Contains the ModuleServiceProvider class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-12
 *
 */

namespace Konekt\History\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Konekt\History\Models\ModelHistoryEvent;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        ModelHistoryEvent::class,
    ];
}
