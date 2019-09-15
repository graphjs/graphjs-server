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
    ["GET", "/addPrivateContent","addPrivateContent"],
    ["GET", "/editPrivateContent","editPrivateContent"],
    ["GET", "/deletePrivateContent","deletePrivateContent"],
    ["GET", "/listPrivateContents","listPrivateContents"],
*/

        public function testListPrivateContents()
        {
            $this->login();
            $res = $this->get('/listPrivateContents', false, true);
        
            $this->assertArrayHasKey("success", $res);
        }

}
