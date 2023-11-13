<?php

declare(strict_types=1);

/**
 * Contains the History class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-13
 *
 */

namespace Konekt\History;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Konekt\History\Contracts\ModelHistoryEvent;
use Konekt\History\Diff\Diff;
use Konekt\History\Models\ModelHistoryEventProxy;

class History
{
    public function __construct(
        protected ?Model $ofModel = null,
    ) {
    }

    public static function of(Model $model): static
    {
        return new static($model);
    }

    public static function begin(Model $model): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create([
            'model_type' => morph_type_of($model),
            'model_id' => $model->id,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'happened_at' => $model->created_at ?? now(),
            'diff' => Diff::fromModel($model)->toArray(),
        ]);
    }

    public static function addUpdate(Model $model): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create([
            'model_type' => morph_type_of($model),
            'model_id' => $model->id,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'happened_at' => $model->updated_at ?? now(),
            'diff' => Diff::fromModel($model)->toArray(),
        ]);
    }

    public static function addComment(Model $model, string $comment): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create([
            'model_type' => morph_type_of($model),
            'model_id' => $model->id,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'happened_at' => now(),
            'comment' => $comment,
            'diff' => [],
        ]);
    }

    public function get(bool $latestOnTop = true): Collection
    {
        /** @var Builder $query */
        $query = ModelHistoryEventProxy::query();
        if (null !== $this->ofModel) {
            $query->where('model_type', morph_type_of($this->ofModel))
                ->where('model_id', $this->ofModel->id);
        }

        return $query->orderBy('happened_at', $latestOnTop ? 'desc' : 'asc')->get();
    }
}
