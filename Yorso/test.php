<?php


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

