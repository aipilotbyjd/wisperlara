<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (!$subscription) {
            return response()->json([
                'plan' => $user->plan,
                'status' => 'free',
                'subscribed' => false,
            ]);
        }

        return response()->json([
            'plan' => $user->plan,
            'status' => $subscription->stripe_status,
            'subscribed' => true,
            'on_trial' => $subscription->onTrial(),
            'canceled' => $subscription->canceled(),
            'on_grace_period' => $subscription->onGracePeriod(),
            'current_period_end' => $subscription->ends_at ?? $subscription->asStripeSubscription()->current_period_end,
            'cancel_at_period_end' => $subscription->canceled() && $subscription->onGracePeriod(),
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => ['required', 'string', 'in:pro,business'],
            'annual' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $plan = $request->plan;
        $annual = $request->boolean('annual', false);

        $plans = config('plans');
        $priceId = $annual
            ? $plans[$plan]['stripe_price_yearly']
            : $plans[$plan]['stripe_price_monthly'];

        if (!$priceId) {
            return response()->json([
                'error' => 'invalid_plan',
                'message' => 'This plan is not available for subscription.',
            ], 400);
        }

        $checkout = $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => config('app.frontend_url', config('app.url')) . '/subscription/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.frontend_url', config('app.url')) . '/subscription/cancel',
            ]);

        return response()->json([
            'checkout_url' => $checkout->url,
        ]);
    }

    public function portal(Request $request): JsonResponse
    {
        $user = $request->user();

        $portalUrl = $user->billingPortalUrl(
            config('app.frontend_url', config('app.url')) . '/settings/subscription'
        );

        return response()->json([
            'portal_url' => $portalUrl,
        ]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (!$subscription || $subscription->canceled()) {
            return response()->json([
                'error' => 'no_subscription',
                'message' => 'No active subscription to cancel.',
            ], 400);
        }

        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription canceled. You will have access until the end of your billing period.',
            'ends_at' => $subscription->ends_at,
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription('default');

        if (!$subscription || !$subscription->onGracePeriod()) {
            return response()->json([
                'error' => 'cannot_resume',
                'message' => 'No canceled subscription to resume.',
            ], 400);
        }

        $subscription->resume();

        return response()->json([
            'message' => 'Subscription resumed successfully.',
        ]);
    }

    public function plans(): JsonResponse
    {
        $plans = collect(config('plans'))->map(function ($plan, $key) {
            return [
                'id' => $key,
                'name' => $plan['name'],
                'price_monthly' => $plan['price_monthly'],
                'price_yearly' => $plan['price_yearly'],
                'minutes_limit' => $plan['minutes_limit'],
                'features' => $plan['features'],
            ];
        })->values();

        return response()->json([
            'plans' => $plans,
        ]);
    }
}
