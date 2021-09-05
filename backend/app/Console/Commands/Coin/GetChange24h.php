<?php

namespace App\Console\Commands\Coin;

use App\Jobs\Coin\GetChange24hJob;
use App\Models\Coin;
use Illuminate\Console\Command;

class GetChange24h extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coin:get-change-24h';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the change in 24 hours';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
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
        Coin::chunk(10, function($coins) {
            GetChange24hJob::dispatch($coins);
        });

        return 0;
    }
}
