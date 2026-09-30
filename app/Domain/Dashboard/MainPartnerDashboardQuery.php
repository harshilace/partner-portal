<?php

namespace App\Domain\Dashboard;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use Illuminate\Auth\Access\AuthorizationException;

class MainPartnerDashboardQuery
{
    /**
     * Execute the query for the Main Partner dashboard.
     *
     * @return array{role: string, data: object, pending_confirmation: array<int, string>}
     *
     * @throws AuthorizationException
     */
    public function execute(User $user): array
    {
        if (! $user->isMainPartner()) {
            throw new AuthorizationException('User is not authorized to access the main partner dashboard.');
        }

        $partner = $user->partner();
        if (! $partner) {
            throw new AuthorizationException('No active main partner organization associated with user.');
        }

        $mainPartnerId = $partner->id;

        $totalRevenue = Order::where('partner_id', $mainPartnerId)->sum('total_amount');
        $totalOrders = Order::where('partner_id', $mainPartnerId)->count();
        $subPartners = Partner::where('parent_partner_id', $mainPartnerId)->count();
        $totalLeads = Lead::where('partner_id', $mainPartnerId)->count();
        $convertedLeads = Lead::where('partner_id', $mainPartnerId)->where('status', 'converted')->count();
        $totalCustomers = Customer::where('current_partner_id', $mainPartnerId)->count();

        $revenueByMonth = Order::where('partner_id', $mainPartnerId)
            ->selectRaw('DATE_FORMAT(ordered_at, "%b %Y") as label, SUM(total_amount) as total')
            ->where('ordered_at', '>=', now()->subMonths(6))
            ->groupByRaw('DATE_FORMAT(ordered_at, "%Y-%m"), DATE_FORMAT(ordered_at, "%b %Y")')
            ->orderByRaw('DATE_FORMAT(ordered_at, "%Y-%m")')
            ->pluck('total', 'label');

        $leadsByStatus = Lead::where('partner_id', $mainPartnerId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentOrders = Order::with('customer')
            ->where('partner_id', $mainPartnerId)
            ->orderByDesc('ordered_at')
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'order_number' => $o->order_number,
                'customer_name' => $o->customer?->name ?? '—',
                'total_amount' => $o->total_amount,
                'payment_status' => $o->payment_status,
                'ordered_at' => $o->ordered_at?->toDateString(),
            ]);

        return [
            'role' => 'main_partner',
            'data' => (object) [
                'kpis' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'sub_partners' => $subPartners,
                    'total_leads' => $totalLeads,
                    'converted_leads' => $convertedLeads,
                    'total_customers' => $totalCustomers,
                ],
                'revenue_by_month' => $revenueByMonth,
                'leads_by_status' => $leadsByStatus,
                'recent_orders' => $recentOrders,
            ],
            'pending_confirmation' => [],
        ];
    }
}
