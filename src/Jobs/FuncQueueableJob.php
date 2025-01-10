<?php

namespace App\Helpers\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FuncQueueableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    # Может работать n мин.
    public int $timeout = 3600;

    public int $tries = 3;
    public int $backoff = 5; # секунды. Задержка после ошибки

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $func,
    )
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return $this->func;
    }
}
