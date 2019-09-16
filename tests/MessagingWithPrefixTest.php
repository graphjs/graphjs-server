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
  * Messaging Controller Test (with /v1/ prefix)
  */
class MessagingWithPrefixTest extends TestCase
{

    public function testGetInbox()
    {
        $this->login();
        $res = $this->get("/v1/countUnreadMessages", false, true);
        $this->assertTrue($res["success"]);
        $this->assertArrayHasKey("count", $res);
        $count = $res["count"];
        return (int) $count;
    }

    
    public function testSendAnonymousMessage()
    {
        $res = $this->get("/v1/sendAnonymousMessage?sender=".urlencode($this->faker->email)."&message=yes&to=".$this->founder_id);
        $this->assertTrue($res["success"]);
        
    }


    /**
     * @depends testGetInbox
     */
    public function testSendMessage(int $inbox_count)
    {
        list($email, $username, $password, $res) = $this->signup();
        $my_id = $res["id"];
        $res = $this->get("/v1/getOutbox", false, true);
        $this->assertTrue($res["success"]);
        $this->assertCount(0, $res["messages"]);
        $res = $this->get("/v1/getInbox", false, true);
        $this->assertTrue($res["success"]);
        $this->assertCount(0, $res["messages"]);
        $res = $this->get("/v1/getConversations", false, true);
        $this->assertTrue($res["success"]);
        $this->assertCount(0, $res["messages"]);
        $res = $this->get("/v1/sendMessage?message=yes&to=".$this->founder_id, false, true);
        $this->assertTrue($res["success"]);
        $this->assertArrayHasKey("id", $res);
        $msgid = $res["id"];
        $res = $this->get("/v1/getOutbox", false, true);
        $this->assertTrue($res["success"]);
        $this->assertCount(1, $res["messages"]);
        $this->login();
        $res = $this->get("/v1/countUnreadMessages", false, true);
        $this->assertTrue($res["success"]);
        $this->assertArrayHasKey("count", $res);
        $this->assertEquals(($inbox_count+1), $res["count"]);
        $res = $this->get("/v1/getConversation?with=".$my_id, false, true);
        $this->assertTrue($res["success"]);
        $this->assertArrayHasKey("messages", $res);
        $this->assertCount(1, $res["messages"]);
        $res = $this->get("/v1/getMessage?msgid=".$msgid, false, true);
        $this->assertTrue($res["success"]);
        $this->assertArrayHasKey("message", $res);
        $this->assertEquals("yes", $res["message"]["Content"]);
    }
}
