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
  * Members Controller Test
  */
class MembersTest extends TestCase
{

    /*
    return \GraphJS\Utils::convertLegacyRoutes([
    ['GET', '/getMembers','getMembers'],
    ['GET', '/getFollowers','getFollowers'],
    ['GET', '/getFollowing','getFollowing'],
    ['GET', '/unfollow','unfollow'],
    ['GET', '/follow','follow']
]);
*/

        public function testGetMembers()
        {
            $res = $this->get('/getMembers');
        
            $this->assertArrayHasKey("success", $res);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("members", $res);
            $this->assertGreaterThan(0, count($res["members"]));
        }

        public function testGetFollowersFalse()
        {
            //$this->expectException('GuzzleHttp\Exception\ServerException');
            $res = $this->get('/getFollowers');
            $this->assertFalse($res["success"]);
        }

        public function testGetFollowersTrue()
        {
            $res = $this->get('/getFollowers?id='.$this->founder_id);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("followers", $res);
        }

        public function testGetFollowing()
        {
            $res = $this->get('/getFollowing?id='.$this->founder_id);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("following", $res);
        }

        public function testFollow()
        {
            $hash = "hash=".$this->getAdminHash();
            $this->get("/setMembershipModerationMode?mode=0&".$hash);
            $this->get("/setVerificationRequiredMode?mode=0&".$hash);
            list($email, $username, $password, $me) = $this->signup();
            $res = $this->get('/follow?id='.$this->founder_id, false, true);
            $this->assertTrue($res["success"]);
            return [$me["id"], $username, $password];
        }

        /**
         * @depends testFollow
         */
         public function testGetFollowers2($user)
        {
            $id = $user[0];
            $res = $this->get('/getFollowers?id='.$this->founder_id);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("followers", $res);
            $this->assertContains($id, array_keys($res["followers"]));
            return $user;
        }

         /**
         * @depends testGetFollowers2
         */
        public function testUnfollow($user)
        {
            list($id, $username, $password) = $user;
            $this->login($username, $password);
            $res = $this->get('/unfollow?id='.$this->founder_id, false, true);
            $this->assertTrue($res["success"]);
            $res = $this->get('/getFollowers?id='.$this->founder_id);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("followers", $res);
            $this->assertNotContains($id, array_keys($res["followers"]));
        }

}
