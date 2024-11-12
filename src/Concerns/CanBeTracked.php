<?php

declare(strict_types=1);

/**
 * Contains the CanBeTracked trait.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History\Concerns;

use Illuminate\Support\Str;

trait CanBeTracked
{
    public string $job_tracking_id;

    public function getJobTrackingId(): string
    {
        return $this->job_tracking_id;
    }

    public function setJobTrackingId(string $trackingId): static
    {
        if (strlen($trackingId) > 22) {
            throw new \InvalidArgumentException('The job tracking ID can not exceed 22 characters');
        }

        $this->job_tracking_id = $trackingId;

        return $this;
    }

    public function generateJobTrackingId(): static
    {
        return $this->setJobTrackingId(Str::ulid()->toBase58());
    }
}
