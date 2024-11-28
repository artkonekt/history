<?php

declare(strict_types=1);

namespace Konekt\History\Models;

/**
 * Contains the JobExecution model class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Konekt\History\Contracts\JobExecution as JobExecutionContract;
use Konekt\History\Contracts\JobExecutionLog as JobExecutionLogContract;
use Konekt\History\Contracts\JobStatus as JobStatusContract;
use Psr\Log\LogLevel;

/**
 * @property int $id
 * @property int $user_id
 * @property Operation $operation
 * @property Via $via
 * @property string|null $scene
 * @property string|null $ip_address
 * @property string|null $user_agent
 *
 * @property string|null $job_uuid
 * @property string $job_class
 * @property string $tracking_id
 * @property int $progress_max;
 * @property int $current_progress;
 *
 * @property Carbon|null $queued_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $failed_at
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Model|Authenticatable $user
 * @property-read Collection $logs
 *
 * @method static Builder ofJobClass(string $jobClass)
 * @method static Builder actives()
 * @method static Builder ended()
 */
class JobExecution extends Model implements JobExecutionContract
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'current_progress' => 'integer',
        'progress_max' => 'integer',
    ];

    public static function findByTrackingId(string $id): ?self
    {
        return static::where('tracking_id', $id)->first();
    }

    public static function byJobClass(string $jobClass, bool $activeOnesOnly = false, ?int $limit = null): Collection
    {
        $query = static::ofJobClass($jobClass)->orderBy('queued_at', 'desc');
        if ($activeOnesOnly) {
            $query->whereNull('failed_at')->whereNull('completed_at');
        }

        if (null !== $limit) {
            $query->take($limit);
        }

        return $query->get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('konekt.history.user_model', Auth::getProvider()->getModel()));
    }

    public function status(): JobStatusContract
    {
        if (null !== $this->failed_at) {
            return JobStatusProxy::FAILED();
        }

        if (null !== $this->completed_at) {
            return JobStatusProxy::COMPLETED();
        }

        if (null !== $this->started_at) {
            return JobStatusProxy::PROCESSING();
        }

        return JobStatusProxy::QUEUED();
    }

    public function setProgressMax(int $max): void
    {
        $this->update(['progress_max' => $max]);
    }

    public function getProgressMax(): int
    {
        return $this->progress_max;
    }

    public function advance(int $steps = 1): void
    {
        $this->update(['current_progress' => $this->current_progress + $steps]);
    }

    public function getProgress(): int
    {
        return $this->current_progress;
    }

    public function getProgressAsPercent(int $precision = 1): float
    {
        return $this->progress_max <= 0 ? 0.00 : round($this->current_progress / $this->progress_max * 100, $precision);
    }

    public function logEmergency(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::EMERGENCY, $context);
    }

    public function logAlert(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::ALERT, $context);
    }

    public function logCritical(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::CRITICAL, $context);
    }

    public function logError(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::ERROR, $context);
    }

    public function logWarning(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::WARNING, $context);
    }

    public function logNotice(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::NOTICE, $context);
    }

    public function logInfo(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::INFO, $context);
    }

    public function logDebug(string $message, array $context = []): JobExecutionLogContract
    {
        return $this->log($message, LogLevel::DEBUG, $context);
    }

    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function logs(): HasMany
    {
        return $this->hasMany(JobExecutionLogProxy::modelClass(), 'job_execution_id', 'id');
    }

    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }

    public function lastTimeSomethingHasHappenedWithIt(): Carbon
    {
        $lastLogEventDate = $this->logs()->max('happened_at');
        $dates = [
            $lastLogEventDate ? Carbon::parse($lastLogEventDate) : null,
            $this->queued_at,
            $this->started_at,
            $this->completed_at,
            $this->failed_at,
        ];

        return max(...$dates);
    }

    protected function scopeOfJobClass(Builder $query, string $jobClass): Builder
    {
        return $query->where('job_class', $jobClass);
    }

    protected function scopeActives(Builder $query): Builder
    {
        return $query->whereNull('completed_at')->whereNull('failed_at');
    }

    protected function scopeEnded(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at')->orWhereNotNull('failed_at');
    }

    protected function log(string $message, string $level, array $context): JobExecutionLogContract
    {
        return $this->logs()->create([
            'message' => $message,
            'happened_at' => Carbon::now(),
            'level' => $level,
            'context' => $context,
        ]);
    }
}
