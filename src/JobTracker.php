<?php

declare(strict_types=1);

/**
 * Contains the JobTracker class.
 *
 * @copyright   Copyright (c) 2024 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2024-11-12
 *
 */

namespace Konekt\History;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Konekt\History\Contracts\JobExecution;
use Konekt\History\Contracts\JobExecutionLog;
use Konekt\History\Contracts\JobStatus;
use Konekt\History\Contracts\SceneResolver;
use Konekt\History\Contracts\TrackableJob;
use Konekt\History\Models\JobExecutionProxy;
use Konekt\History\Models\JobStatusProxy;
use Konekt\History\Scenes\DefaultSceneResolver;
use Psr\Log\LogLevel;

class JobTracker
{
    protected static ?SceneResolver $sceneResolver = null;

    protected static string $sceneResolverClass = DefaultSceneResolver::class;

    private ?JobExecution $_model = null;

    public function __construct(
        protected TrackableJob $job,
    ) {
    }

    public static function of(TrackableJob $job): static
    {
        return new static($job);
    }

    /**
     * Creates a new Job execution entry with `queued` state
     */
    public static function createFor(TrackableJob $job, int $maxProgress = 100): JobExecution
    {
        return JobExecutionProxy::create(array_merge([
            'queued_at' => Carbon::now(),
            'progress_max' => $maxProgress,
        ], static::commonFields($job)));
    }

    /**
     * Mark the job execution as started. Call this at the beginning of your job's handle() method
     */
    public function started(): void
    {
        $this->model()?->update(['started_at' => Carbon::now()]);
    }

    /**
     * Mark the job execution as successfully completed
     */
    public function completed(?string $message = null): void
    {
        if (null !== $model = $this->model()) {
            if ($message) {
                $model->logInfo($message);
            }

            $model->update(['completed_at' => Carbon::now()]);
        }
    }

    /**
     * Mark the job execution as failed
     */
    public function failed(?string $message = null): void
    {
        if (null !== $model = $this->model()) {
            if ($message) {
                $model->logInfo($message);
            }

            $model->update(['failed_at' => Carbon::now()]);
        }
    }

    public function setProgressMax(int $max): void
    {
        $this->model()?->setProgressMax($max);
    }

    public function getProgressMax(): int
    {
        return $this->model()?->getProgressMax() ?? 0;
    }

    public function advance(int $steps = 1): void
    {
        $this->model()?->advance($steps);
    }

    public function getProgress(): int
    {
        return $this->model()?->getProgress() ?? 0;
    }

    public function getProgressAsPercent(): float
    {
        return $this->model()?->getProgressAsPercent() ?? 0.0;
    }

    public function status(): JobStatus
    {
        return $this->model()?->status() ?? JobStatusProxy::create();
    }

    public function log(string $message, string $level = LogLevel::INFO, array $context = []): ?JobExecutionLog
    {
        if (null === $model = $this->model()) {
            return null;
        }

        return match ($level) {
            LogLevel::EMERGENCY => $model->logEmergency($message, $context),
            LogLevel::ALERT => $model->logAlert($message, $context),
            LogLevel::CRITICAL => $model->logCritical($message, $context),
            LogLevel::ERROR => $model->logError($message, $context),
            LogLevel::WARNING => $model->logWarning($message, $context),
            LogLevel::NOTICE => $model->logNotice($message, $context),
            LogLevel::INFO => $model->logInfo($message, $context),
            LogLevel::DEBUG => $model->logDebug($message, $context),
            default => throw new \InvalidArgumentException("Unknown log level: $level"),
        };
    }

    protected static function commonFields(TrackableJob $job): array
    {
        [$via, $scene] = static::sceneResolver()->get();

        return [
            'via' => $via,
            'scene' => $scene,
            'job_class' => get_class($job),
            'job_uuid' => $job instanceof Job ? $job->uuid() : null,
            'tracking_id' => $job->getJobTrackingId(),
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];
    }

    protected static function sceneResolver(): SceneResolver
    {
        if (null === static::$sceneResolver) {
            static::$sceneResolver = App::make(static::$sceneResolverClass);
        }

        return static::$sceneResolver;
    }

    protected function model(): ?JobExecution
    {
        return $this->_model ??= JobExecutionProxy::findByTrackingId($this->job->getJobTrackingId());
    }
}
