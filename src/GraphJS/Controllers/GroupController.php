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
use PhoNetworksAutogenerated\User;
use PhoNetworksAutogenerated\UserOut\Create;
use PhoNetworksAutogenerated\Group;
use Pho\Lib\Graph\ID;


/**
 * Takes care of Groups
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class GroupController extends AbstractController
{
    /**
     * Create a new Group
     * 
     * [title, description]
     * 
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * @param string   $id
     * 
     * @return void
     */
    public function createGroup(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'title' => 'required|max:80',
            'description' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Title (up to 80 chars) and Description are required.");
            return;
        }
        $i = $this->kernel->gs()->node($id);
        $group = $i->create($data["title"], $data["description"]);
        $this->succeed(
            $response, [
            "id" => (string) $group->id()
            ]
        );
    }
    
    /**
     * Deletes an existing Group
     * 
     * [title, description]
     * 
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * @param string   $id
     * 
     * @return void
     */
    public function deleteGroup(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required'
        ]);
        if($validation->fails()) {
            $this->fail($response, "Group ID is required.");
            return;
        }
        
        $i = $this->kernel->gs()->node($id);

        $group = $this->kernel->gs()->node($data["id"]);
        if(!$group instanceof Group) {
            return $this->fail($response, "Valid Group ID is required.");
        }
        
        $group_owner = $group->edges()->in(Create::class)->current()->tail()->id()->toString();
        if($group_owner!=$id && $this->kernel->founder()->id()->toString()!=$id ) {
            return $this->fail($response, "You do not have privileges to delete this group.");
        }
        
        $group->destroy();
        $this->succeed($response);
    }

    public function setGroup(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        // Avatar, Birthday, About, Username, Email
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Group ID is required.");
            return;
        }
    
        $i = $this->kernel->gs()->node($id);
        $sets = [];

        $group = $this->kernel->gs()->node($data["id"]);
        if(!$group instanceof Group) {
            return $this->fail($response, "Valid Group ID is required.");
        }

        $group_owner = $group->edges()->in(Create::class)->current()->tail()->id()->toString();
        if($group_owner!=$id) {
            return $this->fail($response, "You do not have privileges to edit this group.");
        }

        if(isset($data["title"])) {
            if(strlen($data["title"])>80) {
                $this->fail($response, "Title must be 80 chars or less.");
                return;
            }
            $sets[] = "title";
            $group->setTitle($data["title"]);
        }

        if(isset($data["description"])) {
            $sets[] = "description";
            $group->setDescription($data["description"]);
        }

        if(isset($data["cover"])) {
            if(!preg_match('/^https?:\/\/.+\.(png|jpg|jpeg|gif)$/i', $data["cover"])) {
                $this->fail($response, "Cover field should point to a URL.");
                return;
            }
            $sets[] = "cover";
            $group->setCover($data["cover"]);
        }

        if(count($sets)==0) {
            $this->fail($response, "No field to set");
            return;
        }
        $this->succeed(
            $response, [
            "message" => sprintf(
                "Following fields set successfully: %s", 
                implode(", ", $sets)
            )
            ]
        );
    }

    /**
     * Leave Group
     * 
     * [id]
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * 
     * @return void
     */
    public function leave(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Group ID  required.");
            return;
        }
        $i = $this->kernel->gs()->node($id);
        $group = $this->kernel->gs()->node($data["id"]);

        if(!($group instanceof Group)) {
            $this->fail($response, "Given ID is not associated with a Group");
            return;
        }

        if(!$group->contains($i->id())) {
            $this->fail($response, "User is not a member of given Group");
            return;
        }

        $i->leave($group);
        $this->succeed($response);
    }

    /**
     * Join Group
     * 
     * [id]
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * 
     * @return void
     */
    public function join(ServerRequestInterface $request, ResponseInterface $response)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Group ID  required.");
            return;
        }
        $i = $this->kernel->gs()->node($id);
        $group = $this->kernel->gs()->node($data["id"]);

        if(!($group instanceof Group)) {
            $this->fail($response, "Given ID is not associated with a Group");
            return;
        }

        $i->join($group);
        $this->succeed($response);
    }


    /**
     * List Memberships
     * 
     * Returns group memberships
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     * @param Kernel   $this->kernel
     * 
     * @return void
     */
    public function listMemberships(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "User ID  required.");
            return;
        }
        $them = $this->kernel->gs()->node($data["id"]);
        $q = $this->listGroups($request, $response, $this->kernel);
        if(!$q[0]) {
            $this->fail($response, "Problem fetching groups");
        }
        $groups = $q[1];
        $their_groups = [];
        foreach($groups as $group) {
            $group_obj = $this->kernel->gs()->node($group["id"]);
            if($group_obj->contains($them->id()))
                $their_groups[] = $group;
        }
        $this->succeed(
            $response, [
            "groups" => $their_groups
            ]
        );
    }


    /**
     * List Groups
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     
     * @param Kernel   $this->kernel
     * 
     * @return void
     */
    public function listGroups(ServerRequestInterface $request, ResponseInterface $response)
    {
        error_log("listing groups");
        $groups = [];
        $everything = $this->kernel->graph()->members();
        error_log("member count is: ".count($everything));
        foreach($everything as $i=>$thing) {
            error_log("Counting: ".$i);
            if($thing instanceof Group) {
                error_log("Counting with success: ".$i);
                error_log("ID: ".(string) $thing->id());
                error_log("title: ".$thing->getTitle());
                error_log("description: ".$thing->getDescription());
                error_log("cover: ".$thing->getCover());
                error_log("count: ".(string) count($thing->members()));
                error_log("creator: ".(string) $thing->getCreator()->id());
                try {
                    $groups[] = [
                        "id" => (string) $thing->id(),
                        "title" => $thing->getTitle(),
                        "description" => $thing->getDescription(),
                        "creator" => (string) $thing->getCreator()->id(),
                        "cover" => (string) $thing->getCover(),
                        "count" => (string) count($thing->members())
                    ];
                }
                catch(\Exception $e) {
                    error_log("There was an error with one of the groups: ".$e->getMessage());
                }
            }
        }
        error_log("About to succeed! ".print_r($groups, true));
        return $this->succeed(
            $response, [
            "groups" => $groups
            ]
        );
    }

    function getGroup(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Group ID  required.");
            return;
        }
        $group = $this->kernel->gs()->node($data["id"]);
        if(!$group instanceof Group) {
            $this->fail($response, sprintf("The object with ID %s is not a Group", $data["id"]));
        }
        $info = [
                "id" => (string) $group->id(),
                "title" => $group->getTitle(),
                "description" => $group->getDescription(),
                "creator" => (string) $group->getCreator()->id(),
                "cover" => (string) $group->getCover(),
                "count" => (string) count($group->members())
        ];
        $info["members"] = array_keys(array_filter(
            $group->members(),
            function (/*mixed*/ $value): bool {
                    return ($value instanceof User);
            }
        ));
        $this->succeed(
            $response, [
            "group" => $info
            ]
        );
    }

    /**
     * List Group Members
     * 
     * [id]
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     * @param Kernel   $this->kernel
     * 
     * @return void
     */
    public function listMembers(ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Group ID  required.");
            return;
        }
        $group = $this->kernel->gs()->node($data["id"]);
        if(!$group instanceof Group) {
            $this->fail($response, "Given ID is not associated with a Group");
            return;
        }
        $members = array_filter(
            $group->members(),
            function (/*mixed*/ $value): bool {
                    return ($value instanceof User);
            }
        );
        $this->succeed(
            $response, [
            "members" => array_keys($members)
            ]
        );
    }
}
