<?php

declare(strict_types=1);

/**
 * Contains the Change class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Diff;

class Change
{
    public function __construct(
        public readonly mixed $old,
        public readonly mixed $new,
    ) {
    }
}
