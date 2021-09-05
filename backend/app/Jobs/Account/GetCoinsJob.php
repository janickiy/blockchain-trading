<?php

namespace App\Jobs\Account;

use App\Bitclout\Facades\Bitclout;
use App\Models\Account;
use App\Models\Coin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class GetCoinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Account $account)
    {
        //
    }

    public function middleware()
    {
        return [new WithoutOverlapping($this->account->id)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = Bitclout::users([$this->account->public_key], false);

        $coins = $users[0]['UsersYouHODL'] ?? [];

        if (!count($coins)) {
            return;
        }

        $coins = collect($coins)
            ->where('HasPurchased', true)
            ->mapWithKeys(function ($item) {
                $profile = $item['ProfileEntryResponse'];
                $publicKey = $profile['PublicKeyBase58Check'];
                $balance = $item['BalanceNanos'];

                $coin = Coin::find($publicKey);

                if (!$coin) {
                    $coin = Coin::create([
                        'public_key' => $publicKey,
                        'username' => $profile['Username'],
                        'price' => $profile['CoinPriceBitCloutNanos'],
                    ]);
                }

                return [
                    $publicKey => [
                        'balance' => $balance,
                    ],
                ];
            });

        $this->account->coins()->sync($coins);    
    }
}
