<?php

declare(strict_types=1);

namespace Konekt\History\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Konekt\History\Contracts\JobExecution;
use Konekt\History\Contracts\JobExecutionLog as JobExecutionLogContract;

/**
 * @property int $id
 * @property int $job_execution_id;
 * @property string $level
 * @property string $message
 * @property array $context
 *
 * @property Carbon $happened_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read JobExecution $jobExecution
 */
class JobExecutionLog extends Model implements JobExecutionLogContract
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'context' => 'json',
        'happened_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function jobExecution(): BelongsTo
    {
        return $this->belongsTo(JobExecutionProxy::modelClass(), 'job_execution_id', 'id');
    }

    public function getExecution(): JobExecution
    {
        return $this->jobExecution;
    }
}
