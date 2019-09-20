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
  * Notifications Controller Test
  */
class NotificationsWithPrefixTest extends TestCase
{

    /*
    ["GET", "/getNotificationsCount",'getNotificationsCount'],
    ["GET", "/getNotifications",'getNotifications']
*/

        public function testGetNotificationsCountFalse()
        {
            //eval(\Psy\sh());
            //$this->expectException("\\Exception");
            $res = $this->get('/v1/getNotificationsCount');
            $this->assertFalse($res["success"]);
        }

        public function testGetNotificationsCountTrue()
        {
            //eval(\Psy\sh());
            $this->login();
            $res = $this->get('/v1/getNotificationsCount', false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("count", $res);
        }

        public function testGetNotifications1()
        {
            $this->login();
            $res = $this->get('/v1/getNotifications', false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("data", $res);
        }

        public function testGetNotifications2()
        {
            $this->login();
            $res = $this->get('/v1/getNotifications', false, true);
            $this->assertTrue($res["success"]);
            $count = $res["count"];
            $res = $this->get('/v1/logout', false, true);
            $this->assertTrue($res["success"]);
            list($email, $username, $password, $res) = $this->signup();
            $res = $this->get('/v1/follow?id='.$this->founder_id, false, true);
            $this->assertTrue($res["success"]);
            $res = $this->get('/v1/logout', false, true);
            $this->assertTrue($res["success"]);
            $this->login();
            $res = $this->get('/v1/getNotifications', false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("data", $res);
            $this->assertEquals(($count+1), $res["count"]);
            $this->assertCount(($count+1), $res["data"]);
        }


}
