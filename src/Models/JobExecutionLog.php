<?php

declare(strict_types=1);

namespace Konekt\History\Models;

use Illuminate\Database\Eloquent\Model;
use Konekt\History\Contracts\JobExecutionLog as JobExecutionLogContract;

class JobExecutionLog extends Model implements JobExecutionLogContract
{
}
