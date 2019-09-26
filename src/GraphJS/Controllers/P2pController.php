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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pho\Kernel\Kernel;

/**
 * This class contains P2P related controller.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class P2pController extends AbstractController
{

    protected $router;

    public function __construct(Kernel $kernel, bool $jsonp = false)
    {
        parent::__construct($kernel, $jsonp);
        $this->router = $kernel->router();
    }

    public function ping(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->succeed();
    }

    public function findPeer(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getQueryParams();
        $rules = [
            'id' => 'required'
        ];
        $validation = $this->validator->validate($data, $rules);
        if($validation->fails()||!preg_match("/^[0-9a-fA-F]{32}$/", $data["id"])) {
            return $this->fail($response, "Valid peer ID required.");
        }
        $parameter = strtolower($data["id"]);
        $i = 30;
        while($i>0) {
            $i--;
            $hops = $this->router->findPeers($parameter);
            if($hops instanceof PeerInterface)
            {
                return $this->succeed($response, 
                    [
                        "ip" => $hops->ip(),
                        "port" => $hops->port(),
                        "debug" => "attempt ".(string) (30-$i),
                    ]
                );
            }
            /*
            $return = [];
            $i = 0;
            foreach($hops as $peer) {
                // find peers
            }
            */
        }
        return $this->fail($response);
    }

}