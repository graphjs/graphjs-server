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
        self::initAuthentication(...\func_get_args(), self::$session);
        self::initMessaging(...\func_get_args(), self::$session);
        
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

    public function initAuthentication(Server $server, array $controllers, Kernel $kernel, Session $session): void
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

    public function initMessaging(Server $server, array $controllers, Kernel $kernel, Session $session): void
    {
        $id = $session->get($request, "id");
        if(is_null($id)) {
            $this->fail($response, "You must be logged in to use this functionality");
            return;
        }
        $server->get('message', function(Request $request, Response $response) use ($id, $session, $controllers, $kernel) {
            $controllers["messaging"]->message($request, $response, $session, $kernel, $id);
        });
    }
} 
