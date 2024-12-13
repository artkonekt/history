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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Konekt\History\Contracts\ModelHistoryEvent;
use Konekt\History\Contracts\SceneResolver;
use Konekt\History\Diff\Diff;
use Konekt\History\Models\ModelHistoryEventProxy;
use Konekt\History\Models\Operation;
use Konekt\History\Scenes\DefaultSceneResolver;

class History
{
    protected static ?SceneResolver $sceneResolver = null;

    protected static string $sceneResolverClass = DefaultSceneResolver::class;

    public function __construct(
        protected ?Model $ofModel = null,
    ) {
    }

    public static function of(Model $model): static
    {
        return new static($model);
    }

    public static function begin(Model $model, ?string $comment = null): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => $model->created_at ?? now(),
            'diff' => Diff::fromModel($model)->toArray(),
            'operation' => Operation::CREATE,
            'comment' => $comment,
        ], static::commonFields($model)));
    }

    public static function logRecentUpdate(Model $model, ?string $comment = null): ?ModelHistoryEvent
    {
        $diff = Diff::fromModel($model);
        if ($diff->isEmpty()) {
            return null;
        }

        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => $model->updated_at ?? now(),
            'diff' => $diff->toArray(),
            'operation' => Operation::UPDATE,
            'comment' => $comment,
        ], static::commonFields($model)));
    }

    public static function logUpdate(Model $model, array $before, ?string $comment = null): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => now(),
            'diff' => Diff::fromModel($model, $before)->toArray(),
            'operation' => Operation::UPDATE,
            'comment' => $comment,
        ], static::commonFields($model)));
    }

    public static function addComment(Model $model, string $comment): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => now(),
            'comment' => $comment,
            'operation' => Operation::COMMENT,
            'diff' => [],
        ], static::commonFields($model)));
    }

    public static function logActionSuccess(Model $model, ?string $details = null): ModelHistoryEvent
    {
        return static::logAction($model, true, $details);
    }

    public static function logActionFailure(Model $model, ?string $details = null): ModelHistoryEvent
    {
        return static::logAction($model, false, $details);
    }

    public static function logDeletion(Model $model, ?string $comment = null): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => $model->deleted_at ?? now(),
            'comment' => $comment,
            'operation' => Operation::DELETE,
            'diff' => [],
        ], static::commonFields($model)));
    }

    public static function useSceneResolver(string|SceneResolver $resolver): void
    {
        if ($resolver instanceof SceneResolver) {
            static::$sceneResolver = $resolver;
            static::$sceneResolverClass = $resolver::class;

            return;
        }

        if (!in_array(SceneResolver::class, class_implements($resolver) ?: [])) {
            throw new \RuntimeException("The `$resolver` class does not implement the `ScreenResolver` interface");
        }

        static::$sceneResolverClass = $resolver;
        static::$sceneResolver = null;
    }

    public function get(bool $latestOnTop = true): Collection
    {
        /** @var Builder $query */
        $query = ModelHistoryEventProxy::query();
        if (null !== $this->ofModel) {
            $query->where('model_type', morph_type_of($this->ofModel))
                ->where('model_id', $this->ofModel->id);
        }

        return $query->with('user')->orderBy('happened_at', $latestOnTop ? 'desc' : 'asc')->get();
    }

    protected static function commonFields(Model $model): array
    {
        [$via, $scene] = static::sceneResolver()->get();

        return [
            'via' => $via,
            'scene' => $scene,
            'model_type' => morph_type_of($model),
            'model_id' => $model->id,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Str::limit(Request::userAgent(), 255, ''),
        ];
    }

    protected static function sceneResolver(): SceneResolver
    {
        if (null === static::$sceneResolver) {
            static::$sceneResolver = App::make(static::$sceneResolverClass);
        }

        return static::$sceneResolver;
    }

    protected static function logAction(Model $model, bool $wasSuccessful, ?string $details = null): ModelHistoryEvent
    {
        return ModelHistoryEventProxy::create(array_merge([
            'happened_at' => now(),
            'comment' => null,
            'details' => $details,
            'operation' => Operation::ACTION,
            'was_successful' => $wasSuccessful,
            'diff' => [],
        ], static::commonFields($model)));
    }
}
