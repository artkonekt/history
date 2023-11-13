<?php

declare(strict_types=1);

/**
 * Contains the Diff class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Diff;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Diff implements Arrayable
{
    /** @var Change[] $changes */
    protected array $changes = [];

    public function __construct(array $changes)
    {
        foreach ($changes as $field => $values) {
            $this->changes[$field] = new Change(Undefined::valueOrUndef($values, 'o'), Undefined::valueOrUndef($values, 'n'));
        }
    }

    public static function fromModel(Model $model, array $ignore = ['id', 'created_at', 'updated_at']): self
    {
        $changes = [];
        $changedAttributes = $model->getChanges();
        if (empty($changedAttributes) && $model->wasRecentlyCreated) {
            $changedAttributes = $model->getOriginal();
        }
        foreach ($changedAttributes as $field => $newValue) {
            if (!in_array($field, $ignore)) {
                $changes[$field] = ['n' => $newValue];
            }
        }

        return new self($changes);
    }

    public static function fromAttributeSets(array $before, array $after): self
    {
        $changes = [];
        $fields = array_unique(array_merge(array_keys($before), array_keys($after)));
        foreach ($fields as $field) {
            $values = [];
            if (array_key_exists($field, $before)) {
                $values['o'] = $before[$field];
            }
            if (array_key_exists($field, $after)) {
                $values['n'] = $after[$field];
            }

            if ($values['o'] !== $values['n']) {
                $changes[$field] = $values;
            }
        }

        return new self($changes);
    }

    public function hasChanged(string $field): bool
    {
        return array_key_exists($field, $this->changes);
    }

    public function isUnchanged(string $field): bool
    {
        return !$this->hasChanged($field);
    }

    /** @return Change[]
     */
    public function changes(): array
    {
        return $this->changes;
    }

    public function changeOf($field): ?Change
    {
        return $this->changes[$field] ?? null;
    }

    public function changeCount(): int
    {
        return count($this->changes);
    }

    public function old(string $field): mixed
    {
        return $this->changeOf($field)?->old;
    }

    public function new(string $field): mixed
    {
        return $this->changeOf($field)?->new;
    }

    public function toArray()
    {
        return Arr::mapWithKeys(
            $this->changes,
            function (Change $change, string $field) {
                $values = [];
                if (!$change->old instanceof Undefined) {
                    $values['o'] = $change->old;
                }
                if (!$change->new instanceof Undefined) {
                    $values['n'] = $change->new;
                }

                return [$field => $values];
            },
        );
    }
}
