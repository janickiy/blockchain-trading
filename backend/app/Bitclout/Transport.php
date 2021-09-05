<?php

namespace App\Bitclout;

use App\Bitclout\Contracts\Transport as TransportContract;
use Illuminate\Support\Facades\Http;

class Transport implements TransportContract
{
    protected $options = ['verify' => false];

    public function __construct(?string $proxy)
    {
        if ($proxy) {
            $this->options['proxy'] = $proxy;
        }
    }

    public function fetchProfile(string $search)
    {
        if (preg_match('/^[BC1YL][\w]{54}$/', $search)) {
            $data['PublicKeyBase58Check'] = $search;
        } else {
            $data['Username'] = $search;
        }

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api.bitclout.com/api/v0/get-single-profile', $data)
            ->throw()
            ->json();
    }

    public function fetchExchangeRate(): mixed
    {
        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->get('https://api.bitclout.com/get-exchange-rate')
            ->throw()
            ->json();
    }

    public function fetchExchangeRateTicker(): mixed
    {
        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://blockchain.info/ticker')
            ->throw()
            ->json();
    }

    public function fetchUsers(array $publicKeys, bool $skipHolds): mixed
    {
        $data = [
            'PublicKeysBase58Check' => $publicKeys,
            'SkipHodlings' => $skipHolds,
        ];

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://bitclout.com/api/v0/get-users-stateless', $data)
            ->throw()
            ->json();
    }

    public function buyCoin(string $updater, string $creator, int $nanos, int $expectedNanos): mixed
    {
        $data = [
            'UpdaterPublicKeyBase58Check' => $updater,
            'CreatorPublicKeyBase58Check' => $creator,
            'BitCloutToSellNanos' => $nanos,
            'CreatorCoinToSellNanos' => 0,
            'BitCloutToAddNanos' => 0,
            'MinBitCloutExpectedNanos' => 0,
            'MinCreatorCoinExpectedNanos' => $expectedNanos,
            'MinFeeRateNanosPerKB' => 1000,
            'OperationType' => 'buy',
        ];

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://bitclout.com/api/v0/buy-or-sell-creator-coin', $data)
            ->throw()
            ->json();
    }

    public function sellCoin(string $updater, string $creator, int $nanos, int $expectedNanos): mixed
    {
        $data = [
            'UpdaterPublicKeyBase58Check' => $updater,
            'CreatorPublicKeyBase58Check' => $creator,
            'BitCloutToSellNanos' => 0,
            'CreatorCoinToSellNanos' => $nanos,
            'BitCloutToAddNanos' => 0,
            'MinBitCloutExpectedNanos' => $expectedNanos,
            'MinCreatorCoinExpectedNanos' => 0,
            'MinFeeRateNanosPerKB' => 1000,
            'OperationType' => 'sell',
        ];

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://bitclout.com/api/v0/buy-or-sell-creator-coin', $data)
            ->throw()
            ->json();
    }

    public function submitTxn(string $signature): mixed
    {
        $data = [
            'TransactionHex' => $signature,
        ];

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://bitclout.com/api/v0/submit-transaction', $data)
            ->throw()
            ->json();
    }

    public function transactionInfo(string $publicKey): mixed
    {
        $data = [
            'PublicKeyBase58Check' => $publicKey,
        ];

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api.bitclout.com/api/v1/transaction-info', $data)
            ->throw()
            ->json();
    }

    public function block($hashHex): mixed
    {
        $data = [
            'HashHex' => $hashHex,
        ];

        return Http::withOptions($this->options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api.bitclout.com/api/v1/block', $data)
            ->throw()
            ->json();
    }

    public function priceHistory(array $publicKeys): mixed
    {
        $data = [
            'PublicKeyBase58Check' => $publicKeys,
        ];

        $options = $this->options;

        if (isset($options['proxy'])) {
            unset($options['proxy']);
        }

        return Http::withOptions($options)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('http://165.22.20.113:8000/v1/histories/coins', $data)
            ->throw()
            ->json();
    }
}
