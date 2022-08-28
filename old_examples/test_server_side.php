<?php
require __DIR__ . '/vendor/autoload.php';

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

echo '<pre>';

RSA::setExponent(3);

$private = RSA::createKey(512,"The Moon is a Harsh Mistress.")
        ->withPadding(RSA::ENCRYPTION_PKCS1);

$public = $private->getPublicKey()
        ->withPadding(RSA::ENCRYPTION_PKCS1);

$public_xml = simplexml_load_string($public->toString('XML'));
$private_xml = simplexml_load_string($private->toString('XML'));


// ENCRYPTION //

$aes_key = Random::string(16);
$aes_iv = Random::string(16);

$cipher = new AES('cbc');
$cipher->enablePadding();
// $cipher->disablePadding();
$cipher->setKey($aes_key);
$cipher->setIV($aes_iv);

$ciphertext = $cipher->encrypt('Matt, I need you to help me with my Starcraft strategy.');

$rsa_encrypted = $public->encrypt(utf8_encode($aes_key));

$cryptico_cipher = base64_encode($rsa_encrypted)
    .'?'. base64_encode($aes_iv.$ciphertext);

echo "<pre>CYPHER: ***{$cryptico_cipher}***</pre>";


// DECRYPTION

$js_cipher_parts = explode('?', $cryptico_cipher);

foreach($js_cipher_parts as &$v)
    $v = base64_decode($v);

$aes_decrypted = utf8_decode($private->decrypt($js_cipher_parts[0]));

$decryption_key = new AES('cbc');
$decryption_key->enablePadding();
// $decryption_key->disablePadding();
$decryption_key->setKey($aes_decrypted);
$decryption_key->setIV(substr($js_cipher_parts[1],0,16));

$decrypted = $decryption_key->decrypt(substr($js_cipher_parts[1],16));

echo "<pre>***{$decrypted}***</pre>";


// SHARE KEYS 

function fnGetJSON($xml){
    $out = [];
    $params = [
        "Modulus" => "n",       //modulus
        "Exponent" => "e",      //public exponent
        "P" => "p",             //prime1
        "Q" => "q",             //prime2
        "DP" => "dmp1",         //exponent1
        "DQ" => "dmq1",         //exponent2
        "InverseQ" => "coeff",  //coefficient
        "D" => "d",             //private exponent
    ];
    foreach($params as $f1=>$f2)
        if(isset($xml->$f1))
            $out[$f2] = bin2hex(base64_decode($xml->$f1));

    return json_encode($out);
}

$public_xml = simplexml_load_string($public->toString('XML'));
$private_xml = simplexml_load_string($private->toString('XML'));

$public_json = fnGetJSON($public_xml);
$private_json = fnGetJSON($private_xml);

echo "<pre>
MODULUS {$public_xml->Modulus}
PUBLIC: {$public_json}
PRIVATE: {$private_json}
</pre>";


// LOAD KEYS 

function fnGetParams($json){
	$rsa_params = json_decode($json, true);
    foreach($rsa_params as &$v)
    	$v = new BigInteger($v, 16);
    return $rsa_params;
}

$public_params = fnGetParams($public_json);
$private_params = fnGetParams($private_json);

$public = RSA::load($public_params)
        ->withPadding(RSA::ENCRYPTION_PKCS1);
$private = RSA::load($private_params)
        ->withPadding(RSA::ENCRYPTION_PKCS1);
