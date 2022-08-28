<?php
require __DIR__ . '/vendor/autoload.php';

use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use phpseclib3\Math\BigInteger;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

session_start();

if(isset($_GET['clear'])) {
    $_SESSION['msg'] = null;
    $_SESSION['php_pri'] = null;
}

function init_msg($force=false) {
    if(empty($_SESSION['msg']) or $force) {
        $chars = 'áéíóúüñÁÉÍÓÚÜÑ¿¡';
        $l_chars = mb_strlen($chars)-1;
        $l_msg = mt_rand(2, $l_chars);
        $msg = '';
        for($i=0;$i<$l_msg;$i++)
            $msg .= mb_substr($chars, mt_rand(0, $l_chars), 1);

        $_SESSION['msg'] = $msg;
    }
}



RSA::setExponent(3);


// ENCRYPTION //

function do_encrypt($text, $pub_key_str){
    $aes_key = Random::string(16);
    $aes_iv = Random::string(16);

    $cipher = new AES('cbc');
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


function do_decrypt($cipher, $pri_key){
    $js_cipher_parts = explode('?', $cipher);

    foreach($js_cipher_parts as &$v)
        $v = base64_decode($v);

    $aes_decrypted = utf8_decode($pri_key->decrypt($js_cipher_parts[0]));

    $decryption_key = new AES('cbc');
    $decryption_key->disablePadding();
    $decryption_key->setKey($aes_decrypted);
    $decryption_key->setIV(substr($js_cipher_parts[1],0,16));

    return utf8_encode($decryption_key->decrypt(substr($js_cipher_parts[1],16)));
}


if(isset($_POST['init_php_pri'])){
    init_msg();

    $private = !empty($_SESSION['php_pri'])
            ? RSA::load($_SESSION['php_pri'])
            : RSA::createKey(512,"The Moon is a Harsh Mistress.");

    $_SESSION['php_pri'] = $private->withPadding(RSA::ENCRYPTION_PKCS1);

    $private_xml = simplexml_load_string($private->toString('XML'));
    echo $private_xml->Modulus;
    exit;
}

if(isset($_POST['js_pub'])){
    echo do_encrypt($_SESSION['msg'], $_POST['js_pub']);
    exit;
}

if(isset($_POST['js_cipher'])){
    $private = RSA::load($_SESSION['php_pri'])
            ->withPadding(RSA::ENCRYPTION_PKCS1);
    echo do_decrypt($_POST['js_cipher'], $private);
    exit;
}

init_msg(true);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>


<pre>
<b>NOTE:</b> phpseclib might fail 6% of the time, or less. Catch those exceptions. 

PHP PUB: <span id="php_pub"></span> 
PHP CIPHER: ***<span id="php_cipher"></span>*** 
PHP DECRYPT: ***<span id="php_decrypt"></span>*** 

JS CIPHER: ***<span id="js_cipher"></span>*** 
JS DECRYPT: ***<span id="js_decrypt"></span>*** 
</pre>

<script src="node_modules/cryptico-js/dist/cryptico.browser.min.js"></script>


<script>
var private = cryptico.generateRSAKey("The Moon is a Harsh Mistress.", 512);
var res_encrypt = null;

function init_php_pri(){
    console.log('loading pri')
    const frmInitPHPPri = new FormData();
    frmInitPHPPri.append('init_php_pri', 1);

    fetch('<?php echo $_SERVER['PHP_SELF'] ?>', {
      method: 'POST',
      body: frmInitPHPPri
    })
    .then((response) => response.text())
    .then((data) => {
      document.querySelector('#php_pub').innerHTML = data

      fill_js_cipher();
      fill_php_cipher_and_js_decrypt();
      fill_php_decrypt();
    });
}

init_php_pri();


function fill_js_cipher(){
    res_encrypt = cryptico.encrypt(
       '<?php echo $_SESSION['msg'] ?>', 
       document.querySelector('#php_pub').innerHTML);

    document.querySelector('#js_cipher').innerHTML = res_encrypt.cipher   
}

function fill_php_cipher_and_js_decrypt(){
    const frmSendPubKey = new FormData();
    frmSendPubKey.append('js_pub', cryptico.publicKeyString(private));

    fetch('<?php echo $_SERVER['PHP_SELF'] ?>', {
      method: 'POST',
      body: frmSendPubKey
    })
    .then((response) => response.text())
    .then((data) => {
      document.querySelector('#php_cipher').innerHTML = data

      let res_descrypt = cryptico.decrypt(data, private); //$php_cipher
      document.querySelector('#js_decrypt').innerHTML
        = res_descrypt.plaintext.replace(/[\s\x00-\x1f]+$/,'')
    });
}

function fill_php_decrypt(cipher){
    const frmSendCipher = new FormData();
    frmSendCipher.append('js_cipher', res_encrypt.cipher);

    fetch('<?php echo $_SERVER['PHP_SELF'] ?>', {
      method: 'POST',
      body: frmSendCipher
    })
    .then((response) => response.text())
    .then((data) => {
      document.querySelector('#php_decrypt').innerHTML = data
    });    
}



</script>

</body>
</html>
