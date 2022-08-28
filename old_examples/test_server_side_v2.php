<?php
require __DIR__ . '/vendor/autoload.php';

use phpseclib\Crypt\RSA;
echo '<pre>';

$rsa = new RSA();
 
$rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
$rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_PKCS1);

define('CRYPT_RSA_EXPONENT', 3);
// define('CRYPT_RSA_SMALLEST_PRIME', 64); // makes it so multi-prime RSA is used

// $private = $rsa->createKey();
$private = $rsa->createKey(1024);
// extract($rsa->createKey()); // == $rsa->createKey(1024) where 1024 is the key size

var_dump($private);
