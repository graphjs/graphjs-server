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
  * Administration Controller Test
  */
class AdministrationTest extends TestCase
{

    /*
     ["GET", '/setCustomFields','setCustomFields'],
    ["GET", '/getCustomFields','getCustomFields'],
    ["GET", '/approveMembership','approveMembership'],
    ["GET", '/getPendingMemberships','getPendingMemberships'],
    ["GET", '/getMembershipModerationMode','getMembershipModerationMode'],
    ["GET", '/setMembershipModerationMode','setMembershipModerationMode'],
    ["GET", '/getVerificationRequiredMode','getVerificationRequiredMode'],
    ["GET", '/setVerificationRequiredMode','setVerificationRequiredMode'],
    ["GET", '/getReadOnlyMode','getReadOnlyMode'],
    ["GET", '/setReadOnlyMode','setReadOnlyMode'],
    ["GET", '/getAllModes','getAllModes'],
    ["GET", '/setAllModes','setAllModes'],
    ["GET", '/getObjectCounts','getObjectCounts'],
    ["GET", '/getId','getId'],
    ["GET", '/deleteMember','deleteMember'],
    ["GET", '/getPendingComments','getPendingComments'],
    ["GET", '/deletePendingComment','deletePendingComment'],
    ["GET", '/approvePendingComment','approvePendingComment'],
    ["GET", '/setCommentModeration','setCommentModeration'],
    ["GET", '/setBlogEditor','setBlogEditor'],
    ["GET", '/setFounderPassword','setFounderPassword'],
    ["GET", '/getCommentModeration','getCommentModeration'],
    ["GET", '/getSingleSignonKey','getSingleSignonKey']
*/

        public function testGetMembershipModerationMode()
        {
            $res = $this->get('/getMembershipModerationMode', false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("mode", $res);
        }
        public function testGetAllModes()
        {
            $res = $this->get('/getMembershipModerationMode', false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("mode", $res);
        }

        public function testNoHash()
        {   
            $ops = ["/getPendingMemberships", "/getCommentModeration". "/getSingleSignonKey"];
            $op = $ops[array_rand($ops)];
            $this->login();
            //list($email, $username, $password, $res) = $this->signup();
            $this->expectException('GuzzleHttp\Exception\ServerException');
            $res = $this->get($op, false, true);
            //eval(\Psy\sh());
        }

        // for single signon to work there must be a SINGLE_SIGNON_TOKEN_KEY set in the .env var
        public function testGetterWithAdmin()
        {
            $ops = ["/getPendingMemberships", "/getCommentModeration", "/getSingleSignonKey"];
            $op = $ops[array_rand($ops)];
            $this->login();
            $res = $this->get($op."?hash=".$this->getAdminHash(), false, true);
            $this->assertTrue($res["success"]);
        }

        public function testGetObjectCounts()
        {
            $res = $this->get('/getObjectCounts', false, true);
            $this->assertTrue($res["success"]);
            $this->assertArrayHasKey("actor_count", $res);
            $this->assertArrayHasKey("node_count", $res);
            $this->assertArrayHasKey("edge_count", $res);
        }

        public function testModeSetters()
        {
            $modes = ["ReadOnlyMode", "MembershipModerationMode", "VerificationRequiredMode"];
            foreach($modes as $m) {
                $hash = "?hash=".$this->getAdminHash();
                $this->login();
                $res = $this->get('/get'.$m.$hash, false, true);
                $this->assertTrue($res["success"]);
                $mode = $res["mode"];
                $opposite = (($mode == 1) ? 0 : 1);
                $res = $this->get('/set'.$m.$hash."&mode=".$opposite, false, true);
                $this->assertTrue($res["success"]);
                $res = $this->get('/get'.$m.$hash, false, true);
                $this->assertTrue($res["success"]);
                $mode = $res["mode"];
                $this->assertEquals($mode, $opposite);
            }
        }

}
