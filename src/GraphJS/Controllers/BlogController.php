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

  /**
 * Takes care of Blog functionality
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class BlogController extends AbstractController
{
    // postBlog
    // > $user->postBlog("title", "content");
    // editBlog
    // dleeteBlog

    public function fetchAll(Request $request, Response $response, Session $session, Kernel $kernel)
    {
        
        $blogs = [];
        $everything = $kernel->graph()->members();
        
        foreach($everything as $thing) {
            if($thing instanceof Blog) {
                $blogs[] = [
                    "id" => (string) $thing->id(),
                    "title" => $thing->getTitle(),
                    "summary" => $thing->getContent(),
                    "author" => (string) $thing->edges()->in(Post::class)->current()->tail()->id(),
                    "timestamp" => (string) $thing->getCreateTime(),
                ];
            }
        }
        $this->succeed(
            $response, [
                "blogs" => $blogs
            ]
        );
    }

    public function fetch(Request $request, Response $response, Session $session, Kernel $kernel)
    {
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            return $this->fail($response, "Title (up to 255 chars) and Content are required.");
        }
        try {
            $blog = $kernel->gs()->node($data["id"]);
        }
        catch(\Exception $e) {
            return $this->fail($response, "No such Blog Post");
        }
        if(!$blog instanceof Blog) {
            return $this->fail($response, "Given id is not a blog post");
        }
        $this->succeed(
            $response, [
                "blog" => [
                    "id" => (string) $blog->id(),
                    "title" => $blog->getTitle(),
                    "summary" => $blog->getContent(),
                    "author" => (string) $blog->edges()->in(Post::class)->current()->tail()->id(),
                    "timestamp" => (string) $blog->getCreateTime(),
                ]
            ]
        );
    }



    public function post(Request $request, Response $response, Session $session, Kernel $kernel)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'title' => 'required|max:255',
            'content' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Title (up to 255 chars) and Content are required.");
            return;
        }
        $i = $kernel->gs()->node($id);
        $blog = $i->postBlog($data["title"], $data["message"]);
        $this->succeed(
            $response, [
                "id" => (string) $blog->id()
            ]
        );
    }


    public function edit(Request $request, Response $response, Session $session, Kernel $kernel) 
    {
     if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
     $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
            'title'=>'required',
            'content' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "ID, Title and Content are required.");
            return;
        }
        $i = $kernel->gs()->node($id);
        try {
        $entity = $kernel->gs()->entity($data["id"]);
        }
        catch(\Exception $e) 
        {
            return $this->fail($response, "Invalid ID");
        }
        if(!$entity instanceof Blog) {
            $this->fail($response, "Given ID is not a Blog.");
            return;
        }
        try {
        $i->edit($entity)->setTitle($data["title"]);
        $i->edit($entity)->setContent($data["content"]);
        }
     catch(\Exception $e) {
        $this->fail($response, $e->getMessage());
            return;
     }
     $this->succeed($response);
    }


    public function delete(Request $request, Response $response, Session $session, Kernel $kernel)
    {
        if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
            return;
        }
        $data = $request->getQueryParams();
        $validation = $this->validator->validate($data, [
            'id' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "ID is required.");
            return;
        }
        try {
            $i = $kernel->gs()->node($id);
            try {
            $blog = $kernel->gs()->node($data["id"]);
            }
            catch(\Exception $e) {
                return $this->fail($response, "Invalid ID");
            }
            if(!$private_content instanceof Blog) {
                return $this->fail($response, "Invalid ID");
            }
            // check author
            if(
                !$i->id()->equals($kernel->founder()->id()) 
                &&
                !$blog->getAuthor()->id()->equals($i->id())
            ) {
                return $this->fail($response, "No privileges to delete this content");
            }
            $blog->destroy();
            return $this->succeed($response);
        }
        catch (\Exception $e) {
            return $this->fail($response, "Invalid ID");
        }
    }




    public function publish(Request $request, Response $response, Session $session, Kernel $kernel)
    {

     if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
        return;
    }
 $data = $request->getQueryParams();
    $validation = $this->validator->validate($data, [
        'id' => 'required',
    ]);
    if($validation->fails()) {
        $this->fail($response, "ID is required.");
        return;
    }
    $i = $kernel->gs()->node($id);
    try {
    $entity = $kernel->gs()->entity($data["id"]);
    }
    catch(\Exception $e) 
    {
        return $this->fail($response, "Invalid ID");
    }
    if(!$entity instanceof Blog) {
        $this->fail($response, "Given ID is not a Blog.");
        return;
    }
    try {
    $i->edit($entity)->setIsDraft(false);
    }
 catch(\Exception $e) {
    $this->fail($response, $e->getMessage());
        return;
 }
 $this->succeed($response);
    }

    public function unpublish(Request $request, Response $response, Session $session, Kernel $kernel)
    {

     if(is_null($id = $this->dependOnSession(...\func_get_args()))) {
        return;
    }
 $data = $request->getQueryParams();
    $validation = $this->validator->validate($data, [
        'id' => 'required'
    ]);
    if($validation->fails()) {
        $this->fail($response, "ID is required.");
        return;
    }
    $i = $kernel->gs()->node($id);
    try {
    $entity = $kernel->gs()->entity($data["id"]);
    }
    catch(\Exception $e) 
    {
        return $this->fail($response, "Invalid ID");
    }
    if(!$entity instanceof Blog) {
        $this->fail($response, "Given ID is not a Blog.");
        return;
    }
    try {
        $i->edit($entity)->setIsDraft(true);
    }
 catch(\Exception $e) {
    $this->fail($response, $e->getMessage());
        return;
 }
 $this->succeed($response);
    }

}