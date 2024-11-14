<?php

declare(strict_types=1);

/**
 * Contains the TrackableJob interface.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History\Contracts;

interface TrackableJob
{
    public function getJobTrackingId(): ?string;

    public function setJobTrackingId(string $trackingId): static;

    public function generateJobTrackingId(): static;

    public function hasTrackingId(): bool;

    public function doesNotHaveTrackingIdYet(): bool;
}
