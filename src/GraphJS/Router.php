<?php
/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace GraphJS;


use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use CapMousse\ReactRestify\Http\Session;
use Pho\Kernel\Kernel;
use Pho\Server\Rest\Server;

/**
 * Determines routes
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Router extends \Pho\Server\Rest\Router
{

    private static $session;

    /**
     * Expands CORS Urls
     *
     * The input is taken from the command line and expanded into 
     * an array with all HTTP scheme combinations, as well as
     * cleaned format.
     * 
     * @param string $cors
     * @return array
     */
    protected static function expandCorsUrl(string $cors): array
    {
        $final = ["https://graphjs.com", "http://graphjs.com", "https://www.graphjs.com", "http://www.graphjs.com"];
        if(strpos($cors, ";")===false) {
            $urls = [0=>$cors];
        }
        else {
            $urls = explode(";",$cors);
        }
        foreach($urls as $url) {
            $parsed = parse_url($url);
            if(count($parsed)==1&&isset($parsed["path"])) {
                $final[] = "http://".$parsed["path"];
                $final[] = "https://".$parsed["path"];
            }
            elseif(count($parsed)>=2&&isset($parsed["host"])) {
                $final[] = "http://".$parsed["host"] . (isset($parsed["port"])?":{$parsed["port"]}":"");
                $final[] = "https://".$parsed["host"] . (isset($parsed["port"])?":{$parsed["port"]}":"");
            }
            else {
                error_log("skipping unknown format: ".$url." - parsed as    : ".print_r($parsed, true));
            }
        }
        return array_unique($final);
    }

    public static function init2(Server $server, array $controllers, Kernel $kernel, string $cors): void
    {
        

        $server->use(
            function (Request $request, Response $response, $next) use ($kernel, $cors, $server) {
                
                $response->addHeader('Access-Control-Allow-Methods', join(',', [
                    'GET',
                    'POST',
                    'PUT',
                    'DELETE',
                    'OPTIONS',
                    'PATCH',
                    'HEAD',
                ]));
                $response->addHeader('Access-Control-Allow-Headers', join(',', [
                    'Origin',
                    'X-Requested-With',
                    'Content-Type',
                    'Accept',
                    'Authorization',
                ]));
                $data = $request->getQueryParams();
                if(strtolower($data["public_id"])=="79982844-6a27-4b3b-b77f-419a79be0e10") {
                    $response->addHeader("Access-Control-Allow-Origin", "http://localhost:8080");   // cors
                }
                else { 
                    $cors = self::expandCorsUrl($cors);
                    $origin = $request->getHeader("origin");
                    //error_log(print_r($origin, true));
                    $is_production = (null==getenv("IS_PRODUCTION") || getenv("IS_PRODUCTION") === "false") ? false : (bool) getenv("IS_PRODUCTION");
                    //error_log("as follows: ".$is_production. " - ".print_r($origin, true) . " - " . print_r($cors));
                    if(
                        (is_array($origin)&&count($origin)==1) )
                        {
                            error_log("origin problem");
                            error_log(print_r($origin, true));
                        }
                    if($is_production) {
                        if(in_array($origin[0], $cors)) {
                            // $response->addHeader("Access-Control-Allow-Origin", $cors[0]);
                            $response->addHeader("Access-Control-Allow-Origin", $origin[0]); 
                        }
                        else {
                            error_log("cors problem");
                            error_log("cors: ".print_r($cors, true));
                            error_log("origin: ".print_r($origin, true));
                            $response->addHeader("Access-Control-Allow-Origin", $cors[0]);
                        }
                    }
                    else {
                        $response->addHeader("Access-Control-Allow-Origin", $origin[0]); 
                    }
                }
                error_log("request method: ". $request->getMethod());
                
                $server->on('NotFound', function($request, $response) {
                    if("options"==strtolower($request->getMethod())) {
                        error_log("options request");
                        $response->addHeader('Access-Control-Allow-Methods', join(',', [
                            'GET',
                            'POST',
                            'PUT',
                            'DELETE',
                            'OPTIONS',
                            'PATCH',
                            'HEAD',
                        ]));
                        $response->addHeader('Access-Control-Allow-Headers', join(',', [
                            'Origin',
                            'X-Requested-With',
                            'Content-Type',
                            'Accept',
                            'Authorization',
                        ]));
                        $response->addHeader('Access-Control-Allow-Credentials', 'true');
                        $origin = $request->getHeader("origin");
                        $response
                            ->addHeader("Access-Control-Allow-Origin", $origin[0])
                            ->setStatus(200)
                            ->end();
                    }
                    else {
                        $response
                            ->setStatus(404)
                            ->write('Not found')
                            ->end();
                    }
                });
                $next();
            }
        );
        $server->use(function(Request $request, Response $response, $next) {
            $is_inactive = (null==getenv("IS_INACTIVE") || intval(getenv("IS_INACTIVE")) != 1) ? false : true;
            if(!$is_inactive) {
                $next();
            }
            else {
                $response
                    ->writeJson([
                        "success" => false,
                        "reason"   => "Instance inactive"
                    ])
                    ->end();
            }
        });
        self::initSession(...\func_get_args());
        self::initAuthentication(...\func_get_args());
        self::initMessaging(...\func_get_args());
        self::initProfile(...\func_get_args());
        self::initMembers(...\func_get_args());
        self::initContent(...\func_get_args());
        self::initForum(...\func_get_args());
        self::initGroup(...\func_get_args());
        self::initFeed(...\func_get_args());
        self::initBlog(...\func_get_args());
        self::initNotifications(...\func_get_args());
        self::initAdministration(...\func_get_args());
        self::initFileUpload(...\func_get_args());

        $server->get('', function(Request $request, Response $response) {
            $response
                    ->addHeader("Access-Control-Allow-Credentials", "true")
                    ->writeJson([
                        "success" => false,
                        "reason"   => "Path does not exist. Try /whoami"
                    ])
                    ->end();
        }); 
    }

    

    protected static function initSession(Server $server, array $controllers, Kernel $kernel): void
    {
        if(!isset(self::$session)) {
            self::$session = new Session(__DIR__ . "/../../sessions");
        }
        $session = self::$session;
        $server->use(
            function (Request $request, Response $response, $next) use ($session, $kernel) {
                $session->start($request, $response);
                //$response->addHeader("Access-Control-Allow-Origin", "http://localhost:8080");   // cors
                //eval(\Psy\sh());
                $next();
            }
        );
    }

    protected static function initNotifications(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'getNotificationsCount', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["notifications"]->count($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'getNotifications', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["notifications"]->read($request, $response, $session, $kernel);
            }
        );
    }

    protected static function initAdministration(Server $server, array $controllers, Kernel $kernel): void
    {

        $server->get(
            'getId', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->fetchId($request, $response, $kernel);
            }
        );

        $server->get(
            'deleteMember', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->deleteMember($request, $response, $kernel);
            }
        );
        
        $server->get(
            'getPendingComments', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->fetchAllPendingComments($request, $response, $kernel);
            }
        );

        $server->get(
            'deletePendingComment', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->disapprovePendingComment($request, $response, $kernel);
            }
        );

        $server->get(
            'approvePendingComment', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->approvePendingComment($request, $response, $kernel);
            }
        );

        $server->get(
            'setCommentModeration', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->setCommentModeration($request, $response, $kernel);
            }
        );

        $server->get(
            'setBlogEditor', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->setBlogEditor($request, $response, $kernel);
            }
        );

        $server->get(
            'setFounderPassword', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->setFounderPassword($request, $response, $kernel);
            }
        );

        $server->get(
            'getCommentModeration', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["administration"]->getCommentModeration($request, $response, $kernel);
            }
        );
    }

    protected static function initBlog(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'getBlogPosts', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->fetchAll($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'getBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->fetch($request, $response, $session, $kernel);
            }
        );
        $server->post(
            'startBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->post($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'startBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->post($request, $response, $session, $kernel);
            }
        );
        $server->post(
            'editBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->edit($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'editBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->edit($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'removeBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->delete($request, $response, $session, $kernel);
            }
        );
        
        $server->get(
            'unpublishBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->unpublish($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'publishBlogPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["blog"]->publish($request, $response, $session, $kernel);
            }
        );
    }

    protected static function initFeed(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'generateFeedToken', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["feed"]->generate($request, $response, $kernel);
            }
        );
    }

    protected static function initAuthentication(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'tokenSignup', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["authentication"]->signupViaToken($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'signup', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["authentication"]->signup($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'tokenLogin', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["authentication"]->loginViaToken($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'login', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["authentication"]->login($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'logout', function (Request $request, Response $response) use ($session, $controllers) {
                $controllers["authentication"]->logout($request, $response, $session);
            }
        );
        $server->get(
            'whoami', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["authentication"]->whoami($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'resetPassword', function (Request $request, Response $response) use ($controllers) {
                $controllers["authentication"]->reset($request, $response);
            }
        );
        $server->get(
            'verifyReset', function (Request $request, Response $response) use ($session, $kernel, $controllers) {
                $controllers["authentication"]->verify($request, $response, $session, $kernel);
            }
        );
    }

    protected static function initMessaging(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        

        $server->get(
            'sendAnonymousMessage', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->message($request, $response, $session, $kernel, true);
            }
        );

        $server->get(
            'sendMessage', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->message($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'countUnreadMessages', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->fetchUnreadMessageCount($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'getInbox', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->fetchInbox($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'getOutbox', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->fetchOutbox($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'getConversations', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->fetchConversations($request, $response, $session, $kernel);
            }
        );
        
        $server->get(
            'getConversation', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->fetchConversation($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'getMessage', function (Request $request, Response $response) use ($session, $controllers, $kernel) {
                $controllers["messaging"]->fetchMessage($request, $response, $session, $kernel);
            }
        );
        
    }

    protected static function initProfile(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'getProfile', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["profile"]->getProfile($request, $response, $kernel);
            }
        );

        $server->get(
            'setProfile', function (Request $request, Response $response) use ($controllers, $session, $kernel) {
                $controllers["profile"]->setProfile($request, $response, $session, $kernel);
            }
        );
    }

    protected static function initMembers( Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'getMembers', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["members"]->getMembers($request, $response, $kernel);
            }
        );
        $server->get(
            'getFollowers', function (Request $request, Response $response) use ($controllers, $session, $kernel) {
                $controllers["members"]->setExceptionHandler($response)->getFollowers($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'getFollowing', function (Request $request, Response $response) use ($controllers, $session, $kernel) {
                $controllers["members"]->getFollowing($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'unfollow', function (Request $request, Response $response) use ($controllers, $session, $kernel) {
                $controllers["members"]->unfollow($request, $response, $session, $kernel);
            }
        );
        $server->get(
            'follow', function (Request $request, Response $response) use ($controllers, $session, $kernel) {
                $controllers["members"]->follow($request, $response, $session, $kernel);
            }
        );
        
    }

    protected static function initContent(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        
        
        $server->get(
            'isStarred', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->isStarred($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'unstar', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->unstar($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'star', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->star($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'addComment', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->comment($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'removeComment', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->delComment($request, $response, $session, $kernel);
            }
        );
        
        $server->get(
            'editComment', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->edit($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'getComments', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["content"]->fetchComments($request, $response, $kernel);
            }
        );

        $server->get(
            'getMyStarredContent', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->fetchMyStars($request, $response, $session, $kernel);
            }
        );
        
        $server->get(
            'getStarredContent', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["content"]->fetchStarredContent($request, $response, $kernel);
            }
        );

        $server->get(
            'getPrivateContent', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->getPrivateContent($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'addPrivateContent', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->addPrivateContent($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'editPrivateContent', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->editPrivateContent($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'deletePrivateContent', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["content"]->delPrivateContent($request, $response, $session, $kernel);
            }
        );
    }

    protected static function initForum( Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;

        $server->get(
            'editForumPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["forum"]->edit($request, $response, $session, $kernel);
            }
        );
        
        $server->get(
            'deleteForumPost', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["forum"]->delete($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'startThread', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["forum"]->startThread($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'reply', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["forum"]->replyThread($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'getThreads', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["forum"]->getThreads($request, $response, $kernel);
            }
        );

        $server->get(
            'getThread', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["forum"]->getThread($request, $response, $kernel);
            }
        );
    }

    protected static function initGroup(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = self::$session;
        $server->get(
            'createGroup', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["group"]->createGroup($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'setGroup', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["group"]->setGroup($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'join', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["group"]->joinGroup($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'leave', function (Request $request, Response $response) use ($controllers, $kernel, $session) {
                $controllers["group"]->leaveGroup($request, $response, $session, $kernel);
            }
        );

        $server->get(
            'listGroups', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["group"]->listGroups($request, $response, $kernel);
            }
        );

        $server->get(
            'getGroup', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["group"]->fetchGroup($request, $response, $kernel);
            }
        );

        $server->get(
            'listMemberships', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["group"]->listMemberships($request, $response, $kernel);
            }
        );

        $server->get(
            'listMembers', function (Request $request, Response $response) use ($controllers, $kernel) {
                $controllers["group"]->listMembers($request, $response, $kernel);
            }
        );
    }

    protected function initFileUpload(Server $server, array $controllers, Kernel $kernel)
    {
        $session = self::$session;
        $server->post(
            'uploadFile', function (Request $request, Response $response) use ($controllers, $session, $kernel) {
                $controllers["fileupload"]->upload($request, $response, $session, $kernel);
        }
        );
    }
} 
