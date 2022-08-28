```
diff -u \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php.old \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php \
    > patches/RSA.php.patch

patch -p0 < patches/RSA.php.patch

```

Never share private keys.
Public key params are "n" and "e".
Encrypt with public key.
Decrypt with private key.

```
composer install
npm i
php -S localhost:8080
```

#### Main Functions
```
js_pri_key
js_pub_key
js_encrypt
js_decrypt

php_pri_key
php_pub_key
php_encrypt
php_decrypt
```

#### Functions for testing
```
js_pub_share
js_pri_share
js_pub_load
js_pri_load

php_pub_share
php_pri_share
php_pub_load
php_pri_load
```



composer patches:
 - https://packagist.org/packages/symplify/vendor-patches
 - https://github.com/cweagans/composer-patches
 - https://www.howtogeek.com/devops/how-to-apply-your-own-patches-to-composer-packages/
 - https://tomasvotruba.com/blog/2020/07/02/how-to-patch-package-in-vendor-yet-allow-its-updates/

An attack on RSA with exponent 3, by John D. Cook
https://www.johndcook.com/blog/2019/03/06/rsa-exponent-3/

    // (phpseclib)
    // FIX:  Check PRIME numbers 
    //       until they are valid COPRIMES of E,
    //       ensuring this condition
    //        -- while (!$gcd->equals(self::$one));
    //       exits the loop on its first evaluation:
    // REPO:  https://github.com/phpseclib/phpseclib
    // diff -u RSA.php.old RSA.php > RSA.php.patch
    do {
        // PRIME GENERATOR FUNCTION
        $primes[$i] = BigInteger::randomRangePrime($min, $max);
        list(, $rema) = $primes[$i]->divide($e);
    } while($rema->compare(self::$one) <= 0);



    "extra": {
      "patches": {
        "drupal/core": {
          "Add startup configuration for PHP server": "https://www.drupal.org/files/issues/add_a_startup-1543858-30.patch"
        }
      }
    }

docomposer require example/broken-package
php8 vendor\bin\vendor-patches generate


cat vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php.old

cp \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php.old

cp \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php.old \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php

diff -u \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php.old \
    vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php \
    > patches/RSA.php.patch

cat patches/RSA.php.patch

patch < patches/RSA.php.patch
