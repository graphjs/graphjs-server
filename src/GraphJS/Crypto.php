<?php
/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace GraphJS;


/**
 * A helper class for Encryption related static functions
 * 
 * Uses LibSodium
 * Inspired by:
 * https://stackoverflow.com/questions/34477643/how-to-encrypt-decrypt-aes-with-libsodium-php
 * 
 */
class Crypto
{

/**
* Encrypt a message
*

* 
* @param string $message - message to encrypt
* @param string $key - encryption key
* @return string
*/
public static function encrypt(string $message, string $key): string
{
    $key = \base64_decode($key);
    $nonce = \random_bytes(
        SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
    );

    $cipher = base64_encode(
        $nonce.
        \sodium_crypto_secretbox(
            $message,
            $nonce,
            $key
        )
    );
    \sodium_memzero($message);
    \sodium_memzero($key);
    return $cipher;
}

/**
* Decrypt a message
* 
* https://stackoverflow.com/questions/34477643/how-to-encrypt-decrypt-aes-with-libsodium-php
* @param string $encrypted - message encrypted with safeEncrypt()
* @param string $key - encryption key
* @return string
*/
public static function decrypt(string $encrypted, string $key): string
{   
    $key = \base64_decode($key);
    $decoded = \base64_decode($encrypted);
    if ($decoded === false) {
        throw new \Exception('Encoding failed');
    }
    if (\mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
        throw new \Exception('The message was truncated');
    }
    $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

    $plain = sodium_crypto_secretbox_open(
        $ciphertext,
        $nonce,
        $key
    );
    if ($plain === false) {
         throw new \Exception('The message was tampered with in transit');
    }
    \sodium_memzero($ciphertext);
    \sodium_memzero($key);
    return $plain;
    }

    public static function generateKey(): string
    {
        $key = \sodium_crypto_secretbox_keygen();
        return \base64_encode($key);
    }
}
