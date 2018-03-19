<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

 namespace GraphJS\Controllers;

use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use CapMousse\ReactRestify\Http\Session;
use Pho\Kernel\Kernel;
use Valitron\Validator;
use PhoNetworksAutogenerated\User;
use Pho\Lib\Graph\ID;


/**
 * Takes care of Content
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class ContentController extends AbstractController 
{
    /**
     * Star 
     * 
     * [url]
     * 
     * @param Request $request
     * @param Response $response
     * @param Session $session
     * @param Kernel $kernel
     * @param string $id
     * 
     * @return void
     */
    public function star(Request $request, Response $response, Session $session, Kernel $kernel)
    {
        if(is_null($id=$this->dependOnSession(...\func_get_args())))
            return;
        $data = $request->getQueryParams();
        $v = new Validator($data);
        $v->rule('required', ['url']);
        $v->rule('url', ['url']);
        if(!$v->validate()) {
            $this->fail($response, "Url required.");
            return;
        }
        $i = $kernel->gs()->node($id);  
        $page = $this->_fromUrlToNode($kernel, $data["url"]);
        $i->star($page);    
        $this->succeed($response);
    }
 
 protected function _fromUrlToNode(Kernel $kernel, string $url) 
 {
        $res = $kernel->index()->query("MATCH (n:page {Url: {url}}) RETURN n", ["url"=>$url]);
        if(count($res->results())==0) 
        {
            return $kernel->founder()->post($url);
        }
        return $kernel->gs()->node($res->results()[0]["udid"]);
 }
 
 public function isStarred(Request $request, Response $response, Session $session, Kernel $kernel)
 {
        $data = $request->getQueryParams();
        $v = new Validator($data);
        $v->rule('required', ['url']);
        $v->rule('url', ['url']);
        if(!$v->validate()) {
            $this->fail($response, "Url required.");
            return;
        }
       $page = $this->_fromUrlToNode($kernel, $data["url"]);
       $starrers = $page->getStarrers();
       $me=$this->dependOnSession(...\func_get_args());
       $this->succeed($response, [
        "count"=>count($starrers), 
        "starred"=>is_null($me) ? false : $page->hasStarrer(ID::fromString($me))]
      );
 }

    /**
     * Fetch starred content
     *
     * @param Request $request
     * @param Response $response
     * @param Session $session
     * @param Kernel $kernel
     * 
     * @return void
     */
    public function fetchStarredContent(Request $request, Response $response, Kernel $kernel)
    {
        $res = $kernel->index()->query("MATCH ()-[e:star]-(n:page) WITH n.Url AS content, count(e) AS star_count RETURN content, star_count ORDER BY star_count");
        $array = $res->results();
        if(count($array)==0) {
            $this->fail($response, "No content starred yet");
        } 
        $this->succeed($response, $array);
    }

}
