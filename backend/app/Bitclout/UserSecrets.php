<?php

namespace App\Bitclout;

use BIP\BIP44;
use Muvon\Bitclout\BIP39;
use Muvon\KISS\Base58Codec;

final class UserSecrets
{
    private $privateKeyHex;
    private $publicKeyBase58Check;
    private $publicKeyHex;

    public function __construct(
        private string $mnemonic,
        private string $password
    ) {
        $seed = BIP39::mnemonicToSeedHex($mnemonic, $password);
        $HDKey = BIP44::fromMasterSeed($seed)->derive("m/44'/0'/0'/0/0");
        $publicKey = Base58Codec::checkEncode('cd1400' . $HDKey->publicKey);

        $this->privateKeyHex = $HDKey->privateKey;
        $this->publicKeyBase58Check = $publicKey;
        $this->publicKeyHex = $HDKey->publicKey;
    }

    public function getPrivateKeyHex(): string
    {
        return $this->privateKeyHex;
    }

    public function getPublicKeyBase58Check(): string
    {
        return $this->publicKeyBase58Check;
    }

    public function getPublicKeyHex(): string
    {
        return $this->publicKeyHex;
    }
}
