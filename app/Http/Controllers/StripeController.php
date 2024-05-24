<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\StripeCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                }else{
                    return response('Unauthorized', 401);
                }
            } else {
                return response('Not Found', 404);
            }
        } else {
            return response('Unauthorized', 401);
        }
    }
}
