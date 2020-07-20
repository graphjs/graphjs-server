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


use CapMousse\ReactRestify\Http\Request;
use CapMousse\ReactRestify\Http\Response;
use CapMousse\ReactRestify\Http\Session;
use Pho\Kernel\Kernel;
use Valitron\Validator;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Customer;
use Stripe\Plan;

/**
 * Takes care of Member Subscription
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class SubscriptionController extends AbstractController
{
    /**
     * Check Subscription
     *
     * @param Request  $request
     * @param Response $response
     * @param Kernel   $kernel
     * 
     * @return void
     */
    public function checkSubscription(Request $request, Response $response, Kernel $kernel)
    {   
        $data = $request->getQueryParams();
        $stripe_key = trim(getenv('STRIPE_KEY'));
        if(!$data["public_id"] || empty($stripe_key)) {
            $this->fail($response, "Not allowed.");
            return;
        }
        $validation = $this->validator->validate($data, [
            'username' => 'required',
        ]);
        if($validation->fails()) {
            $this->fail($response, "Valid username required.");
            return;
        }
        try {
            $result = $kernel->index()->query(
                "MATCH (n:user {Username: {username}}) RETURN n",
                [ 
                    "username" => $data["username"]
                ]
            );
            $success = (count($result->results()) == 1);
            if(!$success) {
                $this->fail($response, "Information don't match records");
                return;
            }
            $user = $result->results()[0];
            
            
            $stripe = new Stripe();
            $Subscription = new Subscription();
            $customer = new Customer();
            $stripe ->setApiKey($stripe_key);
            $customerData = $customer->all(array('email'=>$user['Email']));
            $subscriptions = $customerData->data[0]->subscriptions->data;
            $subscribedOrNot = false;
            foreach ($subscriptions as $value) {
                if($value->status === "active"){
                    $subscribedOrNot = true;
                }
            }
            
            $this->succeed($response, ["subscribed" => $subscribedOrNot,"customerData" => $customerData]);
        }
        catch(\Exception $e) {
            $this->fail($response, $e);
            return;
        }

    }
    /**
     * Check Subscription
     *
     * @param Request  $request
     * @param Response $response
     * @param Kernel   $kernel
     * 
     * @return void
     */
    public function createSubscription(Request $request, Response $response, Kernel $kernel)
    {   
        $data = $request->getQueryParams();
        $stripe_key = trim(getenv('STRIPE_KEY'));
        if(!$data["public_id"] || empty($stripe_key)) {
            $this->fail($response, "Not allowed.");
            return;
        }
        $validation = $this->validator->validate($data, [
            'email' => 'required',
            'plan' => 'required',
            'source' => 'required'
        ]);
        
        if($validation->fails()) {
            $this->fail($response, "Valid email,plan,source required.");
            return;
        }
        try {
            $email = $data['email'];
            $plan = $data['plan'];
            $source = $data['source'];
            
            $stripe = new Stripe();
            $Subscription = new Subscription();
            $customer = new Customer();
            $stripe ->setApiKey($stripe_key);
            $customerData = $customer->create([
                'email' => $email,
                'source' => $source,
            ]);
            $subscription = $Subscription::create([
                'customer' => $customerData->id,
                'items' => [['plan' => $plan ]],
            ]);
            
            $this->succeed($response, ["subscription" => $subscription]);
        }
        catch(\Exception $e) {
            $this->fail($response, "Invalid Details");
            return;
        }

    }

}
