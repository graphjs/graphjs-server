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
    public static function init2(Server $server, array $controllers, Kernel $kernel): void
    {
        $session = new Session(__DIR__ . "/../../sessions");
        $server->use(function(Request $request, Response $response, $next) use($session, $kernel) {
            $session->start($request, $response);
            $response->addHeader("Access-Control-Allow-Origin", "*");   // cors
            //eval(\Psy\sh());
            $next();
        });
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
        
        $server->get('message', function(Request $request, Response $response) use ($session, $controllers, $kernel) {
            $controllers["messaging"]->message($request, $response, $session, $kernel);
        }
    }
} 
