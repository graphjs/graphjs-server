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

use SmtpAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use GraphJS\Session;
use Pho\Kernel\Kernel;
use Pho\Lib\Graph\ID;
use GraphJS\Utils;

/**
 * Takes care of Messaging
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class MessagingController extends AbstractController
{
    use SmtpAwareTrait;

    public function sendAnonymousMessage(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->sendMessage($request, $response, true);
    }

    /**
     * Send a Message
     *
     * [to, message]
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response

     * @param Kernel   $this->kernel
     * @param string   $id
     *
     * @return void
     */
    public function sendMessage(ServerRequestInterface $request, ResponseInterface $response, bool $anonymous = false)
    {
        $id = null;
        if(!$anonymous) {
            if(is_null($id = Session::depend($request))) {
                return $this->failSession($response);
            }
        }
        $data = $request->getQueryParams();
        $rules = [
            'to' => 'required',
            'message' => 'required',
        ];
        if($anonymous && is_null($id))
            $rules += [ 'sender' => 'required' ];
        $validation = $this->validator->validate($data, $rules);
        if($validation->fails()) {
            return $this->fail($response, "Valid recipient and message are required.");
        }
        if(!preg_match("/^[0-9a-fA-F][0-9a-fA-F]{30}[0-9a-fA-F]$/", $data["to"])) {
            return $this->fail($response, "Invalid recipient");
            
        }
        if(empty($data["message"])) {
            return $this->fail($response, "Message can't be empty");
            
        }
        if($data["to"]==$id) {
            return $this->fail($response, "Can't send a message to self");
            
        }
        $recipient = $this->kernel->gs()->node($data["to"]);
        if(!is_null($id)) {
            $i = $this->kernel->gs()->node($id);
            $msg = $i->message($recipient, $data["message"]);
        }
        $this->smtpSend(
            ($anonymous && is_null($id)) ? $data["sender"] : $i->getUsername() . ' <postmaster@mg.graphjs.com>',
                $recipient->getEmail(),
                'Private Message',
                $data["message"] . PHP_EOL . (!is_null($id) ? (string) $msg->id() : "")
        );
        if(!is_null($id))
            return $this->succeed(
                $response, [
                    "id" => (string) $msg->id()
                ]
            );
        else
        return $this->succeed($response);
    }

    /**
     * Fetch Unread Message Count
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * @param string   $id
     * 
     * @return void
     */
    public function countUnreadMessages(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = Session::depend($request))) {
            return $this->failSession($response);
        }
        $i = $this->kernel->gs()->node($id);
        $incoming_messages = $i->getIncomingMessages();
        return $this->succeed(
            $response, [
                "count" => (string) count($incoming_messages)
            ]
        );
    }

    /**
     * Fetch Inbox
     * 
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * @param string   $id
     * 
     * @return void
     */
    public function getInbox(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = Session::depend($request))) {
            return $this->failSession($response);
        }
        $i = $this->kernel->gs()->node($id);
        $incoming_messages = $i->getIncomingMessages();
        $ret = [];
        foreach($incoming_messages as $m) 
        {
            $ret[(string) $m->id()] = [
                "from" => $m->tail()->id()->toString(),
                "message" => substr($m->getContent(), 0, 70),
                "is_read" => $m->getIsRead() ? true : false,
                "timestamp" => $m->getSentTime()
            ];
        }
        return $this->succeed(
            $response, [
                "messages" => $ret
            ]
        );
    }


    /**
     * Fetch Inbox
     * 
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * @param string   $id
     * 
     * @return void
     */
    public function getOutbox(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = Session::depend($request))) {
            return $this->failSession($response);
        }
        $i = $this->kernel->gs()->node($id);
        $sent_messages = $i->getSentMessages();
        $ret = [];
        foreach($sent_messages as $m) 
        {
            $ret[(string) $m->id()] = [
                "to" => $m->head()->id()->toString(),
                "message" => substr($m->getContent(), 0, 70),
                "is_read" => $m->getIsRead() ? true : false,
                "timestamp" => $m->getSentTime()
            ];
        }
        return $this->succeed(
            $response, [
                "messages" => $ret
            ]
        );
    }


    public function getConversations(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = Session::depend($request))) {
            return $this->failSession($response);
        }
        $i = $this->kernel->gs()->node($id);
        $sent_messages = $i->getSentMessages();
        $incoming_messages = $i->getIncomingMessages();
        $ret = [];
        foreach($sent_messages as $m) 
        {
            $ts = $m->getSentTime();
            if(array_key_exists(($op=$m->head()->id()->toString()), $ret)
                && $ret[$op]["timestamp"] > $ts
            ) {
                continue;
            }
            $ret[$op] = [
                "id" => (string) $m->id(),
                "from" => $id,
                "to" => $op,
                "message" => substr($m->getContent(), 0, 70),
                "is_read" => $m->getIsRead() ? true : false,
                "timestamp" => $ts
            ];
            $mem[$op] = $ts;
        }
        foreach($incoming_messages as $m) 
        {
            $ts = $m->getSentTime();
            if(array_key_exists(($op=$m->tail()->id()->toString()), $ret)
                && $ret[$op]["timestamp"] > $ts
            ) {
                continue;
            }
            $ret[$op] = [
                "id" => (string) $m->id(),
                "from" => $op,
                "to" => $id,
                "message" => substr($m->getContent(), 0, 70),
                "is_read" => $m->getIsRead() ? true : false,
                "timestamp" => $ts
            ];
        }
        uasort(
            $ret, function ($a,$b) {
                return $a['timestamp']>$b['timestamp'];
            }
        );
        return $this->succeed(
            $response, [
                "messages" => $ret
            ]
        );
    }

    public function getConversation(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = Session::depend($request))) {
            return $this->failSession($response);
        }
            $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'with' => 'required',
        ]);
        if($validation->fails()) {
            return $this->fail($response, "Valid user Id (with) required.");
            
        }
        if(!preg_match("/^[0-9a-fA-F][0-9a-fA-F]{30}[0-9a-fA-F]$/", $data["with"])) {
            return $this->fail($response, "Invalid User ID");
            
        }
        /*
        // not supported with Redis so...
        // @todo make sure RedisGraph supports this too
        $ret = $this->kernel->index()->query(
            "MATCH (:user {udid: {u1}})-[r:message]-(:user {udid: {u2}}) SET r.IsRead = true RETURN startNode(r).udid as t, r ORDER BY r.SentTime DESC",
                array("u1"=>$id, "u2"=>$data["with"])
        );
        $records = $ret->results();
        */
        // instead1
        $ret = $this->kernel->index()->query(
            "MATCH (sn:user {udid: {u1}})-[r:message]->(:user {udid: {u2}}) SET r.IsRead = true RETURN sn.udid as t, r",
                array("u1"=>$id, "u2"=>$data["with"])
        );
        $records1 = $ret->results();
        $ret = $this->kernel->index()->query(
            "MATCH (user {udid: {u1}})<-[r:message]-(sn:user {udid: {u2}}) SET r.IsRead = true RETURN sn.udid as t, r",
                array("u1"=>$id, "u2"=>$data["with"])
        );
        $records2 = $ret->results();
        $records = array_merge($records1, $records2);
        // instead1 ENDS
        $return = [];
        error_log("========> TOTAL RECORDS NUM IS: ".count($records));
        foreach($records as $i=>$res) {
            if(!isset($res["r.udid"])||is_null($res["r.udid"])) {
                error_log("r was not set or was null");
                continue;
            }
            try {
                error_log("about to read an edge");
                error_log("about to read the edge: ".$res["r.udid"]);
                $obj = $this->kernel->gs()->edge($res["r.udid"]);
                error_log("read, now set IsRead");
                error_log(print_r($obj->attributes()->toArray(), true));
                $obj->setIsRead(true);
            }
            catch(\Exception $e) {
                error_log("no message with id: ".$res["r.udid"]);
                continue;
            }
            try {
                $sender = $res["t"];
                error_log("Sender is: ".$sender);
                error_log("With is: ".$data["with"]);
                error_log("r.Content is: ".$res["r.Content"]);
                error_log("r.SentTime is: ".$res["r.SentTime"]);
                $return[$res["r.udid"]] = [
                    "from" => ($sender == $id) ? $id  : $data["with"],
                    "to" => ($sender == $id) ? $data["with"]  : $id,
                    "message" => $res["r.Content"],
                    "is_read" => true,
                    "timestamp" => $res["r.SentTime"]
                ];
            }
            catch(\Exception $e) {
                error_log($e->getMessage());
                continue;
            }
        }
        // instead2
        $return = Utils::sortAssocArrayByValue($return, "timestamp", false, true);
        // instead2 ENDS
        return $this->succeed($response, [ "messages" => $return ]);
    }

    /**
     * Fetch Message
     * 
     * [msgid]
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * @param string   $id
     * 
     * @return void
     */
    public function getMessage(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = Session::depend($request))) {
            return $this->failSession($response);
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'msgid' => 'required',
        ]);
        if($validation->fails()) {
            return $this->fail($response, "Valid message id required.");
            
        }
        if(!preg_match("/^[0-9a-fA-F][0-9a-fA-F]{30}[0-9a-fA-F]$/", $data["msgid"])) {
            return $this->fail($response, "Invalid message ID");
            
        }
        $i = $this->kernel->gs()->node($id);
        $msgid = ID::fromString($data["msgid"]);
        if(!$i->hasIncomingMessage($msgid) && !$i->hasSentMessage($msgid) ) {
            return $this->fail($response, "Message ID is not associated with the logged in user.");
            
        }
        $msg = $this->kernel->gs()->edge($data["msgid"]);
        $recipient = (string) $msg->head()->id();
        if($id==$recipient) {
            $msg->setIsRead(true);
        }
        return $this->succeed(
            $response, [
                "message" => array_merge(
                    $msg->attributes()->toArray(),
                    [
                        "from" => (string) $msg->tail()->id(),
                        "to" => $recipient
                    ]
                )
            ]
        );
    }
}
