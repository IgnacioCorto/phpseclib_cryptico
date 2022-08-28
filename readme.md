### Interaction between PHPSecLib 3.0 and Cryptico.JS
Run:
```
composer install
npm i  
patch -p0 < patches/RSA.php.patch
``` 
and then, open `test_public_key_both.php` in your web browser.

**NOTE:** phpseclib might fail 6% of the time, or less. Catch those exceptions. 