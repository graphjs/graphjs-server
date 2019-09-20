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
  * Content Controller Test
  */
class ContentTest extends TestCase
{

    /*
    ["GET", "/isStarred","isStarred"],
    ["GET", "/unstar","unstar"],
    ["GET", "/star","star"],
    ["GET", "/addComment","addComment"],
    ["GET", "/removeComment","removeComment"],
    ["GET", "/editComment","editComment"],
    ["GET", "/getComments","getComments"],
    ["GET", "/getMyStarredContent","getMyStarredContent"],
    ["GET", "/getStarredContent","getStarredContent"],
    ["GET", "/getPrivateContent","getPrivateContent"],
    
    ["GET", "/editPrivateContent","editPrivateContent"],
    ["GET", "/deletePrivateContent","deletePrivateContent"],
*/

        public function testListPrivateContents()
        {
            $this->login();
            $res = $this->get('/listPrivateContents', false, true);
        
            $this->assertArrayHasKey("success", $res);
        }

        public function testAddPrivateContentFalse()
        {
            //$this->expectException("\\Exception");
            $res = $this->get('/addPrivateContent?data=emre');
            $this->assertFalse($res["success"]);
        }

        public function testAddPrivateContent()
        {
            $this->login();
            $res = $this->get('/addPrivateContent?data=emre', false, true);
            $this->assertArrayHasKey("success", $res);
            $this->assertArrayHasKey("id", $res);
            return $res["id"];
        }

        /**
         * @depends testAddPrivateContent
         */
        public function testEditPrivateContent($id)
        {
            $this->login();
            $res = $this->get('/editPrivateContent?id='.$id.'&data=emre2', false, true);
            $this->assertArrayHasKey("success", $res);
        }

        /**
         * @depends testAddPrivateContent
         */
        public function testGetPrivateContent($id)
        {
            $this->login();
            $res = $this->get('/getPrivateContent?id='.$id, false, true);
            $this->assertArrayHasKey("success", $res);
            $this->assertArrayHasKey("contents", $res);
            $this->assertEquals("emre", substr($res["contents"],0 ,4));
        }

        /**
         * @depends testAddPrivateContent
         */
        public function testGetPrivateContentFalse($id)
        {
            //$this->expectException("\\Exception");
            $res = $this->get('/getPrivateContent?id='.$id, false, true);
            $this->assertFalse($res["success"]);
        }

        /**
         * @depends testAddPrivateContent
         */
        public function testDeletePrivateContent($id)
        {
            $this->login();
            $res = $this->get('/deletePrivateContent?id='.$id, false, true);
            $this->assertArrayHasKey("success", $res);
        }

}
