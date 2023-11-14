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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Konekt\History\Contracts\ModelHistoryEvent;
use Konekt\History\Diff\Diff;
use Konekt\History\Models\ModelHistoryEventProxy;
use Konekt\History\Models\Via;
use Konekt\History\Queue\JobInfo;

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
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => $model->created_at ?? now(),
            'diff' => Diff::fromModel($model)->toArray(),
        ], static::commonFields($model)));
    }

    public static function addUpdate(Model $model): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => $model->updated_at ?? now(),
            'diff' => Diff::fromModel($model)->toArray(),
        ], static::commonFields($model)));
    }

    public static function addComment(Model $model, string $comment): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => now(),
            'comment' => $comment,
            'diff' => [],
        ], static::commonFields($model)));
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

    protected static function commonFields(Model $model): array
    {
        if (App::runningInConsole()) {
            /** @var null|JobInfo $jobInfo */
            $jobInfo = App::get(JobInfo::SERVICE_NAME);
            if (null === $jobInfo) {
                $via = Via::CLI;
                $scene = $_SERVER['argv'][1] ?? $_SERVER['argv'][0] ?? null;
                if (is_string($scene) && str_starts_with($scene, '-')) {
                    $scene = $_SERVER['argv'][0] ?? null;
                }
            } else {
                $via = Via::QUEUE;
                $scene = $jobInfo->job;
            }
        } else {
            $via = Via::WEB;
            $scene = Request::url();
        }

        return [
            'via' => $via,
            'scene' => $scene,
            'model_type' => morph_type_of($model),
            'model_id' => $model->id,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];
    }
}
