<?php

namespace App\Console\Commands\Operation;

use App\Services\Operation\LaunchingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Launching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operation:launching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launching';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private LaunchingService $launchingService)
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

        $this->launchingService->launch();

        return 0;
    }
}
