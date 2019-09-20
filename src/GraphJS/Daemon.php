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

use Pho\Kernel\Kernel;
use PhoNetworksAutogenerated\{User, Site, Network};
use React\EventLoop\LoopInterface;
use Pho\Plugins\FeedPlugin;
use WyriHaximus\React\Http\Middleware\SessionMiddleware;
use React\Cache\ArrayCache;
use Sikei\React\Http\Middleware\CorsMiddleware;
//use React\Filesystem\Filesystem as ReactFilesystem;
//use WyriHaximus\React\Cache\Filesystem;
use WyriHaximus\React\Cache\Redis as RedisCache;
use Clue\React\Redis\Factory as RedisFactory;

/**
 * The async/event-driven REST server daemon
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Daemon
{

    use AutoloadingTrait;

    protected $heroku = false;
    protected $kernel;
    protected $server;
    protected $loop;
    
    public function __construct(string $configs = "", string $cors = "", bool $heroku = false, ?LoopInterface &$loop = null)
    {
        if(!isset($loop)) {
            $loop = \React\EventLoop\Factory::create();    
        }
        $this->loop = &$loop;
        $this->heroku = $heroku;
        $this->loadEnvVars($configs);
        $cors .= sprintf(";%s", getenv("CORS_DOMAIN"));
        $this->initKernel();
        $this->server = new Server($this->kernel, $this->loop);
        // won't bootstrap() to skip Pho routes.
        $controller_dir = __DIR__ . DIRECTORY_SEPARATOR . "Controllers";
        $this->server->withControllers($controller_dir);
        $router_dir = __DIR__ . DIRECTORY_SEPARATOR . "Routes";
        $this->server->withRoutes($router_dir);
        $this->addSessionSupport();
        $this->addCorsSupport();
    }

    public function __call(string $method, array $params)//: mixed
    {
        return $this->server->$method(...$params);
    }

    protected function addCorsSupport(): void
    {
        $origins = ["*"];
        $is_production = (null==getenv("IS_PRODUCTION") || getenv("IS_PRODUCTION") === "false") ? false : (bool) getenv("IS_PRODUCTION");
        $env = getenv("CORS_DOMAIN");
        if($is_production && isset($env)&&!empty($env)) 
        {
            $origins = Utils::expandCorsUrl($env);
        }
        $this->server->withMiddleware(
            new CorsMiddleware(
                [
                    'allow_credentials' => true,
                    'allow_origin'      => $origins,
                    'allow_methods'     => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS', 'PATCH'],
                    'allow_headers'     => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range', 'Origin', 'Accept', 'Authorization'],
                    'expose_headers'    => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range', 'Origin', 'Accept', 'Authorization'],
                    'max_age'           => 60 * 60 * 24 * 20, // preflight request is valid for 20 days
                ]
            )
        );
    }

    /**
     * ArrayCache based session, not reliable. 
     * 
     * Don't use in production.
     * 
     * @deprecated dev
     */
    protected function addBasicSessionSupport(): void
    {
        $cache = new ArrayCache;
        $this->server->withMiddleware(
            new SessionMiddleware(
                'id',
                $cache, // Instance implementing React\Cache\CacheInterface
                [ // Optional array with cookie settings, order matters
                    0, // expiresAt, int, default
                    '', // path, string, default
                    '', // domain, string, default
                    false, // secure, bool, default
                    false // httpOnly, bool, default
                ]
            )
        );
    }

    protected function addSessionSupport(): void
    {
        
        //return;
        ////$filesystem = ReactFilesystem::create($this->loop);
        ////$cache = new Filesystem($filesystem, sys_get_temp_dir());
        $uri = getenv("DATABASE_URI");
        //echo ("uri is:".$uri);
        $factory = new \Clue\React\Redis\Factory($this->loop);
        $client = $factory->createLazyClient("redis://127.0.0.1:6379/2");
        //echo ("emre");
        $this->addBasicSessionSupport();
        return;
        //echo ("emel");
        $cache = new \WyriHaximus\React\Cache\Redis($client, 'session:');
        //eval(\Psy\sh());
        //echo ("defne");
        $this->server->withMiddleware(
            new SessionMiddleware(
                'id',
                $cache, // Instance implementing React\Cache\CacheInterface
                [ // Optional array with cookie settings, order matters
                    0, // expiresAt, int, default
                    '', // path, string, default
                    '', // domain, string, default
                    false, // secure, bool, default
                    false // httpOnly, bool, default
                ]
            )
        );
    }

    protected function initKernel(): void
    {
        $configs = array(
            "services"=>array(
                "database" => ["type" => getenv('DATABASE_TYPE'), "uri" => getenv('DATABASE_URI')],
                "storage" => ["type" => getenv('STORAGE_TYPE'), "uri" =>  getenv("STORAGE_URI")],
                "index" => ["type" => getenv('INDEX_TYPE'), "uri" => getenv('INDEX_URI')]
            ),
            "default_objects" => array(
                    "graph" => getenv('INSTALLATION_TYPE') === 'groupsv2' ? Network::class : Site::class,
                    "founder" => User::class,
                    "actor" => User::class
            )
        );
        $this->kernel = new Kernel($configs);
        if(!empty(getenv("STREAM_KEY"))&&!empty(getenv("STREAM_SECRET"))) {
            $feedplugin = new FeedPlugin($this->kernel,  getenv('STREAM_KEY'),  getenv('STREAM_SECRET'));
            $this->kernel->registerPlugin($feedplugin);
        }
        $founder = new User(
            $this->kernel, $this->kernel->space(), 
            getenv('FOUNDER_NICKNAME'), 
            getenv('FOUNDER_EMAIL'), 
            getenv('FOUNDER_PASSWORD')
        );
        $this->kernel->boot($founder);
        //eval(\Psy\sh());
    }

}

