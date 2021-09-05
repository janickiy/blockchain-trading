<?php

namespace App\Bitclout;

use App\Bitclout\Contracts\Client as ClientContract;
use App\Bitclout\Contracts\Transport as TransportContract;
use Exception;
use Muvon\Bitclout\Signer;

class Client implements ClientContract
{
    protected $transport;

    public function __construct(TransportContract $transport)
    {
        $this->transport = $transport;
    }

    public function profile(string $search): mixed
    {
        $payload = $this->transport->fetchProfile($search);

        if (isset($payload['error'])) {
            throw new Exception('Тhe error key is filled in the response: ' . $payload->error);
        }

        if (!isset($payload['Profile'])) {
            throw new Exception('Тhe key with profile was not found in the response');
        }

        return $payload['Profile'];
    }

    public function exchangeRate(): mixed
    {
        $payload = $this->transport->fetchExchangeRate();

        if (
            !isset($payload['NanosSold']) ||
            !isset($payload['SatoshisPerBitCloutExchangeRate']) ||
            !isset($payload['USDCentsPerBitcoinExchangeRate'])
        ) {
            throw new Exception('The response is not valid');
        }

        return $payload;
    }

    public function exchangeRateTicker(): mixed
    {
        $payload = $this->transport->fetchExchangeRateTicker();

        if (!isset($payload['USD'])) {
            throw new Exception('The response is not valid');
        }

        return $payload;
    }

    public function users(array $publicKeys, bool $skipHolds = true): mixed
    {
        $payload = $this->transport->fetchUsers($publicKeys, $skipHolds);

        if (!isset($payload['UserList'])) {
            throw new Exception('Тhe key with users was not found in the response');
        }

        $userList = collect($payload['UserList']);

        foreach ($publicKeys as $publicKey) {
            if (!$userList->firstWhere('PublicKeyBase58Check', $publicKey)) {
                throw new Exception('In the response, the key with users is not a list');
            }
        }

        return $userList;
    }

    public function buyCoin(string $mnemonic, string $password, string $creator, float $amount): mixed
    {
        $userSecrets = new UserSecrets($mnemonic, $password);

        $nanos = (int) ($amount * pow(10, 9));

        $txnPreview = $this->transport->buyCoin(
            $userSecrets->getPublicKeyBase58Check(),
            $creator,
            $nanos,
            0
        );

        $txn = $this->transport->buyCoin(
            $userSecrets->getPublicKeyBase58Check(),
            $creator,
            $nanos,
            $txnPreview['ExpectedCreatorCoinReturnedNanos']
        );

        $signature = $this->signTxn($txn['TransactionHex'], $userSecrets->getPrivateKeyHex());

        return $this->transport->submitTxn($signature);
    }

    public function sellCoin(string $mnemonic, string $password, string $creator, float $amount): mixed
    {
        $userSecrets = new UserSecrets($mnemonic, $password);

        $nanos = (int) ($amount * pow(10, 9));

        $txnPreview = $this->transport->sellCoin(
            $userSecrets->getPublicKeyBase58Check(),
            $creator,
            $nanos,
            0
        );

        $txn = $this->transport->sellCoin(
            $userSecrets->getPublicKeyBase58Check(),
            $creator,
            $nanos,
            $txnPreview['ExpectedBitCloutReturnedNanos']
        );

        $signature = $this->signTxn($txn['TransactionHex'], $userSecrets->getPrivateKeyHex());

        return $this->transport->submitTxn($signature);
    }

    public function signTxn(string $txn, string $privateKey): string
    {
        $hash = hash('sha256', hash('sha256', hex2bin($txn), true));
        $signature = Signer::secp256k1($hash, $privateKey);
        $signature = substr($txn, 0, -2) . bin2hex(pack("c", strlen($signature) / 2)) . $signature;

        if (!preg_match("/^[a-fA-F0-9]+$/", $signature)) {
            throw new Exception('No valid signature');
        }

        return $signature;
    }

    public function transactionInfo(string $publicKey): mixed
    {
        $result = $this->transport->transactionInfo($publicKey);

        if ($result['Error']) {
            throw new Exception($result['Error']);
        }

        return $result['Transactions'];
    }

    public function block($hashHex): mixed
    {
        $result = $this->transport->block($hashHex);

        if ($result['Error']) {
            throw new Exception($result['Error']);
        }

        return $result['Header'];
    }

    public function priceHistory(array $publicKeys): mixed
    {
        return $this->transport->priceHistory($publicKeys);
    }
}
