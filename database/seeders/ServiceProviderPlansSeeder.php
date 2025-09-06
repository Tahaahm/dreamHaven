<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceProviderPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'id' => 'basic_advertiser_plan',
                'name' => 'Basic Advertiser',
                'description' => 'For service companies wanting to reach real estate clients',
                'monthly_price' => 40.00,
                'annual_price' => 400.00,
                'advertisement_slots' => 1,
                'featured_placement_days' => 10,
                'banner' => 0,
                'features' => json_encode([
                    'Publish one service company profile',
                    'Basic company profile page',
                    'Contact form for leads',
                    '10 days featured placement on services page per month',
                    'Basic analytics on views and clicks',
                    'Company logo display'
                ]),
                'trial_days' => 7,
                'most_popular' => true,
                'overage_pricing' => json_encode([
                    'additional_advertisement_slots' => [
                        'price_per_slot' => 0,
                        'minimum_purchase' => 0,
                        'billing_type' => 'not_applicable'
                    ]
                ]),
                'active' => true,
                'sort_order' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 'premium_advertiser_plan',
                'name' => 'Premium Advertiser',
                'description' => 'For established service companies seeking maximum exposure',
                'monthly_price' => 80.00,
                'annual_price' => 800.00,
                'advertisement_slots' => 1,
                'featured_placement_days' => 20,
                'banner' => 1,
                'features' => json_encode([
                    'Publish one service company profile',
                    'Premium company profile with gallery',
                    'Advanced contact forms with custom fields',
                    '20 days featured placement on services page per month',
                    'Priority placement in search results',
                    'Custom banner upload capability',
                    'Detailed analytics and lead tracking',
                    'Email integration for lead notifications',
                    'Company video showcase',
                    'Social media links integration'
                ]),
                'trial_days' => 14,
                'most_popular' => false,
                'overage_pricing' => json_encode([
                    'additional_advertisement_slots' => [
                        'price_per_slot' => 0,
                        'minimum_purchase' => 0,
                        'billing_type' => 'not_applicable'
                    ]
                ]),
                'active' => true,
                'sort_order' => 2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('service_provider_plans')->insert($plans);
    }
}
