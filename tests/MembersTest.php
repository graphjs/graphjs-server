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
            $this->assertArrayHasKey("members", $res);
            $this->assertGreaterThan(0, count($res["members"]));
        }

}
