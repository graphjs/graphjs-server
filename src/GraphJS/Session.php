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

use HansOtt\PSR7Cookies\SetCookie;
use HansOtt\PSR7Cookies\RequestCookies;
use HansOtt\PSR7Cookies\Signer\Key;
use HansOtt\PSR7Cookies\Signer\Hmac\Sha256;
use HansOtt\PSR7Cookies\Signer\Mismatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Session
{
    const COOKIE = "_______g_j_c";

    public static function depend(ServerRequestInterface $request): ?string 
    {
        echo "depend cookie 1\n";
        $signer = new Sha256();
        echo "depend cookie 2\n";
        $key = new Key(md5(getenv("SINGLE_SIGNON_TOKEN_KEY")));
        echo "depend cookie 3\n";
        $cookies = RequestCookies::createFromRequest($request);
        echo "depend cookie 4\n";
        if ($cookies->has(static::COOKIE)) {
            try {
                echo "depend cookie 5\n";
                $idSigned = $cookies->get(static::COOKIE);
                var_dump($idSigned);
                //echo "depend cookie 5.5: ". $idSigned."\n";
                $id = $signer->verify(
                    $idSigned, 
                    $key
                );
                var_dump($id);
                echo "depend cookie 6: ". $id->getValue()."\n";
                return $id->getValue();
            } catch (Mismatch $e) {
                error_log("Cookie tampered");
            }
        }
        echo "depend cookie olmadi\n";
        return null;
    }

    public static function begin(ResponseInterface &$response, string $id): void
    {
        echo "beign cookie 1\n";
        $signer = new Sha256();
        echo "beign cookie 2\n";
        $key = new Key(md5(getenv("SINGLE_SIGNON_TOKEN_KEY")));
        echo "beign cookie 3\n";
        $cookie = SetCookie::thatStaysForever(static::COOKIE, $id, "/");
        echo "beign cookie 4\n";
        $signedCookie = $signer->sign($cookie, $key);
        echo "beign cookie 5\n";
        $response = $signedCookie->addToResponse($response);
        echo "beign cookie 6\n";
    }

    public static function destroy(ResponseInterface &$response): void
    {
        echo "bitir cookie 1\n";
        $cookie = SetCookie::thatDeletesCookie(static::COOKIE, "/");
        echo "bitir cookie 2\n";
        $response = $cookie->addToResponse($response);
        echo "bitir cookie 3\n";
    }
}