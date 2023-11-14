<?php

declare(strict_types=1);

/**
 * Contains the ModelHistoryEvent class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Konekt\Enum\Eloquent\CastsEnums;
use Konekt\History\Contracts\ModelHistoryEvent as ModelHistoryEventContract;
use Konekt\History\Contracts\Trackable;
use Konekt\History\Diff\Diff;

/**
 * @property int $id
 * @property int $user_id
 * @property Via $via
 * @property string|null $scene
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $model_id
 * @property string $model_type
 * @property array $diff;
 * @property string|null $comment
 * @property Carbon $happened_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Model|Authenticatable $user
 * @property-read Model $model
 */
class ModelHistoryEvent extends Model implements ModelHistoryEventContract
{
    use CastsEnums;

    protected $table = 'model_history';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $enums = [
        'via' => 'ViaProxy@enumClass'
    ];

    protected ?Diff $_diffCache = null;

    protected $casts = [
        'diff' => 'json',
        'happened_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function diff(): Diff
    {
        // No caching for WIP and recently changed entries since we can't track at which point the cache was created
        if (null !== $this->_diffCache && !$this->isDirty() && !$this->wasRecentlyCreated && empty($this->getChanges())) {
            return $this->_diffCache;
        }
        $this->_diffCache = new Diff($this->diff);

        return $this->_diffCache;
    }

    public function summary(): string
    {
        if ($this->model instanceof Trackable) {
            return $this->model->generateHistoryEventSummary($this) ?? '';
        }

        return '';
    }

    public function comment(): ?string
    {
        return $this->comment;
    }

    public function isASingleFieldChange(): bool
    {
        return 1 === $this->diff()->changeCount();
    }

    public function isNoFieldChangeEntry(): bool
    {
        return 0 === $this->diff()->changeCount();
    }

    public function isACommentOnlyEntry(): bool
    {
        return 0 === $this->diff()->changeCount() && !is_null($this->comment);
    }
}
