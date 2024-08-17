<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe;

class WebhookController extends Controller
{
    public function index(Request $request)
    {

        // The library needs to be configured with your account's secret key.
        // Ensure the key is kept out of any version control system you might be using.
        $stripe = new Stripe\StripeClient(env('STRIPE_API_KEY'));

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('Webhook UN Error', 400);
        } catch (Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Webhook Sign Error', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                Log::info('Webhook Setup completed');
                break;
            case 'checkout.session.completed':
                $session = $event->data->object;

                $userId = $session->metadata->userId;
                $courseId = $session->metadata->courseId;
                
                if (!$userId || !$courseId) {
                    return response('Webhook Error: Missing metadata', 400);
                }
                Purchase::create([
                    'courseId' => $courseId,
                    'userId' => $userId
                ]);
                break;
            default:
            return response('Webhook Error: Unhandled event type', 200);
        }
        return response(null, 200);
    }
}
