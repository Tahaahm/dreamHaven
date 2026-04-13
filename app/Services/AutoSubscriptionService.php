<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\SubscriptionPlan;
use App\Models\Subscription\Subscription;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AutoSubscriptionService
{
    /**
     * Auto-assign the default 6-month subscription plan to a newly registered office.
     * Safe to call after RealEstateOffice::create(). Never throws.
     */
    public function assignDefaultOfficeSubscription(RealEstateOffice $office): bool
    {
        try {
            $plan = SubscriptionPlan::where('type', 'real_estate_office')
                ->where('duration_label', '6_months')
                ->where('active', true)
                ->first();

            if (!$plan) {
                Log::warning('[AutoSubscription] No active 6-month real_estate_office plan found — skipping.', [
                    'office_id' => $office->id,
                ]);
                return false;
            }

            $subscription = $this->createSubscription($office->id, $plan);

            $office->update(['subscription_id' => $subscription->id]);

            Log::info('[AutoSubscription] Office subscription assigned.', [
                'office_id'       => $office->id,
                'plan_id'         => $plan->id,
                'plan_name'       => $plan->name,
                'subscription_id' => $subscription->id,
                'end_date'        => $subscription->end_date,
            ]);

            // Notification failure must never block registration
            $this->notifyOffice($office, $plan, $subscription);

            return true;
        } catch (\Throwable $e) {
            Log::error('[AutoSubscription] Failed to assign office subscription.', [
                'office_id' => $office->id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Auto-assign the default 6-month subscription plan to a newly registered agent.
     * Safe to call after Agent::save(). Never throws.
     */
    public function assignDefaultAgentSubscription(Agent $agent): bool
    {
        try {
            $plan = SubscriptionPlan::where('type', 'agent')
                ->where('duration_label', '6_months')
                ->where('active', true)
                ->first();

            if (!$plan) {
                Log::warning('[AutoSubscription] No active 6-month agent plan found — skipping.', [
                    'agent_id' => $agent->id,
                ]);
                return false;
            }

            $subscription = $this->createSubscription($agent->id, $plan);

            // Only patch subscription_id if the column exists on agents table
            if (Schema::hasColumn('agents', 'subscription_id')) {
                $agent->update(['subscription_id' => $subscription->id]);
            }

            Log::info('[AutoSubscription] Agent subscription assigned.', [
                'agent_id'        => $agent->id,
                'plan_id'         => $plan->id,
                'plan_name'       => $plan->name,
                'subscription_id' => $subscription->id,
                'end_date'        => $subscription->end_date,
                'max_properties'  => $plan->max_properties,
            ]);

            // Notification failure must never block registration
            $this->notifyAgent($agent, $plan, $subscription);

            return true;
        } catch (\Throwable $e) {
            Log::error('[AutoSubscription] Failed to assign agent subscription.', [
                'agent_id' => $agent->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function createSubscription(string $userId, SubscriptionPlan $plan): Subscription
    {
        $startDate = now();
        $endDate   = now()->addMonths($plan->duration_months);

        return Subscription::create([
            'user_id'                         => $userId,
            'current_plan_id'                 => $plan->id,
            'status'                          => 'active',
            'start_date'                      => $startDate,
            'end_date'                        => $endDate,
            'billing_cycle'                   => $plan->duration_months >= 12 ? 'annual' : 'monthly',
            'auto_renewal'                    => false,
            'property_activation_limit'       => $plan->max_properties ?? 0,
            'properties_activated_this_month' => 0,
            'remaining_activations'           => $plan->max_properties ?? 0,
            'next_billing_date'               => $endDate,
            'last_payment_date'               => $startDate,
            'trial_period'                    => false,
            'monthly_amount'                  => $plan->price_per_month_iqd ?? 0,
        ]);
    }

    private function notifyOffice(RealEstateOffice $office, SubscriptionPlan $plan, Subscription $subscription): void
    {
        try {
            $lang = $office->language ?? 'en';

            $titles = [
                'en' => 'Subscription Activated 🎉',
                'ar' => 'تم تفعيل الاشتراك 🎉',
                'ku' => 'بەشداری چالاک کرا 🎉',
            ];

            $messages = [
                'en' => "Welcome to Dream Mulk! Your {$plan->name} subscription is now active until " . $subscription->end_date->format('M d, Y') . '.',
                'ar' => "مرحباً بك في Dream Mulk! اشتراكك {$plan->name} نشط الآن حتى " . $subscription->end_date->format('d/m/Y') . '.',
                'ku' => "بخێربێیت بۆ Dream Mulk! بەشداریکردنی {$plan->name} تا " . $subscription->end_date->format('d/m/Y') . ' چالاکە.',
            ];

            $title   = $titles[$lang]   ?? $titles['en'];
            $message = $messages[$lang] ?? $messages['en'];

            // All values cast to string — FCM data payload only supports strings
            $notificationData = [
                'subscription_id' => (string) $subscription->id,
                'plan_name'       => (string) $plan->name,
                'end_date'        => $subscription->end_date->toIso8601String(),
                'max_properties'  => (string) ($plan->max_properties ?? 0),
                'type'            => 'subscription',   // ← short value matching DB column limit
            ];

            // 1. Persist to notifications table
            \App\Models\Notification::create([
                'user_id' => $office->id,
                'title'   => $title,
                'message' => $message,
                'type'    => 'subscription',           // ← fixed: was 'subscription_activated' (too long)
                'data'    => $notificationData,
                'is_read' => false,
                'read_at' => null,
            ]);

            // 2. Push via FCM only if the model has tokens
            $tokens = method_exists($office, 'getFCMTokens') ? $office->getFCMTokens() : [];

            if (!empty($tokens)) {
                /** @var FCMNotificationService $fcm */
                $fcm = app(FCMNotificationService::class);
                foreach ($tokens as $token) {
                    $fcm->sendToToken($token, $title, $message, $notificationData);
                }
            }

            Log::info('[AutoSubscription] Office activation notification saved.', ['office_id' => $office->id]);
        } catch (\Throwable $e) {
            Log::warning('[AutoSubscription] Office notification failed (non-fatal).', [
                'office_id' => $office->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function notifyAgent(Agent $agent, SubscriptionPlan $plan, Subscription $subscription): void
    {
        try {
            $lang = $agent->language ?? 'en';

            $titles = [
                'en' => 'Subscription Activated 🎉',
                'ar' => 'تم تفعيل الاشتراك 🎉',
                'ku' => 'بەشداری چالاک کرا 🎉',
            ];

            $messages = [
                'en' => "Welcome to Dream Mulk! Your {$plan->name} subscription is now active until " . $subscription->end_date->format('M d, Y') . ". You can list up to {$plan->max_properties} properties.",
                'ar' => "مرحباً بك في Dream Mulk! اشتراكك {$plan->name} نشط حتى " . $subscription->end_date->format('d/m/Y') . ". يمكنك إضافة حتى {$plan->max_properties} عقار.",
                'ku' => "بخێربێیت بۆ Dream Mulk! بەشداریکردنی {$plan->name} تا " . $subscription->end_date->format('d/m/Y') . " چالاکە. دەتوانی تا {$plan->max_properties} خانوو زیاد بکەیت.",
            ];

            $title   = $titles[$lang]   ?? $titles['en'];
            $message = $messages[$lang] ?? $messages['en'];

            $notificationData = [
                'subscription_id' => (string) $subscription->id,
                'plan_name'       => (string) $plan->name,
                'end_date'        => $subscription->end_date->toIso8601String(),
                'max_properties'  => (string) ($plan->max_properties ?? 0),
                'type'            => 'subscription',   // ← short value matching DB column limit
            ];

            // 1. Persist to notifications table
            \App\Models\Notification::create([
                'user_id' => $agent->id,
                'title'   => $title,
                'message' => $message,
                'type'    => 'subscription',           // ← fixed: was 'subscription_activated' (too long)
                'data'    => $notificationData,
                'is_read' => false,
                'read_at' => null,
            ]);

            // 2. Push via FCM
            $tokens = method_exists($agent, 'getFCMTokens') ? $agent->getFCMTokens() : [];

            if (!empty($tokens)) {
                /** @var FCMNotificationService $fcm */
                $fcm = app(FCMNotificationService::class);
                foreach ($tokens as $token) {
                    $fcm->sendToToken($token, $title, $message, $notificationData);
                }
            }

            Log::info('[AutoSubscription] Agent activation notification saved.', ['agent_id' => $agent->id]);
        } catch (\Throwable $e) {
            Log::warning('[AutoSubscription] Agent notification failed (non-fatal).', [
                'agent_id' => $agent->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}