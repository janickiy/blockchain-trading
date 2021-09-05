<?php

namespace App\Console\Commands\Account;

use App\Jobs\Account\CalculateProfitJob;
use App\Models\Account;
use Illuminate\Console\Command;

class CalculateProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:calculate-profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate profits';

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
        Account::get()
            ->each(function ($account) {
                CalculateProfitJob::dispatch($account);
            });
            
        return 0;
    }
}
