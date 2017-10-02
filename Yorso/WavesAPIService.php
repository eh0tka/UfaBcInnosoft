<?php

require __DIR__ . '/vendor/autoload.php'; //load library by composer "stephenhill/base58": "~1.0"

class WavesAPIService
{
    use HttpRequester;

    private $publicKey;
    private $privateKey;
    private $walletAddress;
    private $yorsoAssetId;
    private $url;

    public function __construct(array $config)
    {
        $this->publicKey = $config['yorso_public_key'];
        $this->privateKey = $config['yorso_private_key'];
        $this->walletAddress = $config['yorso_wallet'];
        $this->url = $config['testnet_address'];
        $this->yorsoAssetId = $config['yorso_token_id'];
    }

    public function getTokenShare($destinationAddress)
    {
        $url = $this->url . '/assets/balance/' . $destinationAddress;
        $builder = new CurlBuilderDto();
        $builder->url = $url;
        $result = $this->sendGetRequest($builder);

        if (!$result->isOk()) {
            throw new Exception('Something went wrong: ' . json_encode($result));
        }

        $body = json_decode($result->body);
        foreach ($body->balances as $balance) {
            if ($balance->assetId == $this->yorsoAssetId) {
                $balanceValue = $balance->balance;
                $quantity = $balance->quantity;
                return $balanceValue / $quantity;
            }
        }

        return 0;
    }

    public function transferWaves($destinationAddress, $valueWaves)
    {
        $url = $this->url . '/assets/broadcast/transfer';
        $builder = new CurlBuilderDto();
        $builder->url = $url;
        $data = [
            'assetId' => '',
            'senderPublicKey' => $this->publicKey,
            'recipient' => $destinationAddress,
            'fee' => 100000,
            'feeAssetId' => '',
            'amount' => $valueWaves * 100000000,
            'attachment' => '',
            'timestamp' => time(),
        ];

        $data['signature'] = self::calculateTransferSignature($data);

        $builder->parameters = json_encode($data);
        $builder->headers = [
            'content-type: application/json;charset=UTF-8'
        ];

        return $this->sendPostRequest($builder);
    }

    private static function calculateTransferSignature($inputData)
    {
        //do some heavy math
        //Transaction type (0x04)
        $result = [
            4
        ];

        //Sender's public key - 32 bytes
        $result = array_merge($result, base58ToByteArray($inputData['senderPublicKey']));

        //Amount's asset flag (0-Waves, 1-Asset) - 1 byte
        //TODO:

        //Amount's asset ID (*if used) = 0 or 32
        //TODO:

        //Amount's asset ID (*if used) = 0 or 32
        //TODO:

        //Fee's asset flag (0-Waves, 1-Asset) - 1 byte
        //TODO:

        //Fee's asset ID (**if used) - 0 or 32
        //TODO:

        //Timestamp - 8 bytes
        //TODO:

        //Amount - 8 bytes
        //TODO:

        //Fee - 8 bytes
        //TODO:

        //Recipient's address - 26 bytes
        //TODO:

        //Attachment's length (N) - 2 bytes
        //TODO:

        //Attachment's bytes - N bytes
        //TODO:

        //get data do sign:
        //TODO:

        //sign with private key
        //TODO:

        //https://github.com/wavesplatform/Waves/wiki/Cryptographic-practical-details#signing
        //https://github.com/wavesplatform/Waves/wiki/Data-Structures
        return '44bKL8ubcR6hyuQuV6HAq7opWkNxuZxxJ4TtzjxGEzEEWPczCkdAwzpF4aBcjBLqUAGT5gHfr4kWcYt54erm9vhd';
    }
}

function base58ToByteArray($base58String)
{
    $base58 = new StephenHill\Base58();
    $converted = $base58->decode($base58String);

    $result = [];
    $len = strlen($converted);
    for ($i = 0; $i < $len; $i++) {
        $result[] = ord($converted[$i]);
    }

    return $result;
}

