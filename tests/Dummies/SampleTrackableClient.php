<?php

declare(strict_types=1);

/**
 * Contains the SampleTrackableClient class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-14
 *
 */

namespace Konekt\History\Tests\Dummies;

use Illuminate\Database\Eloquent\Model;
use Konekt\History\Contracts\ModelHistoryEvent;
use Konekt\History\Contracts\Trackable;

class SampleTrackableClient extends Model implements Trackable
{
    protected $guarded = ['id'];

    public function generateHistoryEventSummary(ModelHistoryEvent $event): ?string
    {
        return null;
    }

    public function includeAttributesInHistory(): ?array
    {
        return null;
    }

    public function excludeAttributesFromHistory(): ?array
    {
        return ['api_key'];
    }
}
