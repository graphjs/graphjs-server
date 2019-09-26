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
use Pho\Lib\DHT\PeerInterface;

/**
 * This class contains P2P related controller.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class P2pController extends AbstractController
{

    public function ping(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->succeed($response);
    }

    public function dump(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->succeed($response, [
            "tree" => $GLOBALS["router"]->tree()
        ]);
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
        echo "Parameter is: {$parameter}\n";
        while($i>0) {
            echo "i is: {$i} and router is ".get_class($GLOBALS["router"])."\n";
            $i--;
            $hops = $GLOBALS["router"]->findPeers($parameter);
            echo "Found: ".gettype($hops)."\n";
            if($hops instanceof PeerInterface)
            {
                echo "is peer\n";
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