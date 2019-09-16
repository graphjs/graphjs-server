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
  * Blog Controller Test
  */
class BlogTest extends TestCase
{

    /*
    ["GET", "/getBlogPosts",'getBlogPosts'],
    ["GET", "/getBlogPost",'getBlogPost'],
    ["GET", "/startBlogPost",'startBlogPost'],
    ["POST", "/startBlogPost",'startBlogPost'],
    ["GET", "/editBlogPost",'editBlogPost'],
    ["POST", "/editBlogPost",'editBlogPost'],
    ["GET", "/removeBlogPost",'removeBlogPost'],
    ["GET", "/unpublishBlogPost",'unpublishBlogPost'],
    ["GET", "/publishBlogPost",'publishBlogPost'],
    ["GET", "/unpin",'unpin'],
    ["GET", "/pin",'pin']
]);
*/

        public function testGetBlogPosts()
        {
            $res = $this->get('/getBlogPosts');
        
            $this->assertArrayHasKey("success", $res);
            $this->assertArrayHasKey("blogs", $res);
            $this->assertArrayHasKey("total", $res);
        }

        public function testStartBlogPostFalse()
        {
            $this->expectException("GuzzleHttp\Exception\ServerException"); // because not signed in
            $res = $this->post('/startBlogPost', ["title"=>"Lorem Ipsum", "content"=>$this->faker->text]);
        }

        public function testStartBlogPostTrue()
        {
            list($email, $username, $password, $res) = $this->signup();
            
            $res = $this->post('/startBlogPost', ["title"=>"Lorem Ipsum", "content"=>$this->faker->text], false, true);
            $this->assertTrue($res["success"]);
            $blog_id = $res["id"];
            return $blog_id;
        }

        /**
         * @depends testStartBlogPostTrue
         */
        public function testGetBlogPost($blog_id)
        {
            $res = $this->get('/getBlogPost?id='.$blog_id, false, true);
            $this->assertTrue($res["success"]);
            $this->assertSame($blog_id, $res["blog"]["id"]);
            return $res["blog"];
        }

        /**
         //* @depends testStartBlogPostTrue
         */
        public function testEditBlogPost()
        {   
            list($email, $username, $password, $res) = $this->signup();
            $res = $this->post('/startBlogPost', ["title"=>"Lorem Ipsum", "content"=>$this->faker->text], false, true);
            $this->assertTrue($res["success"]);
            $id = $res["id"];
            $new_text = $this->faker->text;
            $res = $this->post('/editBlogPost', ["id"=>$id, "title"=>"some other thing", "content"=>$new_text], false, true);
            $this->assertTrue($res["success"]);
            //eval(\Psy\sh());
        }


}
