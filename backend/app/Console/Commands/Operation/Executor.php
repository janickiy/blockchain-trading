<?php

namespace App\Console\Commands\Operation;

use App\Services\Operation\ExecutorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Executor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operation:executor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executor';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private ExecutorService $executorService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info(__CLASS__ . ' started');

        $this->executorService->launch();

        return 0;
    }
}
