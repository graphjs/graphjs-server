<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 /**
  * Authentication Controller Test
  */
class AuthenticationTest extends TestCase
{

    /*
    ['GET', '/tokenSignup',"tokenSignup"],
    ['GET', '/tokenLogin',"tokenLogin"],
    ['GET', '/signup',"signup"],
    ['GET', '/login',"login"],
    ['GET', '/logout',"logout"],
    ['GET', '/whoami',"whoami"],
    ['GET', '/resetPassword',"resetPassword"],
    ['GET', '/verifyReset',"verifyReset"],
    ['GET', '/verifyEmailCode',"verifyEmailCode"],
*/

        public function testWhoAmIFalse()
        {
            //$this->expectException('GuzzleHttp\Exception\ServerException');
            $res = $this->get('/whoami');
            $this->assertFalse($res["success"]);
        }

        public function testSignup()
        {
            list($email, $username, $password, $res) = $this->signup();
            $this->assertTrue($res["success"]);
            $id = $res["id"];
            $whoami = $this->get("/whoami", false, true);
            $this->assertEquals($whoami["id"], $id);
            $whoami = $this->get("/logout", false, true);
            try {
                $this->get('/whoami');
            } catch (\Exception $e) {
                $this->assertTrue(true); // exception received
            }
            return [$username, $password, $id];
        }

        /**
         * @depends testSignup
         */
        public function testLogin(array $credentials)
        {
            $username = $credentials[0];
            $password = $credentials[1];
            $id = $credentials[2];
            $url = sprintf('/login?username=%s&password=%s', urlencode($username), urlencode($password));
            $res = $this->get($url, false, true);
            //eval(\Psy\sh());
            $this->assertEquals($res["id"], $id);
        }

}
