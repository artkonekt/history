<?php

declare(strict_types=1);

namespace Konekt\History\Tests\Dummies;

use Illuminate\Database\Eloquent\Model;
use Konekt\History\Contracts\ModelHistoryEvent;
use Konekt\History\Contracts\Trackable;

class SampleTrackableWithDefaultRedact extends Model implements Trackable
{
    protected $guarded = ['id'];

    protected $table = 'sample_trackable_clients';

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
        return null;
    }

    public function redactInHistory(string $field, mixed $value): bool|\Closure
    {
        return match ($field) {
            'name' => fn ($v) => '42' === $v ? 'The meaning of life' : 'REDACTED',
            'api_key' => true,
            default => false,
        };
    }
}
