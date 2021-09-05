<?php

namespace App\Services\Operation;

use App\Bitclout\Facades\Bitclout;
use App\Models\Operation;
use Exception;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class ExecutorService
{
    public function launch()
    {
        while (true) {
            Operation::with('account')
                ->inProccess()
                ->chunk(10, function ($operations) {
                    $operations->each(function ($operation) {
                        $result = $this->processOperation($operation);

                        $this->saveResult($operation, $result);

                        $this->sender($operation);
                    });
                });
        }
    }


    private function processOperation(Operation $operation)
    {
        $attempts = ++$operation->attempts;
        $beginTimeAction = time();

        try {
            if (!$operation->account) {
                throw new Exception('Unknown account ' . $operation->from_account_id);
            }

            if ($attempts >= Operation::OPERATION_ATTEMPTS) {
                throw new Exception('Тhe maximum number of attempts has been exceeded '
                    . Operation::OPERATION_ATTEMPTS . '. History: ' . $operation->result);
            }

            Log::info('Buy/sell start: #' . $operation->id);

            if ($operation->action === Operation::OPERATION_ACTION_BUY) {
                $payload = Bitclout::buyCoin(
                    $operation->account->mnemonic,
                    $operation->account->password,
                    $operation->target_public_key,
                    $operation->amount
                );
            } elseif ($operation->action === Operation::OPERATION_ACTION_SELL) {
                $payload = Bitclout::sellCoin(
                    $operation->account->mnemonic,
                    $operation->account->password,
                    $operation->target_public_key,
                    $operation->amount
                );
            } else {
                throw new Exception('Unknown action');
            }

            Log::info('Buy/sell finished: #' . $operation->id);

            $status = true;

            return compact('status', 'payload', 'attempts');
        } catch (Exception $e) {
            $status = false;
            $payload = ['error' => $e->getMessage()];
            return compact('status', 'payload', 'attempts');
        } finally {
            Log::info('operation=' . $operation->id . ' time=' . time() - $beginTimeAction);
        }
    }

    private function saveResult(Operation $operation, array $result)
    {
        $attemps = $result['attempts'] ?? $operation->attempts;

        if ($result['status']) {
            $operation->status = Operation::OPERATION_STATUS_SUCCESS;
        } elseif ($attemps < Operation::OPERATION_ATTEMPTS) {
            $operation->status = Operation::OPERATION_STATUS_IN_PROCESS;
        } else {
            $operation->status = Operation::OPERATION_STATUS_FAILED;
        }

        $operation->attempts = $attemps;
        $operation->result = $result['payload'];
        $operation->save();

        return $operation;
    }

    private function sender(Operation $operation)
    {
        try {
            Log::info('Start send to telegram: #' . $operation->id);

            $token = env('TASK_OPERATION_EXECUTOR_BOT_TOKEN');
            $chatId = env('TASK_OPERATION_EXECUTOR_CHAT_ID');

            $telegram = new Api($token);

            if ($operation->action == Operation::OPERATION_ACTION_BUY) {
                $action_type = 'Buy';
            } elseif ($operation->action == Operation::OPERATION_ACTION_SELL) {
                $action_type = 'Sell';
            } else {
                $action_type = null;
            }

            $payload = $operation->condition_payload;

            if ($operation->condition == Operation::OPERATION_CONDITION_ABT_PRICE) {
                $min_value = $payload['value'] - ($payload['value'] / 100 * $payload['percent']);
                $max_value = $payload['value'] + ($payload['value'] / 100 * $payload['percent']);
                $condition = 'Price ~ ' . $min_value . ' <=> ' . $max_value;
            } elseif ($operation->condition == Operation::OPERATION_CONDITION_EQ_PRICE) {
                $condition = 'Price = ' . $payload['value'];
            } elseif ($operation->condition == Operation::OPERATION_CONDITION_GE_PRICE) {
                $condition = 'Price >= ' . $payload['value'];
            } elseif ($operation->condition == Operation::OPERATION_CONDITION_LE_PRICE) {
                $condition = 'Price <= ' . $payload['value'];
            } elseif ($operation->condition == Operation::OPERATION_CONDITION_GE_FRP) {
                $condition = 'FRP >= ' . $payload['value'];
            } elseif ($operation->condition == Operation::OPERATION_CONDITION_LE_FRP) {
                $condition = 'FRP <= ' . $payload['value'];
            } else {
                $condition = '???';
            }

            $text = view('telegram.executed', [
                'id' => hash("adler32", $operation->id),
                'from_username' => $operation->account->username ?? '',
                'action' => $action_type,
                'target_username' => $operation->target_username,
                'amount' => $operation->amount,
                'condition' => $condition,
                'status' => $operation->status ? 'Success' : 'Failed',
            ])->render();

            $telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            Log::info('Sent to telegram: #' . $operation->id);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
