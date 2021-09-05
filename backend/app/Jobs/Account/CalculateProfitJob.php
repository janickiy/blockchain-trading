<?php

namespace App\Jobs\Account;

use App\Bitclout\Facades\Bitclout;
use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class CalculateProfitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private Account $account)
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
        $transactions = Bitclout::transactionInfo($this->account->public_key);

        $profits = $this->getProfits($transactions);

        foreach ($profits as $publicKey => $item) {

            $data = [
                'spent' => $item['buy'] ?? 0,
                'sold' => $item['sell'] ?? 0,
            ];

            DB::table('coin_account')
                ->where('account_id', $this->account->id)
                ->where('public_key', $publicKey)
                ->update($data);
        }
    }

    private function getProfits(array $transactions): array
    {
        $profits = [];

        collect($transactions)
            ->where('TransactionType', 'CREATOR_COIN')
            ->each(function ($transaction) use (&$profits) {

                $meta = $transaction['TransactionMetadata'];

                $affectedPublicKeys = collect($meta['AffectedPublicKeys']);

                $operationType = $meta['CreatorCoinTxindexMetadata']['OperationType'];

                if ($operationType === 'buy') {
                    $publicKey = $affectedPublicKeys->firstWhere('Metadata', 'CreatorPublicKey')['PublicKeyBase58Check'] ?? null;
                } else {
                    $publicKey = $affectedPublicKeys->firstWhere('Metadata', 'BasicTransferOutput')['PublicKeyBase58Check'] ?? null;
                }

                if ($publicKey) {
                    $operationType = $meta['CreatorCoinTxindexMetadata']['OperationType'];

                    if ($operationType === 'sell') {
                        $amount = $meta['CreatorCoinTxindexMetadata']['CreatorCoinToSellNanos'];
                    } else if ($operationType === 'buy') {
                        $amount = $meta['CreatorCoinTxindexMetadata']['BitCloutToSellNanos'];
                    }

                    if (isset($amount)) {
                        if (isset($profits[$publicKey][$operationType])) {
                            $profits[$publicKey][$operationType] += $amount;
                        } else {
                            $profits[$publicKey][$operationType] = $amount;
                        }
                    }
                }
            });

        return $profits;
    }
}
