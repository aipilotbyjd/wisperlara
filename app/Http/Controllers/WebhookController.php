<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierController
{
    public function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $data = $payload['data']['object'];
        
        if ($user = $this->getUserByStripeId($data['customer'])) {
            $plan = $this->getPlanFromPriceId($data['items']['data'][0]['price']['id'] ?? null);
            $user->update(['plan' => $plan]);
        }

        return parent::handleCustomerSubscriptionCreated($payload);
    }

    public function handleCustomerSubscriptionUpdated(array $payload): Response
    {
        $data = $payload['data']['object'];
        
        if ($user = $this->getUserByStripeId($data['customer'])) {
            $plan = $this->getPlanFromPriceId($data['items']['data'][0]['price']['id'] ?? null);
            $user->update(['plan' => $plan]);
        }

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $data = $payload['data']['object'];
        
        if ($user = $this->getUserByStripeId($data['customer'])) {
            $user->update(['plan' => 'free']);
        }

        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    protected function getUserByStripeId($stripeId)
    {
        return User::where('stripe_id', $stripeId)->first();
    }

    protected function getPlanFromPriceId(?string $priceId): string
    {
        if (!$priceId) {
            return 'free';
        }

        $plans = config('plans');
        
        foreach ($plans as $planKey => $plan) {
            if ($plan['stripe_price_monthly'] === $priceId || $plan['stripe_price_yearly'] === $priceId) {
                return $planKey;
            }
        }

        return 'free';
    }
}
