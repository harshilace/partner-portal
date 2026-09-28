<?php

namespace App\Domain\Dashboard;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Payments\Order;
use App\Domain\Referrals\ReferralCode;
use Illuminate\Auth\Access\AuthorizationException;

class SubPartnerDashboardQuery
{
    /**
     * Execute the query for the Sub-Partner dashboard.
     *
     * @return array{role: string, data: object, pending_confirmation: array<int, string>}
     *
     * @throws AuthorizationException
     */
    public function execute(User $user): array
    {
        if (! $user->isSubPartner()) {
            throw new AuthorizationException('User is not authorized to access the sub-partner dashboard.');
        }

        $partner = $user->partner();
        if (! $partner) {
            throw new AuthorizationException('No active sub-partner organization associated with user.');
        }

        $subPartnerId = $partner->id;

        $totalRevenue = Order::where('sub_partner_id', $subPartnerId)->sum('total_amount');
        $totalOrders = Order::where('sub_partner_id', $subPartnerId)->count();
        $totalLeads = Lead::where('sub_partner_id', $subPartnerId)->count();
        $convertedLeads = Lead::where('sub_partner_id', $subPartnerId)->where('status', 'converted')->count();
        $totalCustomers = Customer::where('current_sub_partner_id', $subPartnerId)->count();
        $referralCodes = ReferralCode::where('sub_partner_id', $subPartnerId)->where('is_active', true)->count();

        return [
            'role' => 'sub_partner',
            'data' => (object) [
                'kpis' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'total_leads' => $totalLeads,
                    'converted_leads' => $convertedLeads,
                    'total_customers' => $totalCustomers,
                    'referral_codes' => $referralCodes,
                ],
            ],
            'pending_confirmation' => [],
        ];
    }
}
