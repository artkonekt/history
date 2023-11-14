<?php

declare(strict_types=1);

/**
 * Contains the SampleTrackableProduct class.
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

class SampleTrackableProduct extends Model implements Trackable
{
    protected $guarded = ['id'];

    public function generateHistoryEventSummary(ModelHistoryEvent $event): ?string
    {
        if ($event->isACommentOnlyEntry()) {
            return 'Comment added';
        } elseif ($event->isASingleFieldChange()) {
            $changedField = $event->diff()->singleChangedFieldName();
            return match ($changedField) {
                'name' => "Renamed to: " . $event->diff()->new($changedField),
                'price' => "Price changed to " . $event->diff()->new($changedField),
                'is_active' => $event->diff()->new($changedField) ? 'Activated' : 'Deactivated',
                default => null,
            };
        }

        return null;
    }

    public function includeAttributesInHistory(): ?array
    {
        return ['name', 'price', 'is_active'];
    }

    public function excludeAttributesFromHistory(): ?array
    {
        return null;
    }
}
