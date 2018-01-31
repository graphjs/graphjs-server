<?php
/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace GraphPress;


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

    public static function init2(Server $server, array $controllers, Kernel $kernel): void
    {
        
        self::initSession(...\func_get_args());
        self::initAuthentication(self::$session, ...\func_get_args());
        self::initMessaging(self::$session, ...\func_get_args());
        self::initProfile(self::$session, ...\func_get_args());
        self::initMembers(...\func_get_args());
        self::initContent(self::$session, ...\func_get_args());
        
    }

    protected function initSession(Server $server, array $controllers, Kernel $kernel): void
    {
        if(!isset(self::$session))
            self::$session = new Session(__DIR__ . "/../../sessions");
        $session = self::$session;
        $server->use(function(Request $request, Response $response, $next) use($session, $kernel) {
            $session->start($request, $response);
            $response->addHeader("Access-Control-Allow-Origin", "*");   // cors
            //eval(\Psy\sh());
            $next();
        });
    }

    protected static function initAuthentication(Session $session, Server $server, array $controllers, Kernel $kernel): void
    {
        //$server->get('signup', [$controllers["authentication"], "signup"]);
        $server->get('signup', function(Request $request, Response $response) use ($session, $controllers, $kernel) {
            $controllers["authentication"]->signup($request, $response, $session, $kernel);
        });
        $server->get('login', function(Request $request, Response $response) use ($session, $controllers, $kernel) {
            $controllers["authentication"]->login($request, $response, $session, $kernel);
        });
        //$server->get('logout', [$controllers["authentication"], "logout"]);
        //$server->get('whoami', [$controllers["authentication"], "whoami"]);
        $server->get('logout', function(Request $request, Response $response) use ($session, $controllers) {
            $controllers["authentication"]->logout($request, $response, $session);
        });
        $server->get('whoami', function(Request $request, Response $response) use ($session, $controllers) {
            $controllers["authentication"]->whoami($request, $response, $session);
        });
    }

    protected static function initMessaging(Session $session, Server $server, array $controllers, Kernel $kernel): void
    {
        $server->get('sendMessage', function(Request $request, Response $response) use ($id, $session, $controllers, $kernel) {
            $id = $session->get($request, "id");
            if(is_null($id)) {
                $this->fail($response, "You must be logged in to use this functionality");
                return;
            }
            $controllers["messaging"]->message($request, $response, $session, $kernel, $id);
        });

        $server->get('count', function(Request $request, Response $response) use ($id, $session, $controllers, $kernel) {
            $id = $session->get($request, "id");
            if(is_null($id)) {
                $this->fail($response, "You must be logged in to use this functionality");
                return;
            }
            $controllers["messaging"]->fetchUnreadMessageCount($request, $response, $session, $kernel, $id);
        });

        $server->get('inbox', function(Request $request, Response $response) use ($id, $session, $controllers, $kernel) {
            $id = $session->get($request, "id");
            if(is_null($id)) {
                $this->fail($response, "You must be logged in to use this functionality");
                return;
            }
            $controllers["messaging"]->fetchInbox($request, $response, $session, $kernel, $id);
        });

        $server->get('getMessage', function(Request $request, Response $response) use ($id, $session, $controllers, $kernel) {
            $id = $session->get($request, "id");
            if(is_null($id)) {
                $this->fail($response, "You must be logged in to use this functionality");
                return;
            }
            $controllers["messaging"]->fetchMessage($request, $response, $session, $kernel, $id);
        });
        
    }

    protected static function initProfile(Session $session, Server $server, array $controllers, Kernel $kernel): void
    {
        $server->get('profile', function(Request $request, Response $response) use ($controllers, $kernel) {
            $controllers["profile"]->getProfile($request, $response, $kernel);
        });

        $server->get('setProfile', function(Request $request, Response $response) use ($controllers, $session, $kernel) {
            $id = $session->get($request, "id");
            if(is_null($id)) {
                $this->fail($response, "You must be logged in to use this functionality");
                return;
            }
            $controllers["profile"]->setProfile($request, $response, $session, $kernel, $id);
        });
    }

    protected static function initMembers( Server $server, array $controllers, Kernel $kernel): void
    {
        $server->get('members', function(Request $request, Response $response) use ($controllers, $kernel) {
            $controllers["members"]->getMembers($request, $response, $kernel);
        });
    }

    protected static function initContent(Session $session,  Server $server, array $controllers, Kernel $kernel): void
    {
        $server->get('star', function(Request $request, Response $response) use ($controllers, $kernel) {
            $controllers["members"]->getMembers($request, $response, $kernel);
        });
    }
} 
