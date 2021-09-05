<?php

namespace App\Jobs\Coin;

use App\Bitclout\Facades\Bitclout;
use App\Models\Coin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class GetChange24hJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Collection $coins)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pks = $this->coins->pluck('public_key')->all();

        $history = Bitclout::priceHistory($pks);

        collect($history)
            ->groupBy('PublicKeyBase58Check')
            ->each(function ($prices, $publicKey) {

                if ($coin = Coin::find($publicKey)) {
                    if (count($prices) > 1) {
                        $currentPrice = $prices[0]['CoinPriceBitCloutNanos'];
                        $prevPrice = $prices[1]['CoinPriceBitCloutNanos'];

                        $percent = 0;

                        for ($i = 1; $i < count($prices); $i++) {
                            $price = $prices[$i];

                            if (strtotime($price['CreatedAt']) <= strtotime('-24hours')) {
                                if ($price['CoinPriceBitCloutNanos'] > $currentPrice) {
                                    $diff = $price['CoinPriceBitCloutNanos'] - $currentPrice;
                                    if ($price['CoinPriceBitCloutNanos']) {
                                        $percent = $diff * 100 / $price['CoinPriceBitCloutNanos'] * -1;
                                    } else {
                                        $percent = $diff * -1;
                                    }
                                } else if ($price['CoinPriceBitCloutNanos'] < $currentPrice) {
                                    $diff = $currentPrice - $price['CoinPriceBitCloutNanos'];
                                    if ($currentPrice) {
                                        $percent = $diff * 100 / $currentPrice;
                                    } else {
                                        $percent = $diff;
                                    }
                                } else {
                                    $percent = 0;
                                }

                                break;
                            }
                        }

                        $coin->update([
                            'change24h' => round($percent, 2),
                            'prev_price' => $prevPrice,
                        ]);
                    }
                }
            });
    }
}
