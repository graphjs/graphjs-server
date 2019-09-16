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
  * Forum Controller Test
  */
class ForumTest extends TestCase
{

    /*
    ["GET", "/editForumPost",'editForumPost'],
    ["GET", "/deleteForumPost",'deleteForumPost'],
    ["GET", "/startThread",'startThread'],
    ["GET", "/reply",'reply'],
    ["GET", "/getThreads",'getThreads'],
    ["GET", "/getThread",'getThread']
]);
*/

        public function testGetThreads()
        {
            $res = $this->get('/getThreads');
        
            $this->assertArrayHasKey("success", $res);
            $this->assertArrayHasKey("threads", $res);
        }

        public function testStartThreadFalse()
        {
            $this->expectException("GuzzleHttp\Exception\ServerException"); // because not signed in
            $res = $this->get('/startThread?' . http_build_query(["title"=>"Lorem Ipsum", "message"=>$this->faker->text]));
        }

        public function testStartThreadTrue()
        {
            $this->login();
            $res = $this->get('/startThread?' . http_build_query(["title"=>"Lorem Ipsum", "message"=>$this->faker->text]), false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("id", $res);
            $id = $res["id"];
            return $id;
        }

        /**
         * @depends testStartThreadTrue
         */
        public function testEditForumPost($id)
        {
            $this->login();
            $res = $this->get('/editForumPost?id='.$id.'&content='.$this->faker->text, false, true);
            $this->assertTrue($res["success"]);
        }

        /**
         * @depends testStartThreadTrue
         */
        public function testGetThread1($id)
        {   
            $res = $this->get('/getThread?id='.$id, false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("messages", $res);
            $this->assertArrayHasKey("title", $res);
            $this->assertCount(1, $res["messages"]);
        }
        

        /**
         * @depends testStartThreadTrue
         */
        public function testReply($id)
        {   
            list($email, $username, $password, $res) = $this->signup();
            $res = $this->get('/reply?id='.$id."&message=".$this->faker->text, false, true);
            $this->assertTrue($res["success"]);
        }

        /**
         * @depends testStartThreadTrue
         */
        public function testGetThread2($id)
        {   
            $res = $this->get('/getThread?id='.$id, false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("messages", $res);
            $this->assertArrayHasKey("title", $res);
            $this->assertCount(2, $res["messages"]);
        }

        /**
         * @depends testStartThreadTrue
         */
        public function testDeleteForumPostFalse($id)
        {   
            $this->expectException("GuzzleHttp\Exception\ServerException"); // because not signed in
            $res = $this->get('/deleteForumPost?id='.$id, false, true);
        }

        /**
         * @depends testStartThreadTrue
         */
        public function testDeleteForumPostTrue($id)
        {   
            $this->login();
            $res = $this->get('/deleteForumPost?id='.$id, false, true);
            $this->assertTrue($res["success"]);
        }

        

}
