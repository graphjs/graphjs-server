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
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     * @param Kernel   $this->kernel
     */
    public function checkSubscription(ServerRequestInterface $request, ResponseInterface $response)
    {   
        $data = $request->getQueryParams();
        $stripe_key = trim(getenv('STRIPE_KEY'));
        if(!$data["public_id"] || empty($stripe_key)) {
            return $this->fail($response, "Not allowed.");
        }
        $validation = $this->validator->validate($data, [
            'username' => 'required',
        ]);
        if($validation->fails()) {
            return $this->fail($response, "Valid username required.");
        }
        try {
            $result = $this->kernel->index()->query(
                "MATCH (n:user {Username: {username}}) RETURN n",
                [ 
                    "username" => $data["username"]
                ]
            );
            $success = (count($result->results()) == 1);
            if(!$success) {
                return $this->fail($response, "Information don't match records");
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
            
            return $this->succeed($response, ["subscribed" => $subscribedOrNot,"customerData" => $customerData]);
        }
        catch(\Exception $e) {
            return $this->fail($response, $e->getMessage());
        }

    }
    /**
     * Check Subscription
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     * 
     * @return void
     */
    public function createSubscription(ServerRequestInterface $request, ResponseInterface $response)
    {   
        $data = $request->getQueryParams();
        $stripe_key = trim(getenv('STRIPE_KEY'));
        if(!$data["public_id"] || empty($stripe_key)) {
            return $this->fail($response, "Not allowed.");
        }
        $validation = $this->validator->validate($data, [
            'email' => 'required',
            'plan' => 'required',
            'source' => 'required'
        ]);
        
        if($validation->fails()) {
            return $this->fail($response, "Valid email,plan,source required.");
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
            
            return $this->succeed($response, ["subscription" => $subscription]);
        }
        catch(\Exception $e) {
            return $this->fail($response, "Invalid Details");
        }

    }

}
