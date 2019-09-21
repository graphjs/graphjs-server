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
  * Group Controller Test
  */
class GroupTest extends TestCase
{

    /*
    ["GET", "/createGroup","createGroup"],
    ["GET", "/deleteGroup","deleteGroup"],
    ["GET", "/setGroup","setGroup"],
    ["GET", "/join","join"],
    ["GET", "/leave","leave"],
    ["GET", "/listGroups","listGroups"],
    ["GET", "/getGroup","getGroup"],
    ["GET", "/listMemberships","listMemberships"],
    ["GET", "/listMembers","listMembers"],
*/

        public function testListGroups()
        {
            $res = $this->get('/listGroups');
            $this->assertArrayHasKey("success", $res);
            $this->assertArrayHasKey("groups", $res);
        }

        public function testCreateGroupFalse()
        {
            $res = $this->get('/createGroup?' . http_build_query(["title"=>"Lorem Ipsum", "description"=>$this->faker->text]));
            $this->assertFalse($res["success"]);
        }

        public function testCreateGroupTrue()
        {
            $this->login();
            $res = $this->get('/createGroup?' . http_build_query(["title"=>"Lorem Ipsum", "description"=>$this->faker->text]), false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("id", $res);
            $id = $res["id"];
            return $id;
        }

        /**
         * @depends testCreateGroupTrue
         */
        public function testListGroupsAgain($id)
        {
            $res = $this->get('/listGroups');
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("groups", $res);
            $groups = $res["groups"];
            $group_ids = array_map(function($group) { return $group["id"]; }, $groups);
            $this->assertContains($id, $group_ids);
        }

        /**
         * @depends testCreateGroupTrue
         */
        public function testGetGroup($id)
        {
            $res = $this->get('/getGroup?id='.$id);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("cover", $res["group"]);
            $this->assertArrayHasKey("count", $res["group"]);
            $this->assertArrayHasKey("members", $res["group"]);
            $this->assertArrayHasKey("creator", $res["group"]);
            $this->assertArrayHasKey("description", $res["group"]);
            $this->assertArrayHasKey("title", $res["group"]);
            $this->assertArrayHasKey("id", $res["group"]);
        }

        /**
         * @depends testCreateGroupTrue
         */
        public function testSetGroup($id)
        {
            $new_text = $this->faker->text;
            $this->login();
            $res = $this->get('/setGroup?id='.$id.'&description='.$new_text, false, true);
            $this->assertTrue($res["success"]);
            //eval(\Psy\sh());
            $res = $this->get('/getGroup?id='.$id);
            $this->assertTrue($res["success"]);
            $this->assertEquals($new_text, $res["group"]["description"]);
        }

        /**
         * @depends testCreateGroupTrue
         */
        public function testJoinFalse($id)
        {
            $res = $this->get('/join?id='.$id);
            $this->assertFalse($res["success"]);
        }

        /**
         * @depends testCreateGroupTrue
         */
        public function testJoin($id)
        {
            list($email, $username, $password, $me) = $this->signup();
            $res = $this->get('/join?id='.$id, false, true);
            $this->assertTrue($res["success"]);
            $res = $this->get('/listMembers?id='.$id, false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("members", $res);
            $this->assertContains($me["id"], $res["members"]);
            $count = count($res["members"]);
            return [$count, $id, $me["id"], $username, $password];
        }

        /**
         * @depends testJoin
         */
        public function testListMemberships($params)
        {
            list($count, $id, $user_id, $username, $password) = $params;
            //eval(\Psy\sh());
            $res = $this->get('/listMemberships?id='.$user_id, false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("groups", $res);
            $this->assertCount(1, $res["groups"]);
        }
        
        /**
         * @depends testJoin
         */
        public function testLeave($params)
        {
            list($count, $id, $user_id, $username, $password) = $params;
            $this->login($username, $password);
            $res = $this->get('/leave?id='.$id, false, true);
            $this->assertTrue($res["success"]);
            $res = $this->get('/listMembers?id='.$id, false, true);
            $this->assertTrue($res["success"]);
            $this->assertCount(($count-1), $res['members']);
        }

        public function testDeleteGroup()
        {
            //$this->markTestSkipped();
            $this->login();
            $res = $this->get('/createGroup?' . http_build_query(["title"=>"Lorem Ipsum", "description"=>$this->faker->text]), false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("id", $res);
            $id = $res["id"];
            $res = $this->get('/deleteGroup?' . http_build_query(["id"=>$id]), false, true);
            //eval(\Psy\sh());
        }
}
