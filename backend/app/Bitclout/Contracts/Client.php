<?php

namespace App\Bitclout\Contracts;

interface Client
{
    public function profile(string $search): mixed;

    public function exchangeRate(): mixed;

    public function exchangeRateTicker(): mixed;

    public function users(array $publicKeys, bool $skipHolds = true): mixed;

    public function buyCoin(string $mnemonic, string $password, string $creator, float $amount): mixed;
    
    public function sellCoin(string $mnemonic, string $password, string $creator, float $amount): mixed;

    public function signTxn(string $txn, string $privateKey): string;

    public function transactionInfo(string $publicKey): mixed;

    public function block($hashHex): mixed;

    public function priceHistory(array $publicKeys): mixed;
}
