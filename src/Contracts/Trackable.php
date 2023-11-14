<?php

declare(strict_types=1);

/**
 * Contains the Trackable interface.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Contracts;

interface Trackable
{
    public function generateHistoryEventSummary(ModelHistoryEvent $event): ?string;

    public function includeAttributesInHistory(): ?array;

    public function excludeAttributesFromHistory(): ?array;
}
