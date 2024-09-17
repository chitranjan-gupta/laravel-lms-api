<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\StripeCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe;

class StripeController extends Controller
{
    public function checkout(Request $request, $courseId)
    {
        if (Auth::user()) {
            if ($courseId) {
                $user = Auth::user();
                $course = Course::where('id', $courseId)->where('isPublished', true)->first();
                if (!$course) {
                    return response('Not Found', 404);
                }
                $purchase = Purchase::where('userId', $user->id)->where('courseId', $courseId)->first();
                if ($purchase) {
                    return response('Already Purchased', 400);
                }
                Stripe\Stripe::setApiKey(env('STRIPE_API_KEY'));

                $stripeCustomer = StripeCustomer::where('userId', $user->id)->first();

                if (!$stripeCustomer) {
                    $customer = Stripe\Customer::create([
                        'email' => $user->email
                    ]);

                    $stripeCustomer = StripeCustomer::create([
                        'userId' => $user->id,
                        'stripeCustomerId' => $customer->id
                    ]);
                }

                $session = Stripe\Checkout\Session::create([
                    'customer' => $stripeCustomer->stripeCustomerId,
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'inr',
                                'product_data' => [
                                    'name' => $course->title,
                                    'description' => $course->description,
                                    'images' => [$course->imageUrl]
                                ],
                                'unit_amount' => round($course->price * 100)
                            ],
                            'quantity' => 1
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => env('FRONTEND_APP_URL') . '/courses/' . $course->id . '?success=1',
                    'cancel_url' => env('FRONTEND_APP_URL') . '/courses/' . $course->id . '?canceled=1',
                    'metadata' => [
                        'courseId' => $course->id,
                        'userId' => $user->id
                    ]
                ]);

                if ($session) {
                    return response()->json(["url" => $session->url]);
                } else {
                    return response('Unauthorized', 401);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function intent(Request $request)
    {
        if (Auth::user()) {
            $data = $request->all();
            if ($data && $data['courseId']) {
                $courseId = $data['courseId'];
                $user = Auth::user();
                $course = Course::where('id', $courseId)->where('isPublished', true)->first();
                if (!$course) {
                    return response('Not Found', 404);
                }
                $purchase = Purchase::where('userId', $user->id)->where('courseId', $courseId)->first();
                if ($purchase) {
                    return response('Already Purchased', 400);
                }
                Stripe\Stripe::setApiKey(env('STRIPE_API_KEY'));

                $stripeCustomer = StripeCustomer::where('userId', $user->id)->first();

                if (!$stripeCustomer) {
                    $customer = Stripe\Customer::create([
                        'email' => $user->email
                    ]);

                    $stripeCustomer = StripeCustomer::create([
                        'userId' => $user->id,
                        'stripeCustomerId' => $customer->id
                    ]);
                }
                
                $ephemeralKey = Stripe\EphemeralKey::create(["customer" => $stripeCustomer->stripeCustomerId],["stripe_version" => "2024-06-20"]);

                $paymentIntent = Stripe\PaymentIntent::create([
                    'amount' => round($course->price * 100),
                    'currency' => 'inr',
                    'customer' => $stripeCustomer->stripeCustomerId,
                    'description' => $course->description,
                    'shipping' => [
                        'name' => $user->name,
                        'address' => [
                            'line1' => env('STRIPE_TEMP_ADDRESS_LINE1'),
                            'postal_code' =>  env('STRIPE_TEMP_ADDRESS_POSTAL_CODE'),
                            'city' =>  env('STRIPE_TEMP_ADDRESS_CITY'),
                            'state' =>  env('STRIPE_TEMP_ADDRESS_STATE'),
                            'country' =>  env('STRIPE_TEMP_ADDRESS_COUNTRY'),
                        ],
                    ],
                    'automatic_payment_methods' => ['enabled' => true, "allow_redirects" => "never"],
                    'metadata' => [
                        'courseId' => $course->id,
                        'userId' => $user->id
                    ]
                ]);
                if ($paymentIntent && $ephemeralKey) {                    
                    return response()->json(["paymentIntent" => $paymentIntent, "ephemeralKey" => $ephemeralKey, "customer" => $stripeCustomer->stripeCustomerId, "userId" => $user->id]);
                } else {
                    return response('Unauthorized', 401);
                }
            }
        } else {
            return response('Unauthorized', 401);
        }
    }

    public function completeIntent(Request $request)
    {
        if (Auth::user()) {
            $user = Auth::user();
            $data = $request->all();
            if ($data && $data['payment_method_id'] && $data['payment_intent_id'] && $data['customer_id'] && $data['client_secret']) {
                $stripe = new \Stripe\StripeClient(env('STRIPE_API_KEY'));
                $paymentMethod = $stripe->paymentMethods->attach($data['payment_method_id'], ["customer" => $data['customer_id']]);
                $result = $stripe->paymentIntents->confirm($data['payment_intent_id'], ["payment_method" => $paymentMethod->id]);
                return response()->json([
                    "success" => true,
                    "message" => "Payment successful",
                    "result" => $result,
                    "userId" => $user->id
                ]);
            } else {
                return response('Missing required fields', 400);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }
}
