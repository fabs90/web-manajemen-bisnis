<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Throwable;

class SendErrorLogJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public string $message;

    public string $file;

    public int $line;

    public string $trace;

    public ?array $userData = null;

    /**
     * Create a new job instance.
     */
    public function __construct(Throwable $exception)
    {
        $this->message = $exception->getMessage();
        $this->file = $exception->getFile();
        $this->line = $exception->getLine();
        $this->trace = $exception->getTraceAsString();

        $user = request()->user();
        if ($user) {
            $this->userData = [
                'name' => $user->name,
                'email' => $user->email,
            ];
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Http::post(config('services.n8n.webhook'), [
            'app' => config('app.name'),
            'env' => app()->environment(),
            'user' => $this->userData,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'trace' => $this->trace,
        ]);
    }
}
