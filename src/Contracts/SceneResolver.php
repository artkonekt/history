<?php

declare(strict_types=1);

/**
 * Contains the SceneResolver interface.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Contracts;

interface SceneResolver
{
    public function via(): ?Via;

    public function scene(): ?string;

    /** @return array{0: ?Via, 1: ?string}  */
    public function get(): array;
}
