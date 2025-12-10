# Job Execution Tracking

Besides Eloquent model history, it is also possible to track a laravel job in real time, so that you can show the status
and updates of a background job on frontends. It is similar to logging, but is always scoped to a given job execution,
and adds state to each job execution.

## Features

- Track the execution status of Laravel Jobs
- Set and read the completion % of a job execution
- Write logs for job executions
- Detect the user that has executed the job

## Example

```php
class MyJob implements \Konekt\History\Contracts\TrackableJob
{
    use \Konekt\History\Concerns\CanBeTracked;
    
    public function __construct(private array $dataToProcess)
    {        
    }
    
    public function handle()
    {
        $tracker = $this->jobTracker();
        $tracker->setProgressMax(count($this->dataToProcess));
        $tracker->started();
        try {
            foreach ($this->dataToProcess as $data) {
                Do::something()->withThe($data);
                $tracker->advance();
                $tracker->logInfo('An entry was processed');
            }
            $tracker->completed();
        } catch (\Throwable $e) {
            $tracker->failed($e->getMessage());
        }
    }
}

MyJob::dispatch($myDataToProcess);
```

