<?php

declare(strict_types=1);

/**
 * Contains the ModelHistoryEvent interface.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Contracts;

use Konekt\History\Diff\Diff;

interface ModelHistoryEvent
{
    public function diff(): Diff;

    public function summary(): string;

    public function comment(): ?string;

    public function isASingleFieldChange(): bool;

    public function isNoFieldChangeEntry(): bool;

    public function isACommentOnlyEntry(): bool;
}
