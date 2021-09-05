<?php

namespace App\Bitclout\Contracts;

interface Transport
{
    public function fetchProfile(string $search);

    public function fetchExchangeRate(): mixed;

    public function fetchExchangeRateTicker(): mixed;
    
    public function fetchUsers(array $publicKeys, bool $skipHolds): mixed;

    public function buyCoin(string $updater, string $creator, int $nanos, int $expectedNanos): mixed;

    public function sellCoin(string $updater, string $creator, int $nanos, int $expectedNanos): mixed;

    public function submitTxn(string $signature): mixed;

    public function transactionInfo(string $publicKey): mixed;

    public function block($hashHex): mixed;

    public function priceHistory(array $publicKeys): mixed;
}
