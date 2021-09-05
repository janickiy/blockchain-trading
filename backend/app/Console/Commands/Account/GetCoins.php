<?php

namespace App\Console\Commands\Account;

use App\Jobs\Account\GetCoinsJob;
use App\Models\Account;
use Illuminate\Console\Command;

class GetCoins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:get-coins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get coins for account';

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
            ->each(function($account) {
                GetCoinsJob::dispatch($account);
            });

        return 0;
    }
}
