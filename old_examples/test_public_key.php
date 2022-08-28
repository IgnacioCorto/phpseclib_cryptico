<?php
require __DIR__ . '/vendor/autoload.php';

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

echo '<pre>';

RSA::setExponent(3);

$params = [
    "n" => new BigInteger("b8c74358ee59c003e8eaea645593c4563054a416a91beecd03b232f23692c36a0a056fb142a00d63e46c034c866194bcbd8da59e9b785e6835fd616e0d991577", 16),
    "e" => new BigInteger("03", 16),
    "p" => new BigInteger("b0c7c12a2a561f613c59fc5c8283285f45c31db676bd9ede7284d03ea8570bb3", 16),
    "q" => new BigInteger("010b950c7b537204d4efc9e354fe04b7c40f9c2969f924b2d554f853b196c45d2d", 16),
    "dmp1" => new BigInteger("75da80c6c6e414eb7d9152e857021aea2e8213cef9d3bf3ef703357f1ae4b277", 16),
    "dmq1" => new BigInteger("b2635da78cf6ade34a86978dfeadcfd80a681b9bfb6dcc8e38a58d210f2d9373", 16),
    "coeff" => new BigInteger("8dd7cebdda2a6e90fc188342229c45f1a8eb67ebc8b893bd1bce3e3d0b93e0cb", 16),
    "d" => new BigInteger("1ecbe08ed2644aab517c7c660e434b63b2b8c603c6d9fd222b485dd3091875e6b79c1b01f623fc3249615b4480f99e19915d0fbfb1ee577367c00a3fa26a476f", 16),
];

$private = RSA::load($params)
        ->withPadding(RSA::ENCRYPTION_PKCS1);


$private_xml = simplexml_load_string($private->toString('XML'));

echo 'PUBLIC KEY STRING: ', $private_xml->Modulus, "\n";


// ENCRYPTION //

function do_encrypt($text, $pub_key_str){
    $aes_key = Random::string(16);
    $aes_iv = Random::string(16);

    $cipher = new AES('cbc');
    $cipher->enablePadding();
    // $cipher->disablePadding();
    // $cipher->enablePoly1305();
    $cipher->setKey($aes_key);
    $cipher->setIV($aes_iv);

    $ciphertext = $cipher->encrypt(utf8_decode($text));

    $public = PublicKeyLoader::load([
                "n" => new BigInteger(bin2hex(base64_decode($pub_key_str)), 16),
                "e" => new BigInteger("03", 16),
            ])
            ->withPadding(RSA::ENCRYPTION_PKCS1);

    $rsa_encrypted = $public->encrypt(utf8_encode($aes_key));

    return base64_encode($rsa_encrypted)
        .'?'. base64_encode($aes_iv.$ciphertext);
}


$cryptico_cipher = do_encrypt('Matt, I need you to help me with my Starcraft strategy.', $private_xml->Modulus);
echo 'CIPHER: ', $cryptico_cipher, "\n"; 



// DECRYPTION

/*
explode ?
0 & 1 on base64-dec
0: decrypt > utf8_decode  (aes_decrypteed, use as key)

*/

function do_decrypt($cipher, $pri_key){
    $js_cipher_parts = explode('?', $cipher);

    foreach($js_cipher_parts as &$v)
        $v = base64_decode($v);

    $aes_decrypted = utf8_decode($pri_key->decrypt($js_cipher_parts[0]));

    $decryption_key = new AES('cbc');
    // $decryption_key->enablePadding();
    // $decryption_key->disablePadding();
    // $decryption_key->enablePoly1305();
    $decryption_key->setKey($aes_decrypted);
    $decryption_key->setIV(substr($js_cipher_parts[1],0,16));

    return utf8_encode($decryption_key->decrypt(substr($js_cipher_parts[1],16)));
}

$decrypted = do_decrypt($cryptico_cipher, $private);
echo 'DECRYPTED: ', $decrypted, "\n";



// MESSAGE ENCRYPT/DECRYPT

$cryptico_cipher = do_encrypt('PHP says: "Hello JS! (1ñü)"', 'amm3Tx0RqSd4St+34UjlX2ILusFGc4xKh+3wrXSfmEkejnylA59QYW+aTL+rwrs5pX+DVLw9yqGzLPTfzxb+Yw==');
echo "\n", 'CIPHER FOR JS: ', $cryptico_cipher, "\n"; 

$decrypted = do_decrypt(
    'hnCxGtGeon5PqBgu82vl861LqZ0zGGmCDBXocEKVl2GEsKSWVb0hMeNZ8FSFxSvYUoox2k2lklZi/hHiQ/rn/w==?8wpO9qB03f4xNG6V5KHQ87ofe5g2x03Z3m9ffoDJb9IlB/ZLWYq19IetzTA/GE8m'
    , $private);
echo 'DECRYPTED FROM JS: ', $decrypted, "**\n";



