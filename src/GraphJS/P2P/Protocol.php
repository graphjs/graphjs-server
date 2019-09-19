<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphJS\P2P;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pho\Kernel\Kernel;
use GraphJS\Controllers\AbstractController;

/**
 * This class contains P2P related controller.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Protocol extends AbstractController
{

    protected $router;

    public function __construct(Kernel $kernel, bool $jsonp = false)
    {
        parent::__construct($kernel, $jsonp);
        $this->router = new \Pho\DHT\Router();
    }

    public function findPeer(ServerRequestInterface $request, ResponseInterface $response)
    {
        $hops = $this->router->findPeers($parameter);
        if($hops instanceof PeerInterface)
        {
            return [
                "success"=>true,
                "ip" => $hops->ip(),
                "port" => $hops->port()
            ];
        }
        $return = [];
        $i = 0;
        foreach($hops as $peer) {
            //foreach($result as $peer) {
                $return[$i++] = [
                    "id"   => (string) $peer->id(),
                    "port" => $peer->port(),
                    "ip"   => $peer->ip()
                ];
            //}
        }

        return [
            "success" => false,
            "check" => $return
        ];
    }

}