<?php

namespace Database\Seeders;

use App\Domain\Authentication\Enums\Role;
use App\Domain\Customers\Customer;
use App\Domain\Customers\CustomerPartnerAttribution;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Payments\OrderItem;
use App\Domain\Products\Product;
use App\Domain\Products\ProductPlan;
use App\Domain\Referrals\ReferralCode;
use App\Domain\Renewals\Renewal;
use App\Domain\Subscriptions\AutoDebitMandate;
use App\Domain\Subscriptions\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds with comprehensive realistic records for all screens.
     */
    public function run(): void
    {
        $admin = User::where('role', Role::ADMIN->value)->first();
        $mainPartner = Partner::where('partner_code', 'PARTNER-MAIN')->first();
        $subPartner = Partner::where('partner_code', 'PARTNER-SUB')->first();
        $mainUser = User::where('email', 'partner@example.com')->first();
        $subUser = User::where('email', 'subpartner@example.com')->first();

        // -------------------------------------------------------------
        // 1. ADDITIONAL PARTNERS & SUB-PARTNERS
        // -------------------------------------------------------------
        $apexPartner = Partner::firstOrCreate(
            ['partner_code' => 'PARTNER-APEX'],
            [
                'name' => 'Apex Solutions Group',
                'type' => 'main',
                'status' => 'active',
                'created_at' => Carbon::now()->subMonths(6),
            ]
        );

        $apexSubEast = Partner::firstOrCreate(
            ['partner_code' => 'SUB-APEX-EAST'],
            [
                'name' => 'Apex East Regional Hub',
                'type' => 'sub',
                'parent_partner_id' => $apexPartner->id,
                'status' => 'active',
                'created_at' => Carbon::now()->subMonths(5),
            ]
        );

        $apexSubWest = Partner::firstOrCreate(
            ['partner_code' => 'SUB-APEX-WEST'],
            [
                'name' => 'Apex West Distribution',
                'type' => 'sub',
                'parent_partner_id' => $apexPartner->id,
                'status' => 'active',
                'created_at' => Carbon::now()->subMonths(4),
            ]
        );

        $nexusPartner = Partner::firstOrCreate(
            ['partner_code' => 'PARTNER-NEXUS'],
            [
                'name' => 'Nexus Global Enterprise',
                'type' => 'main',
                'status' => 'active',
                'created_at' => Carbon::now()->subMonths(3),
            ]
        );

        $nexusSubTech = Partner::firstOrCreate(
            ['partner_code' => 'SUB-NEXUS-TECH'],
            [
                'name' => 'Nexus Tech Services',
                'type' => 'sub',
                'parent_partner_id' => $nexusPartner->id,
                'status' => 'active',
                'created_at' => Carbon::now()->subMonths(2),
            ]
        );

        $summitPartner = Partner::firstOrCreate(
            ['partner_code' => 'PARTNER-SUMMIT'],
            [
                'name' => 'Summit Digital Partners',
                'type' => 'main',
                'status' => 'inactive',
                'created_at' => Carbon::now()->subMonths(8),
            ]
        );

        // -------------------------------------------------------------
        // 2. REFERRAL CODES
        // -------------------------------------------------------------
        $referralData = [
            ['code' => 'MAIN-PARTNER-2026', 'partner_id' => $mainPartner->id, 'sub_partner_id' => null, 'is_active' => true],
            ['code' => 'SUB-PARTNER-REF', 'partner_id' => $mainPartner->id, 'sub_partner_id' => $subPartner->id, 'is_active' => true],
            ['code' => 'APEX-GLOBAL-PROMO', 'partner_id' => $apexPartner->id, 'sub_partner_id' => null, 'is_active' => true],
            ['code' => 'APEX-EAST-DIRECT', 'partner_id' => $apexPartner->id, 'sub_partner_id' => $apexSubEast->id, 'is_active' => true],
            ['code' => 'APEX-WEST-RETAIL', 'partner_id' => $apexPartner->id, 'sub_partner_id' => $apexSubWest->id, 'is_active' => true],
            ['code' => 'NEXUS-VIP-ACCESS', 'partner_id' => $nexusPartner->id, 'sub_partner_id' => null, 'is_active' => true],
            ['code' => 'NEXUS-TECH-SPECIAL', 'partner_id' => $nexusPartner->id, 'sub_partner_id' => $nexusSubTech->id, 'is_active' => true],
            ['code' => 'SUMMIT-EXPIRED-CODE', 'partner_id' => $summitPartner->id, 'sub_partner_id' => null, 'is_active' => false],
        ];

        $refModels = [];
        foreach ($referralData as $ref) {
            $refModels[$ref['code']] = ReferralCode::firstOrCreate(
                ['code' => $ref['code']],
                $ref
            );
        }

        // -------------------------------------------------------------
        // 3. PRODUCTS & PRODUCT PLANS
        // -------------------------------------------------------------
        $productsData = [
            [
                'code' => 'PRD-ERP',
                'name' => 'Enterprise Cloud ERP',
                'description' => 'Integrated cloud ERP suite for real-time finance, supply chain, inventory, and operations.',
                'is_active' => true,
                'plans' => [
                    ['code' => 'PLAN-ERP-STD', 'name' => 'Standard Business', 'price' => 99.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                    ['code' => 'PLAN-ERP-PRO', 'name' => 'Professional Scale', 'price' => 249.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                    ['code' => 'PLAN-ERP-ENT', 'name' => 'Enterprise Annual', 'price' => 2490.00, 'billing_cycle' => 'yearly', 'duration_days' => 365, 'is_active' => true],
                ],
            ],
            [
                'code' => 'PRD-SEC',
                'name' => 'CyberShield Zero-Trust',
                'description' => 'Next-gen enterprise endpoint security, automated threat detection, and continuous compliance telemetry.',
                'is_active' => true,
                'plans' => [
                    ['code' => 'PLAN-SEC-STARTER', 'name' => 'Endpoint Guard', 'price' => 49.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                    ['code' => 'PLAN-SEC-TEAM', 'name' => 'Team Shield (25 Seats)', 'price' => 199.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                    ['code' => 'PLAN-SEC-CORP', 'name' => 'Corporate Total Defense', 'price' => 1890.00, 'billing_cycle' => 'yearly', 'duration_days' => 365, 'is_active' => true],
                ],
            ],
            [
                'code' => 'PRD-CRM',
                'name' => 'CustomerPulse CRM',
                'description' => 'Omnichannel sales engagement, pipeline forecasting, and automated lead nurturing suite.',
                'is_active' => true,
                'plans' => [
                    ['code' => 'PLAN-CRM-BASIC', 'name' => 'Sales Starter', 'price' => 29.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                    ['code' => 'PLAN-CRM-GROWTH', 'name' => 'Growth Acceleration', 'price' => 79.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                ],
            ],
            [
                'code' => 'PRD-AI',
                'name' => 'Cognitive Analytics Engine',
                'description' => 'AI-driven predictive business intelligence, automated anomaly detection, and custom model deployments.',
                'is_active' => true,
                'plans' => [
                    ['code' => 'PLAN-AI-PRO', 'name' => 'Analytics Pro', 'price' => 149.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => true],
                    ['code' => 'PLAN-AI-ENT', 'name' => 'Dedicated ML Pipeline', 'price' => 3999.00, 'billing_cycle' => 'yearly', 'duration_days' => 365, 'is_active' => true],
                ],
            ],
            [
                'code' => 'PRD-LEGACY',
                'name' => 'Legacy Connector Utility',
                'description' => 'Archival database connector module for legacy on-premise systems.',
                'is_active' => false,
                'plans' => [
                    ['code' => 'PLAN-LEGACY-BASE', 'name' => 'Legacy Maintenance', 'price' => 15.00, 'billing_cycle' => 'monthly', 'duration_days' => 30, 'is_active' => false],
                ],
            ],
        ];

        $productModels = [];
        $planModels = [];

        foreach ($productsData as $pData) {
            $plans = $pData['plans'];
            unset($pData['plans']);

            $product = Product::firstOrCreate(
                ['code' => $pData['code']],
                $pData
            );
            $productModels[$product->code] = $product;

            foreach ($plans as $plan) {
                $plan['product_id'] = $product->id;
                $pModel = ProductPlan::firstOrCreate(
                    ['code' => $plan['code']],
                    $plan
                );
                $planModels[$plan['code']] = $pModel;
            }
        }

        // -------------------------------------------------------------
        // 4. CUSTOMERS & ATTRIBUTIONS
        // -------------------------------------------------------------
        $customersData = [
            [
                'customer_code' => 'CUST-8A9B0C1D',
                'name' => 'Acme Global Logistics',
                'email' => 'contact@acmeglobal.com',
                'mobile' => '9876543211',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'status' => 'active',
                'ref_code' => 'MAIN-PARTNER-2026',
            ],
            [
                'customer_code' => 'CUST-7E6F5D4C',
                'name' => 'TechWave Software Labs',
                'email' => 'finance@techwave.io',
                'mobile' => '9876543212',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'status' => 'active',
                'ref_code' => 'SUB-PARTNER-REF',
            ],
            [
                'customer_code' => 'CUST-3B2A1F0E',
                'name' => 'Apex Retail Solutions',
                'email' => 'procurement@apexretail.com',
                'mobile' => '9876543213',
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubEast->id,
                'status' => 'active',
                'ref_code' => 'APEX-EAST-DIRECT',
            ],
            [
                'customer_code' => 'CUST-5D4C3B2A',
                'name' => 'Nexus Financial Partners',
                'email' => 'billing@nexusfp.org',
                'mobile' => '9876543214',
                'partner_id' => $nexusPartner->id,
                'sub_partner_id' => $nexusSubTech->id,
                'status' => 'active',
                'ref_code' => 'NEXUS-TECH-SPECIAL',
            ],
            [
                'customer_code' => 'CUST-9C8B7A6F',
                'name' => 'Horizon Media Studios',
                'email' => 'admin@horizonmedia.tv',
                'mobile' => '9876543215',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'status' => 'active',
                'ref_code' => 'SUB-PARTNER-REF',
            ],
            [
                'customer_code' => 'CUST-1F2E3D4C',
                'name' => 'Summit Engineering Works',
                'email' => 'ops@summiteng.net',
                'mobile' => '9876543216',
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubWest->id,
                'status' => 'active',
                'ref_code' => 'APEX-WEST-RETAIL',
            ],
            [
                'customer_code' => 'CUST-6A5B4C3D',
                'name' => 'Pinnacle BioMed Labs',
                'email' => 'info@pinnaclebiomed.com',
                'mobile' => '9876543217',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'status' => 'active',
                'ref_code' => 'MAIN-PARTNER-2026',
            ],
            [
                'customer_code' => 'CUST-0D9C8B7A',
                'name' => 'Starlight Hospitality Group',
                'email' => 'accounts@starlighthotels.com',
                'mobile' => '9876543218',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'status' => 'inactive',
                'ref_code' => 'MAIN-PARTNER-2026',
            ],
        ];

        // Also assign existing customer Viren to PARTNER-MAIN so it shows in partner screens
        $existingCustomer = Customer::where('email', 'viren@gmail.com')->first();
        if ($existingCustomer && ! $existingCustomer->current_partner_id) {
            $existingCustomer->update([
                'current_partner_id' => $mainPartner->id,
                'current_sub_partner_id' => $subPartner->id,
            ]);
        }

        $custModels = [];
        foreach ($customersData as $cData) {
            $refCodeStr = $cData['ref_code'];
            unset($cData['ref_code']);

            $cust = Customer::firstOrCreate(
                ['customer_code' => $cData['customer_code']],
                [
                    'name' => $cData['name'],
                    'email' => $cData['email'],
                    'email_normalized' => strtolower($cData['email']),
                    'mobile' => $cData['mobile'],
                    'mobile_normalized' => preg_replace('/[^0-9]/', '', $cData['mobile']),
                    'current_partner_id' => $cData['partner_id'],
                    'current_sub_partner_id' => $cData['sub_partner_id'],
                    'status' => $cData['status'],
                    'created_at' => Carbon::now()->subMonths(rand(1, 6)),
                ]
            );
            $custModels[$cust->customer_code] = $cust;

            $refObj = $refModels[$refCodeStr] ?? null;
            CustomerPartnerAttribution::firstOrCreate(
                ['customer_id' => $cust->id, 'partner_id' => $cData['partner_id'], 'sub_partner_id' => $cData['sub_partner_id']],
                [
                    'referral_code_id' => $refObj?->id,
                    'starts_at' => Carbon::now()->subMonths(6),
                    'changed_by_user_id' => $admin?->id,
                ]
            );
        }

        // -------------------------------------------------------------
        // 5. LEADS
        // -------------------------------------------------------------
        $leadsData = [
            [
                'name' => 'Acme Logistics Expansion',
                'email' => 'expansion@acmeglobal.com',
                'mobile' => '9876543211',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'customer_id' => $custModels['CUST-8A9B0C1D']->id,
                'status' => 'converted',
                'notes' => 'Successfully converted to ERP Enterprise Annual.',
                'converted_at' => Carbon::now()->subMonths(2),
            ],
            [
                'name' => 'TechWave Cloud Upgrade',
                'email' => 'upgrade@techwave.io',
                'mobile' => '9876543212',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'customer_id' => $custModels['CUST-7E6F5D4C']->id,
                'status' => 'converted',
                'notes' => 'Subscribed to CyberShield Team Shield protection.',
                'converted_at' => Carbon::now()->subMonth(),
            ],
            [
                'name' => 'BioMed Compliance Security',
                'email' => 'compliance@pinnaclebiomed.com',
                'mobile' => '9876543217',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'customer_id' => $custModels['CUST-6A5B4C3D']->id,
                'status' => 'proposal_sent',
                'notes' => 'Proposal for CyberShield Corporate Defense under review with board.',
                'converted_at' => null,
            ],
            [
                'name' => 'Quantum Robotics AI Pilot',
                'email' => 'director@quantumrobotics.io',
                'mobile' => '9876543220',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'customer_id' => null,
                'status' => 'qualified',
                'notes' => 'Architectural validation scheduled for Cognitive Analytics Engine.',
                'converted_at' => null,
            ],
            [
                'name' => 'Starlight Booking Engine',
                'email' => 'tech@starlighthotels.com',
                'mobile' => '9876543218',
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubEast->id,
                'customer_id' => $custModels['CUST-0D9C8B7A']->id,
                'status' => 'contacted',
                'notes' => 'Followed up regarding CRM integrations with existing POS.',
                'converted_at' => null,
            ],
            [
                'name' => 'Solaris Clean Energy',
                'email' => 'partnerships@solarisenergy.com',
                'mobile' => '9876543225',
                'partner_id' => $nexusPartner->id,
                'sub_partner_id' => $nexusSubTech->id,
                'customer_id' => null,
                'status' => 'new',
                'notes' => 'Inbound referral inquiry for ERP Standard deployment.',
                'converted_at' => null,
            ],
            [
                'name' => 'Vintage Motors Archive',
                'email' => 'inquiry@vintagemotors.net',
                'mobile' => '9876543229',
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'customer_id' => null,
                'status' => 'lost',
                'notes' => 'Customer deferred project to next fiscal year.',
                'converted_at' => null,
            ],
        ];

        $leadModels = [];
        foreach ($leadsData as $lData) {
            $lead = Lead::firstOrCreate(
                ['email' => $lData['email']],
                [
                    'name' => $lData['name'],
                    'email_normalized' => strtolower($lData['email']),
                    'mobile' => $lData['mobile'],
                    'mobile_normalized' => preg_replace('/[^0-9]/', '', $lData['mobile']),
                    'partner_id' => $lData['partner_id'],
                    'sub_partner_id' => $lData['sub_partner_id'],
                    'customer_id' => $lData['customer_id'],
                    'status' => $lData['status'],
                    'notes' => $lData['notes'],
                    'converted_at' => $lData['converted_at'],
                    'created_at' => Carbon::now()->subWeeks(rand(1, 8)),
                ]
            );
            $leadModels[] = $lead;
        }

        // -------------------------------------------------------------
        // 6. ORDERS & ORDER ITEMS
        // -------------------------------------------------------------
        $ordersData = [
            [
                'order_number' => 'ORD-2026-0001',
                'customer' => $custModels['CUST-8A9B0C1D'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'total_amount' => 2490.00,
                'status' => 'completed',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subMonths(2),
                'items' => [
                    ['plan' => $planModels['PLAN-ERP-ENT'], 'qty' => 1, 'price' => 2490.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0002',
                'customer' => $custModels['CUST-7E6F5D4C'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'total_amount' => 199.00,
                'status' => 'completed',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subWeeks(3),
                'items' => [
                    ['plan' => $planModels['PLAN-SEC-TEAM'], 'qty' => 1, 'price' => 199.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0003',
                'customer' => $custModels['CUST-3B2A1F0E'],
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubEast->id,
                'total_amount' => 249.00,
                'status' => 'completed',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subWeeks(2),
                'items' => [
                    ['plan' => $planModels['PLAN-ERP-PRO'], 'qty' => 1, 'price' => 249.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0004',
                'customer' => $custModels['CUST-5D4C3B2A'],
                'partner_id' => $nexusPartner->id,
                'sub_partner_id' => $nexusSubTech->id,
                'total_amount' => 3999.00,
                'status' => 'completed',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subDays(10),
                'items' => [
                    ['plan' => $planModels['PLAN-AI-ENT'], 'qty' => 1, 'price' => 3999.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0005',
                'customer' => $custModels['CUST-9C8B7A6F'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'total_amount' => 99.00,
                'status' => 'completed',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subDays(4),
                'items' => [
                    ['plan' => $planModels['PLAN-ERP-STD'], 'qty' => 1, 'price' => 99.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0006',
                'customer' => $custModels['CUST-6A5B4C3D'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'total_amount' => 1890.00,
                'status' => 'pending',
                'payment_status' => 'pending',
                'ordered_at' => Carbon::now()->subDay(),
                'items' => [
                    ['plan' => $planModels['PLAN-SEC-CORP'], 'qty' => 1, 'price' => 1890.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0007',
                'customer' => $custModels['CUST-0D9C8B7A'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'total_amount' => 29.00,
                'status' => 'cancelled',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subMonths(4),
                'items' => [
                    ['plan' => $planModels['PLAN-CRM-BASIC'], 'qty' => 1, 'price' => 29.00],
                ],
            ],
            [
                'order_number' => 'ORD-2026-0008',
                'customer' => $custModels['CUST-1F2E3D4C'],
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubWest->id,
                'total_amount' => 49.00,
                'status' => 'completed',
                'payment_status' => 'paid',
                'ordered_at' => Carbon::now()->subMonths(3),
                'items' => [
                    ['plan' => $planModels['PLAN-SEC-STARTER'], 'qty' => 1, 'price' => 49.00],
                ],
            ],
        ];

        $orderModels = [];
        foreach ($ordersData as $oData) {
            $items = $oData['items'];
            $cust = $oData['customer'];
            unset($oData['items'], $oData['customer']);

            $oData['customer_id'] = $cust->id;

            $order = Order::firstOrCreate(
                ['order_number' => $oData['order_number']],
                $oData
            );
            $orderModels[$order->order_number] = $order;

            foreach ($items as $item) {
                OrderItem::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $item['plan']->product_id,
                        'product_plan_id' => $item['plan']->id,
                    ],
                    [
                        'unit_price' => $item['price'],
                        'quantity' => $item['qty'],
                        'total_price' => $item['price'] * $item['qty'],
                    ]
                );
            }
        }

        // -------------------------------------------------------------
        // 7. SUBSCRIPTIONS
        // -------------------------------------------------------------
        $subscriptionsData = [
            [
                'subscription_number' => 'SUB-2026-1001',
                'customer' => $custModels['CUST-8A9B0C1D'],
                'order' => $orderModels['ORD-2026-0001'],
                'plan' => $planModels['PLAN-ERP-ENT'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'status' => 'active',
                'auto_debit_enabled' => true,
                'starts_at' => Carbon::now()->subMonths(2),
                'expires_at' => Carbon::now()->addMonths(10),
                'cancelled_at' => null,
            ],
            [
                'subscription_number' => 'SUB-2026-1002',
                'customer' => $custModels['CUST-7E6F5D4C'],
                'order' => $orderModels['ORD-2026-0002'],
                'plan' => $planModels['PLAN-SEC-TEAM'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'status' => 'active',
                'auto_debit_enabled' => true,
                'starts_at' => Carbon::now()->subWeeks(3),
                'expires_at' => Carbon::now()->addDays(9),
                'cancelled_at' => null,
            ],
            [
                'subscription_number' => 'SUB-2026-1003',
                'customer' => $custModels['CUST-3B2A1F0E'],
                'order' => $orderModels['ORD-2026-0003'],
                'plan' => $planModels['PLAN-ERP-PRO'],
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubEast->id,
                'status' => 'active',
                'auto_debit_enabled' => true,
                'starts_at' => Carbon::now()->subWeeks(2),
                'expires_at' => Carbon::now()->addDays(14),
                'cancelled_at' => null,
            ],
            [
                'subscription_number' => 'SUB-2026-1004',
                'customer' => $custModels['CUST-5D4C3B2A'],
                'order' => $orderModels['ORD-2026-0004'],
                'plan' => $planModels['PLAN-AI-ENT'],
                'partner_id' => $nexusPartner->id,
                'sub_partner_id' => $nexusSubTech->id,
                'status' => 'active',
                'auto_debit_enabled' => false,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addMonths(11),
                'cancelled_at' => null,
            ],
            [
                'subscription_number' => 'SUB-2026-1005',
                'customer' => $custModels['CUST-9C8B7A6F'],
                'order' => $orderModels['ORD-2026-0005'],
                'plan' => $planModels['PLAN-ERP-STD'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'status' => 'active',
                'auto_debit_enabled' => true,
                'starts_at' => Carbon::now()->subDays(4),
                'expires_at' => Carbon::now()->addDays(26),
                'cancelled_at' => null,
            ],
            [
                'subscription_number' => 'SUB-2026-0999',
                'customer' => $custModels['CUST-0D9C8B7A'],
                'order' => $orderModels['ORD-2026-0007'],
                'plan' => $planModels['PLAN-CRM-BASIC'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'status' => 'cancelled',
                'auto_debit_enabled' => false,
                'starts_at' => Carbon::now()->subMonths(4),
                'expires_at' => Carbon::now()->subMonths(2),
                'cancelled_at' => Carbon::now()->subMonths(2),
            ],
            [
                'subscription_number' => 'SUB-2026-0888',
                'customer' => $custModels['CUST-1F2E3D4C'],
                'order' => $orderModels['ORD-2026-0008'],
                'plan' => $planModels['PLAN-SEC-STARTER'],
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubWest->id,
                'status' => 'expired',
                'auto_debit_enabled' => false,
                'starts_at' => Carbon::now()->subMonths(3),
                'expires_at' => Carbon::now()->subMonth(),
                'cancelled_at' => null,
            ],
        ];

        $subModels = [];
        foreach ($subscriptionsData as $sData) {
            $cust = $sData['customer'];
            $plan = $sData['plan'];
            $order = $sData['order'];
            unset($sData['customer'], $sData['plan'], $sData['order']);

            $sData['customer_id'] = $cust->id;
            $sData['product_id'] = $plan->product_id;
            $sData['product_plan_id'] = $plan->id;
            $sData['order_id'] = $order?->id;

            $sub = Subscription::firstOrCreate(
                ['subscription_number' => $sData['subscription_number']],
                $sData
            );
            $subModels[$sub->subscription_number] = $sub;
        }

        // -------------------------------------------------------------
        // 8. AUTO-DEBIT MANDATES
        // -------------------------------------------------------------
        $mandatesData = [
            [
                'subscription' => $subModels['SUB-2026-1001'],
                'customer' => $custModels['CUST-8A9B0C1D'],
                'mandate_reference' => 'MNDT-ACME-8801',
                'status' => 'active',
            ],
            [
                'subscription' => $subModels['SUB-2026-1002'],
                'customer' => $custModels['CUST-7E6F5D4C'],
                'mandate_reference' => 'MNDT-TECH-4402',
                'status' => 'active',
            ],
            [
                'subscription' => $subModels['SUB-2026-1003'],
                'customer' => $custModels['CUST-3B2A1F0E'],
                'mandate_reference' => 'MNDT-APEX-1203',
                'status' => 'active',
            ],
            [
                'subscription' => $subModels['SUB-2026-1005'],
                'customer' => $custModels['CUST-9C8B7A6F'],
                'mandate_reference' => 'MNDT-HORZ-9904',
                'status' => 'active',
            ],
            [
                'subscription' => $subModels['SUB-2026-0999'],
                'customer' => $custModels['CUST-0D9C8B7A'],
                'mandate_reference' => 'MNDT-STAR-0005',
                'status' => 'stopped',
                'stopped_by_user_id' => $admin?->id,
                'stopped_at' => Carbon::now()->subMonths(2),
                'stop_reason' => 'Customer requested cancellation of recurring automated billing.',
            ],
        ];

        foreach ($mandatesData as $mData) {
            $sub = $mData['subscription'];
            $cust = $mData['customer'];
            unset($mData['subscription'], $mData['customer']);

            $mData['subscription_id'] = $sub->id;
            $mData['customer_id'] = $cust->id;

            AutoDebitMandate::firstOrCreate(
                ['mandate_reference' => $mData['mandate_reference']],
                $mData
            );
        }

        // -------------------------------------------------------------
        // 9. RENEWALS
        // -------------------------------------------------------------
        $renewalsData = [
            [
                'subscription' => $subModels['SUB-2026-1002'],
                'customer' => $custModels['CUST-7E6F5D4C'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'due_date' => Carbon::now()->addDays(9)->toDateString(),
                'status' => 'pending',
                'reminder_30d_sent_at' => Carbon::now()->subDays(21),
                'reminder_15d_sent_at' => Carbon::now()->subDays(6),
                'reminder_7d_sent_at' => null,
            ],
            [
                'subscription' => $subModels['SUB-2026-1003'],
                'customer' => $custModels['CUST-3B2A1F0E'],
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubEast->id,
                'due_date' => Carbon::now()->addDays(14)->toDateString(),
                'status' => 'pending',
                'reminder_30d_sent_at' => Carbon::now()->subDays(16),
                'reminder_15d_sent_at' => null,
                'reminder_7d_sent_at' => null,
            ],
            [
                'subscription' => $subModels['SUB-2026-1005'],
                'customer' => $custModels['CUST-9C8B7A6F'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => $subPartner->id,
                'due_date' => Carbon::now()->addDays(26)->toDateString(),
                'status' => 'pending',
                'reminder_30d_sent_at' => null,
                'reminder_15d_sent_at' => null,
                'reminder_7d_sent_at' => null,
            ],
            [
                'subscription' => $subModels['SUB-2026-1001'],
                'customer' => $custModels['CUST-8A9B0C1D'],
                'partner_id' => $mainPartner->id,
                'sub_partner_id' => null,
                'due_date' => Carbon::now()->addMonths(10)->toDateString(),
                'status' => 'pending',
                'reminder_30d_sent_at' => null,
                'reminder_15d_sent_at' => null,
                'reminder_7d_sent_at' => null,
            ],
            [
                'subscription' => $subModels['SUB-2026-0888'],
                'customer' => $custModels['CUST-1F2E3D4C'],
                'partner_id' => $apexPartner->id,
                'sub_partner_id' => $apexSubWest->id,
                'due_date' => Carbon::now()->subMonth()->toDateString(),
                'status' => 'expired',
                'reminder_30d_sent_at' => Carbon::now()->subMonths(2),
                'reminder_15d_sent_at' => Carbon::now()->subDays(45),
                'reminder_7d_sent_at' => Carbon::now()->subDays(37),
                'reminder_1d_sent_at' => Carbon::now()->subDays(31),
            ],
        ];

        foreach ($renewalsData as $rData) {
            $sub = $rData['subscription'];
            $cust = $rData['customer'];
            unset($rData['subscription'], $rData['customer']);

            $rData['subscription_id'] = $sub->id;
            $rData['customer_id'] = $cust->id;

            Renewal::firstOrCreate(
                [
                    'subscription_id' => $sub->id,
                    'customer_id' => $cust->id,
                ],
                $rData
            );
        }

        // -------------------------------------------------------------
        // 10. IN-APP NOTIFICATIONS
        // -------------------------------------------------------------
        $recipients = array_filter([$admin, $mainUser, $subUser]);

        $notificationItems = [
            [
                'type' => 'App\\Notifications\\SaleCompletedNotification',
                'data' => [
                    'type' => 'sale_completed',
                    'title' => 'New Order Completed',
                    'message' => 'Order ORD-2026-0002 ($199.00) completed for TechWave Software Labs.',
                    'order_number' => 'ORD-2026-0002',
                    'amount' => '$199.00',
                ],
                'read_at' => null,
            ],
            [
                'type' => 'App\\Notifications\\RenewalDueNotification',
                'data' => [
                    'type' => 'renewal_due',
                    'title' => 'Upcoming Renewal Alert',
                    'message' => 'Subscription SUB-2026-1002 is due for renewal in 9 days.',
                    'subscription_number' => 'SUB-2026-1002',
                    'due_date' => Carbon::now()->addDays(9)->toFormattedDateString(),
                ],
                'read_at' => null,
            ],
            [
                'type' => 'App\\Notifications\\NewLeadNotification',
                'data' => [
                    'type' => 'new_lead',
                    'title' => 'New Qualified Lead',
                    'message' => 'Lead "Quantum Robotics AI Pilot" has been qualified and attributed to your portal.',
                    'lead_name' => 'Quantum Robotics AI Pilot',
                ],
                'read_at' => null,
            ],
            [
                'type' => 'App\\Notifications\\NewCustomerNotification',
                'data' => [
                    'type' => 'new_customer',
                    'title' => 'New Customer Attributed',
                    'message' => 'Customer Acme Global Logistics (CUST-8A9B0C1D) joined via referral code MAIN-PARTNER-2026.',
                    'customer_code' => 'CUST-8A9B0C1D',
                ],
                'read_at' => Carbon::now()->subDays(3),
            ],
        ];

        foreach ($recipients as $recipient) {
            foreach ($notificationItems as $notif) {
                $exists = DB::table('notifications')
                    ->where('notifiable_type', get_class($recipient))
                    ->where('notifiable_id', $recipient->id)
                    ->where('data->title', $notif['data']['title'])
                    ->exists();

                if (! $exists) {
                    DB::table('notifications')->insert([
                        'id' => (string) Str::uuid(),
                        'type' => $notif['type'],
                        'notifiable_type' => get_class($recipient),
                        'notifiable_id' => $recipient->id,
                        'data' => json_encode($notif['data']),
                        'read_at' => $notif['read_at'],
                        'created_at' => Carbon::now()->subHours(rand(1, 48)),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }
}
