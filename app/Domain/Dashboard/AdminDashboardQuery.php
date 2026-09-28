<?php

namespace App\Domain\Dashboard;

use App\Domain\Authentication\User;
use App\Domain\Customers\Customer;
use App\Domain\Leads\Lead;
use App\Domain\Partners\Partner;
use App\Domain\Payments\Order;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Auth\Access\AuthorizationException;

class AdminDashboardQuery
{
    /**
     * Execute the query for the Admin dashboard.
     *
     * @return array{role: string, data: object, pending_confirmation: array<int, string>}
     *
     * @throws AuthorizationException
     */
    public function execute(User $user): array
    {
        if (! $user->isAdmin()) {
            throw new AuthorizationException('User is not authorized to access the admin dashboard.');
        }

        // KPI metrics
        $totalRevenue = Order::sum('total_amount');
        $totalOrders = Order::count();
        $totalPartners = Partner::count();
        $activePartners = Partner::where('status', 'active')->count();
        $totalLeads = Lead::count();
        $convertedLeads = Lead::where('status', 'converted')->count();
        $totalCustomers = Customer::count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        // Revenue by month (last 6 months)
        $revenueByMonth = Order::selectRaw('DATE_FORMAT(ordered_at, "%b %Y") as label, SUM(total_amount) as total')
            ->where('ordered_at', '>=', now()->subMonths(6))
            ->groupByRaw('DATE_FORMAT(ordered_at, "%Y-%m"), DATE_FORMAT(ordered_at, "%b %Y")')
            ->orderByRaw('DATE_FORMAT(ordered_at, "%Y-%m")')
            ->pluck('total', 'label');

        // Lead status breakdown
        $leadsByStatus = Lead::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Partner type breakdown
        $partnersByType = Partner::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Recent orders
        $recentOrders = Order::with('customer')
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
            'role' => 'admin',
            'data' => (object) [
                'kpis' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders' => $totalOrders,
                    'total_partners' => $totalPartners,
                    'active_partners' => $activePartners,
                    'total_leads' => $totalLeads,
                    'converted_leads' => $convertedLeads,
                    'total_customers' => $totalCustomers,
                    'active_subscriptions' => $activeSubscriptions,
                ],
                'revenue_by_month' => $revenueByMonth,
                'leads_by_status' => $leadsByStatus,
                'partners_by_type' => $partnersByType,
                'recent_orders' => $recentOrders,
            ],
            'pending_confirmation' => [],
        ];
    }
}
