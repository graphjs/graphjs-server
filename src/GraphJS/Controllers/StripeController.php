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

/**
 * Takes care of Members
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class StripeController extends AbstractController
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
        if(!$data["public_id"] || strtolower($data["public_id"])!=getenv('STRIPE_FUNCTION_AVAILABLE')) {
            $this->fail($response, "Not allowed.");
            return;
        }
        $v = new Validator($data);
        $v->rule('required', ['email']);
        if(!$v->validate()) {
            $this->fail($response, "Valid Email required.");
            return;
        }
        try {
            $stripe = new Stripe();
            $Subscription = new Subscription();
            $customer = new Customer();
            $stripe ->setApiKey(getenv('STRIPE_KEY'));
            $customerData = $customer->all(array('email'=>$data["email"]));
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
            $this->fail($response, "Invalid Details");
            return;
        }

    }

}
