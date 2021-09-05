<?php

namespace App\Console\Commands\Account;

use App\Bitclout\Facades\Bitclout;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:update_balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update balance';

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
        $accounts = Account::select('public_key')->get();

        if ($accounts->isEmpty()) {
            return Log::info('Not found accounts');
        }

        $pks = $accounts->pluck('public_key')->all();

        $users = Bitclout::users($pks);

        if (!$users) {
            return Log::info('Not found users by accounts');
        }

        foreach ($users as $user) {
            if (!$profile = $user['ProfileEntryResponse']) {
                Log::info('Not found profile by user ', $user);
                continue;
            }

            $this->update($user['BalanceNanos'], $profile['Username'], $profile['PublicKeyBase58Check']);
        }

        return 0;
    }

    public function update($balance, $username, $publicKey) 
    {
        if ($account = Account::where('public_key', $publicKey)->first()) {
            $account->update([
                'balance' => $balance,
                'username' => $username,
            ]);
        }
    }
}
