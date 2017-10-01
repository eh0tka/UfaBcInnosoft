<?php

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
        //https://github.com/wavesplatform/Waves/wiki/Cryptographic-practical-details#signing
        return '44bKL8ubcR6hyuQuV6HAq7opWkNxuZxxJ4TtzjxGEzEEWPczCkdAwzpF4aBcjBLqUAGT5gHfr4kWcYt54erm9vhd';
    }
}

class base58
{
    static public $alphabet = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";
    public static function encode($int) {
        $base58_string = "";
        $base = strlen(self::$alphabet);
        while($int >= $base) {
            $div = floor($int / $base);
            $mod = ($int - ($base * $div)); // php's % is broke with >32bit int on 32bit proc
            $base58_string = self::$alphabet{$mod} . $base58_string;
            $int = $div;
        }
        if($int) $base58_string = self::$alphabet{$int} . $base58_string;
        return $base58_string;
    }

    public static function decode($base58) {
        $result = '';
        for($i=strlen($base58)-1,$j=1,$base=strlen(self::$alphabet);$i>=0;$i--,$j*=$base) {
            $result .= $j * strpos(self::$alphabet, $base58{$i});
        }
        return $result;
    }
}

function depositAddress($destinationAddress, $orderAmountWaves)
{
    //executor
    $obj = new WavesAPIService([
        'yorso_wallet' => '3NAqh5VMMWqhDJ2b9chNguRAc9bzEqecHbs',
        'yorso_public_key' => 'Go2r2WX9SQYxWazE8eBbb4RyMxMYsUREBpDv67BFuaRY',
        'yorso_private_key' => 'HiNnUQfX66wvxnNrLL2xxHWmCn9K4qJp776wZy6cNaK3',
        'testnet_address' => 'http://52.30.47.67:6869',
        'yorso_token_id' => 'H8BSjn3NsSrxhD4nB2LpdcqwAKDHSY7JaZvscLg7iquq',
    ]);

    $share = $obj->getTokenShare($destinationAddress);
    //if share is zero - do nothing
    if (!$share) {
        return;
    }

    //transfer waves to investor address
    $result = $obj->transferWaves($destinationAddress, $orderAmountWaves * $share);
}


$destinationAddress = '3N4JU6J7aSLKBZquma3gscv9FjD5o39x2Vq';
depositAddress($destinationAddress, 50);

