<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing plans
        SubscriptionPlan::truncate();

        // Exchange rate
        $exchangeRate = 1310;

        // Banner Advertising Plans
        SubscriptionPlan::create([
            'name' => 'Banner Advertising - Monthly',
            'type' => 'banner',
            'description' => '1-banner price starting from 10k for month',
            'duration_months' => 1,
            'duration_label' => '1_month',
            'original_price_iqd' => 10000,
            'discount_iqd' => 0,
            'final_price_iqd' => 10000,
            'price_per_month_iqd' => 10000,
            'total_amount_iqd' => 10000,
            'original_price_usd' => round(10000 / $exchangeRate, 2),
            'discount_usd' => 0,
            'final_price_usd' => round(10000 / $exchangeRate, 2),
            'price_per_month_usd' => round(10000 / $exchangeRate, 2),
            'total_amount_usd' => round(10000 / $exchangeRate, 2),
            'discount_percentage' => 0,
            'features' => ['Single banner placement', 'Monthly rotation', 'High visibility'],
            'conditions' => ['Banner displayed for full month', 'Premium placement on homepage'],
            'active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        // Services Plans (50% discount applied) - For Companies
        $servicesPlans = [
            [
                'duration' => 1,
                'label' => '1_month',
                'original' => 50000,  // Before 50% discount
                'final' => 25000,     // After 50% discount = 25k
            ],
            [
                'duration' => 3,
                'label' => '3_months',
                'original' => 140000, // Before 50% discount
                'final' => 70000,     // After 50% discount = 70k
            ],
            [
                'duration' => 6,
                'label' => '6_months',
                'original' => 230000, // Before 50% discount
                'final' => 115000,    // After 50% discount = 115k
            ],
            [
                'duration' => 12,
                'label' => '1_year',
                'original' => 500000, // Before 50% discount
                'final' => 250000,    // After 50% discount = 250k
            ],
        ];

        foreach ($servicesPlans as $index => $plan) {
            $discountIQD = $plan['original'] - $plan['final'];
            $pricePerMonth = $plan['final'] / $plan['duration'];

            SubscriptionPlan::create([
                'name' => 'Company Services - ' . ucfirst(str_replace('_', ' ', $plan['label'])),
                'type' => 'services',
                'description' => 'Company service subscription with 50% discount applied',
                'duration_months' => $plan['duration'],
                'duration_label' => $plan['label'],
                'original_price_iqd' => $plan['original'],
                'discount_iqd' => $discountIQD,
                'final_price_iqd' => $plan['final'],
                'price_per_month_iqd' => round($pricePerMonth, 2),
                'total_amount_iqd' => $plan['final'],
                'original_price_usd' => round($plan['original'] / $exchangeRate, 2),
                'discount_usd' => round($discountIQD / $exchangeRate, 2),
                'final_price_usd' => round($plan['final'] / $exchangeRate, 2),
                'price_per_month_usd' => round($pricePerMonth / $exchangeRate, 2),
                'total_amount_usd' => round($plan['final'] / $exchangeRate, 2),
                'discount_percentage' => 50,
                'features' => ['Service visibility on platform', 'Company profile listing', 'Customer inquiry management', 'Review system'],
                'conditions' => ['50% discount applied', 'Auto-renewal available', 'Cancel anytime'],
                'active' => true,
                'is_featured' => $plan['duration'] == 12,
                'sort_order' => $index + 1,
            ]);
        }

        // Real Estate Office Plans
        $officePlans = [
            [
                'duration' => 6,
                'label' => '6_months',
                'price' => 575000,
            ],
            [
                'duration' => 12,
                'label' => '1_year',
                'price' => 1100000,
            ],
        ];

        foreach ($officePlans as $index => $plan) {
            $pricePerMonth = $plan['price'] / $plan['duration'];

            SubscriptionPlan::create([
                'name' => 'Real Estate Office - ' . ucfirst(str_replace('_', ' ', $plan['label'])),
                'type' => 'real_estate_office',
                'description' => 'Comprehensive subscription for real estate offices',
                'duration_months' => $plan['duration'],
                'duration_label' => $plan['label'],
                'original_price_iqd' => $plan['price'],
                'discount_iqd' => 0,
                'final_price_iqd' => $plan['price'],
                'price_per_month_iqd' => round($pricePerMonth, 2),
                'total_amount_iqd' => $plan['price'],
                'original_price_usd' => round($plan['price'] / $exchangeRate, 2),
                'discount_usd' => 0,
                'final_price_usd' => round($plan['price'] / $exchangeRate, 2),
                'price_per_month_usd' => round($pricePerMonth / $exchangeRate, 2),
                'total_amount_usd' => round($plan['price'] / $exchangeRate, 2),
                'discount_percentage' => 0,
                'features' => [
                    'Unlimited property listings',
                    'Multiple agent management',
                    'Office branding',
                    'Advanced analytics',
                    'Priority support',
                    'Featured office placement'
                ],
                'conditions' => ['Full office account access', 'Team collaboration tools', 'Custom branded portal'],
                'active' => true,
                'is_featured' => $plan['duration'] == 12,
                'sort_order' => $index + 1,
                'savings_vs_monthly_iqd' => $plan['duration'] == 12 ? 50000 : null,
                'savings_vs_monthly_usd' => $plan['duration'] == 12 ? round(50000 / $exchangeRate, 2) : null,
            ]);
        }

        // Agent Plans
        $agentPlans = [
            [
                'duration' => 1,
                'label' => '1_month',
                'price' => 35000,
                'discount' => 0,
                'properties' => 25,
            ],
            [
                'duration' => 3,
                'label' => '3_months',
                'price' => 75000,
                'discount' => 30000,
                'properties' => 75,
            ],
            [
                'duration' => 6,
                'label' => '6_months',
                'price' => 125000,
                'discount' => 85000,
                'properties' => 150,
            ],
            [
                'duration' => 12,
                'label' => '1_year',
                'price' => 250000,
                'discount' => 170000,
                'properties' => 300,
            ],
        ];

        foreach ($agentPlans as $index => $plan) {
            $originalPrice = $plan['price'] + $plan['discount'];
            $pricePerMonth = $plan['price'] / $plan['duration'];
            $pricePerProperty = $plan['price'] / $plan['properties'];
            $savingsPercentage = $plan['discount'] > 0 ? round(($plan['discount'] / $originalPrice) * 100, 2) : 0;

            SubscriptionPlan::create([
                'name' => 'Agent - ' . ucfirst(str_replace('_', ' ', $plan['label'])),
                'type' => 'agent',
                'description' => 'Individual agent subscription with property limits',
                'duration_months' => $plan['duration'],
                'duration_label' => $plan['label'],
                'original_price_iqd' => $originalPrice,
                'discount_iqd' => $plan['discount'],
                'final_price_iqd' => $plan['price'],
                'price_per_month_iqd' => round($pricePerMonth, 2),
                'total_amount_iqd' => $plan['price'],
                'original_price_usd' => round($originalPrice / $exchangeRate, 2),
                'discount_usd' => round($plan['discount'] / $exchangeRate, 2),
                'final_price_usd' => round($plan['price'] / $exchangeRate, 2),
                'price_per_month_usd' => round($pricePerMonth / $exchangeRate, 2),
                'total_amount_usd' => round($plan['price'] / $exchangeRate, 2),
                'discount_percentage' => $savingsPercentage,
                'savings_percentage' => $savingsPercentage,
                'max_properties' => $plan['properties'],
                'price_per_property_iqd' => round($pricePerProperty, 2),
                'price_per_property_usd' => round($pricePerProperty / $exchangeRate, 2),
                'features' => [
                    'Property listing management',
                    'Lead capture and tracking',
                    'Client CRM system',
                    'Analytics dashboard',
                    'Mobile app access',
                    'Marketing tools',
                    'Email & SMS notifications'
                ],
                'conditions' => [
                    'Maximum ' . $plan['properties'] . ' active properties',
                    'Can be standalone or part of office',
                    'Full platform access',
                    'Auto-renewal available'
                ],
                'note' => 'Best for ' . ($plan['duration'] == 1 ? 'new agents' : ($plan['duration'] == 3 ? 'growing agents' : ($plan['duration'] == 6 ? 'established agents' : 'high-volume agents'))),
                'active' => true,
                'is_featured' => $plan['duration'] == 12,
                'sort_order' => $index + 1,
            ]);
        }
    }
}