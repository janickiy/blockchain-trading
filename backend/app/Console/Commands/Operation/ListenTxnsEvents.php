<?php

namespace App\Console\Commands\Operation;

use App\Models\Operation;
use App\Services\Operation\LaunchingService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ListenTxnsEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operation:listen-txns-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen txns events';

    private $operations;

    protected $ct;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private LaunchingService $launchingService)
    {
        parent::__construct();

        $nsqHost = env('NSQ_HOST');
        if (!$nsqHost) {
            throw new Exception('Empty config NSQ_HOST');
        }

        $this->ct = new \OneNsq\Client('tcp://' . $nsqHost);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $topic = 'txns-events';
        $channel = 'trading-tool-' . random_int(0, 65536) . '#ephemeral';

        $res = $this->ct->subscribe($topic, $channel);

        foreach ($res as $data) {
            if ($data === null) {
                continue;
            }

            $this->operations = Operation::wait()->get();

            $events = json_decode($data->msg, true);

            $events = collect($events)->filter(function ($v) {
                return isset($v['CreatedAt']);
            });

            foreach ($events as $txn) {
                if (!isset($txn['Event'])) {
                    continue;
                }

                match ($txn['Event']) {
                    'CreatorCoin' => $this->eventCreatorCoin($txn),
                    default => null
                };
            }
        };

        return 0;
    }

    private function eventCreatorCoin($txn)
    {
        Log::info('Event eventCreatorCoin');

        foreach ($this->operations as $operation) {

            try {
                if ($operation->target_public_key !== $txn['From']['PublicKeyBase58Check']) {
                    continue;
                }

                if ($txn['Operation'] === 'sell') {
                    $operation_action = Operation::OPERATION_ACTION_SELL;
                } elseif ($txn['Operation'] === 'buy') {
                    $operation_action = Operation::OPERATION_ACTION_BUY;
                } else {
                    $operation_action = null;
                }

                $payload = $operation->condition_payload;

                if ($operation_action != $payload['action']) {
                    continue;
                }

                if (
                    $operation->condition === Operation::OPERATION_CONDITION_GE_FRP
                    || $operation->condition === Operation::OPERATION_CONDITION_LE_FRP
                ) {
                    $value = round($txn['To']['CoinEntry']['CreatorBasisPoints'] / 100, 2);
                } else {
                    $value = round($txn['To']['CoinPriceBitCloutNanos'] / 10 ** 9, 9);
                }

                $payload['last_value'] = $value;
                $payload['operation_parent_id'] = $operation->id;

                if (
                    $this->launchingService->isConditionValid($operation->condition, $payload, $value)
                    && $operation->is_cloner
                ) {
                    $operation = Operation::create([
                        'from_account_id' => $operation->from_account_id,
                        'action' => $operation->action,
                        'target_public_key' => $txn['To']['PublicKeyBase58Check'],
                        'target_username' => $txn['To']['Username'],
                        'amount' => $operation->amount,
                        'condition' => $operation->condition,
                        'condition_payload' => $payload,
                        'status' => Operation::OPERATION_STATUS_IN_PROCESS,
                    ]);
                    Log::info('Operation was created from the cloner #' . $operation->id);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
}
