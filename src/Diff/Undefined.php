<?php

declare(strict_types=1);

/**
 * Contains the Undefined class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Diff;

final class Undefined
{
    public const STRINGVAL = '::uNdEf::';

    public static function valueOrUndef(array $from, string $key): mixed
    {
        if (array_key_exists($key, $from)) {
            return self::STRINGVAL === $from[$key] ? new self() : $from[$key];
        }

        return new self();
    }

    public function __toString(): string
    {
        return self::STRINGVAL;
    }
}
