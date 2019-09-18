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
  * Profile Controller Test
  */
class ProfileWithPrefixTest extends TestCase
{

    /*
    ['GET', '/getProfile',"getProfile"],
    ['GET', '/setProfile',"setProfile"],
*/

        public function testGetProfile()
        {
            //eval(\Psy\sh());
            $res = $this->get('/v1/getProfile?id='.$this->founder_id);
            $this->assertArrayHasKey("success", $res);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("profile", $res);
            $this->assertArrayHasKey("username", $res["profile"]);
        }

        public function testSetProfile()
        {
            list($email, $username, $password, $res) = $this->signup();
            $res = $this->get('/v1/getProfile?id='.$res["id"]);
            $birthday = $res["profile"]["birthday"];
            $res = $this->get('/v1/setProfile?about='.($this->faker->text), false, true);
            $this->assertTrue($res["success"]);
            $this->assertEquals("Following fields set successfully: about", $res["message"]);
        }

}
