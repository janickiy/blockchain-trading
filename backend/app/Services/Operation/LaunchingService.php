<?php

namespace App\Services\Operation;

use App\Bitclout\Facades\Bitclout;
use App\Models\Operation;
use Illuminate\Support\Facades\Log;

class LaunchingService
{
    public function launch()
    {
        while (true) {
            Operation::wait()
                ->where('is_cloner', false)
                ->chunk(10, function ($operations) {
                    $pks = $operations->pluck('target_public_key')->all();
                    $profiles = Bitclout::users($pks);

                    $operations->each(function ($operation) use ($profiles) {
                        $profile = $profiles->firstWhere('PublicKeyBase58Check', $operation->target_public_key);

                        if (isset($profile['ProfileEntryResponse'])) {
                            $profile = $profile['ProfileEntryResponse'];
                            $this->processOperation($operation, $profile);
                        }
                    });
                });
        }
    }


    public function processOperation(Operation $operation, array $profile)
    {
        Log::info('Process operation #' . $operation->id);

        $payload = $operation->condition_payload;
        if (
            $operation->condition === Operation::OPERATION_CONDITION_GE_FRP ||
            $operation->condition === Operation::OPERATION_CONDITION_LE_FRP
        ) {
            $value = round($profile['CoinEntry']['CreatorBasisPoints'] / 100, 2);
        } else {
            $value = round($profile['CoinPriceBitCloutNanos'] / 10 ** 9, 6);
        }
        $payload['last_value'] = $value;
        $operation->condition_payload = $payload;

        if ($this->isConditionValid($operation->condition, $payload, $value)) {
            $operation->status = Operation::OPERATION_STATUS_IN_PROCESS;
            $operation->save();
            Log::info('the status is set: ' . $operation->status . ': #' . $operation->id);
        }
    }

    public function isConditionValid(int $condition, array $payload, $value): bool
    {
        if ($condition == Operation::OPERATION_CONDITION_ABT_PRICE) {
            $min_value = $value - ($value / 100 * $payload['percent']);
            $max_value = $value + ($value / 100 * $payload['percent']);
            return ($min_value >= $payload['value'] && $payload['value'] <= $max_value);
        }

        if ($condition == Operation::OPERATION_CONDITION_EQ_PRICE) {
            return $value == $payload['value'];
        }
            
        if ($condition == Operation::OPERATION_CONDITION_GE_PRICE) {
            return $value >= $payload['value'];
        }
            
        if ($condition == Operation::OPERATION_CONDITION_LE_PRICE) {
            return $value <= $payload['value'];
        }
            
        if ($condition == Operation::OPERATION_CONDITION_GE_FRP) {
            return $value >= $payload['value'];
        }
            
        if ($condition == Operation::OPERATION_CONDITION_LE_FRP) {
            return $value <= $payload['value'];
        }

        return false;
    }
}
