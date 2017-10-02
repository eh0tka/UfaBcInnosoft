<?php

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

