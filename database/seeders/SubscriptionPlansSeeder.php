<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'id' => 'starter_plan',
                'name' => 'Starter',
                'description' => 'Perfect for new agents getting started',
                'monthly_price' => 25.00,
                'annual_price' => 250.00,
                'property_activation_limit' => 10,
                'features' => json_encode([
                    'List up to 10 properties monthly',
                    'Basic lead capture forms',
                    'Email campaigns (500 emails/month)',
                    'Mobile app for property management',
                    'Basic sales reports'
                ]),
                'team_members' => 1,
                'trial_days' => 14,
                'most_popular' => false,
                'banner' => 0,
                'overage_pricing' => json_encode([
                    'additional_team_members' => [
                        'price_per_member' => 10.00,
                        'billing_type' => 'monthly'
                    ],
                    'additional_property_activations' => [
                        'price_per_activation' => 0.50,
                        'minimum_purchase' => 10,
                        'billing_type' => 'pay_as_you_go'
                    ]
                ]),
                'active' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'professional_plan',
                'name' => 'Professional',
                'description' => 'Ideal for established agents and small teams',
                'monthly_price' => 50.00,
                'annual_price' => 500.00,
                'property_activation_limit' => 30,
                'features' => json_encode([
                    'List up to 30 properties monthly',
                    'Advanced lead capture with custom forms',
                    'Email campaigns (2000 emails/month)',
                    'Client contact database',
                    'Advanced CRM with client history tracking',
                    'Automated follow-up email sequences',
                    'Mobile app with offline property viewing',
                    'Detailed analytics and performance reports'
                ]),
                'team_members' => 3,
                'trial_days' => 14,
                'most_popular' => true,
                'banner' => 0,
                'overage_pricing' => json_encode([
                    'additional_team_members' => [
                        'price_per_member' => 15.00,
                        'billing_type' => 'monthly'
                    ],
                    'additional_property_activations' => [
                        'price_per_activation' => 0.40,
                        'minimum_purchase' => 25,
                        'billing_type' => 'pay_as_you_go'
                    ]
                ]),
                'active' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'enterprise_plan',
                'name' => 'Enterprise',
                'description' => 'For brokerages and large teams',
                'monthly_price' => 100.00,
                'annual_price' => 1000.00,
                'property_activation_limit' => 100,
                'features' => json_encode([
                    'List up to 100 properties monthly',
                    'Unlimited lead capture and forms',
                    'Unlimited email marketing campaigns',
                    'Complete CRM suite with deal pipeline tracking',
                    'Full marketing automation workflows',
                    'Multi-agent team management dashboard',
                    'Custom branding and white-label options',
                    'API access for third-party integrations',
                    'Mobile app with team collaboration features',
                    'Custom reports and business intelligence'
                ]),
                'team_members' => 25,
                'trial_days' => 30,
                'most_popular' => false,
                'banner' => 1,
                'overage_pricing' => json_encode([
                    'additional_team_members' => [
                        'price_per_member' => 12.00,
                        'billing_type' => 'monthly'
                    ],
                    'additional_property_activations' => [
                        'price_per_activation' => 0.30,
                        'minimum_purchase' => 50,
                        'billing_type' => 'pay_as_you_go'
                    ]
                ]),
                'active' => true,
                'sort_order' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('Subscription_plans')->insert($plans);
    }
}
