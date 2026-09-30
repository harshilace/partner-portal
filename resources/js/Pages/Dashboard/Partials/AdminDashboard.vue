<script setup>
import { ref, computed, onMounted } from 'vue';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';
import {
    CurrencyDollarIcon,
    ShoppingCartIcon,
    UsersIcon,
    UserGroupIcon,
    UserPlusIcon,
    ArrowPathIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ChartBarIcon,
    BuildingOffice2Icon,
} from '@heroicons/vue/24/outline';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    data: { type: Object, default: () => ({}) },
    pending_confirmation: { type: Array, default: () => [] },
});

const kpis  = computed(() => props.data?.kpis ?? {});
const recentOrders = computed(() => props.data?.recent_orders ?? []);

// ── Tabs ─────────────────────────────────────────────────
const tabs = ['Overview', 'Revenue', 'Partners & Leads'];
const activeTab = ref('Overview');

// ── Charts ───────────────────────────────────────────────
const revenueLabels = computed(() => Object.keys(props.data?.revenue_by_month ?? {}));
const revenueValues = computed(() => Object.values(props.data?.revenue_by_month ?? {}).map(Number));

const revenueBarData = computed(() => ({
    labels: revenueLabels.value.length ? revenueLabels.value : ['No data'],
    datasets: [{
        label: 'Revenue ($)',
        data: revenueValues.value.length ? revenueValues.value : [0],
        backgroundColor: 'rgba(59,130,246,0.15)',
        borderColor: '#3b82f6',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
    }],
}));

const revenueLineData = computed(() => ({
    labels: revenueLabels.value.length ? revenueLabels.value : ['No data'],
    datasets: [{
        label: 'Revenue ($)',
        data: revenueValues.value.length ? revenueValues.value : [0],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#3b82f6',
        pointRadius: 4,
        pointHoverRadius: 6,
    }],
}));

const leadStatusData = computed(() => {
    const d = props.data?.leads_by_status ?? {};
    return {
        labels: Object.keys(d).map((k) => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{
            data: Object.values(d),
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#6b7280'],
            borderColor: '#fff',
            borderWidth: 2,
            hoverOffset: 4,
        }],
    };
});

const partnerTypeData = computed(() => {
    const d = props.data?.partners_by_type ?? {};
    return {
        labels: Object.keys(d).map((k) => k.charAt(0).toUpperCase() + k.slice(1) + ' Partner'),
        datasets: [{
            data: Object.values(d),
            backgroundColor: ['#3b82f6', '#8b5cf6'],
            borderColor: '#fff',
            borderWidth: 2,
            hoverOffset: 4,
        }],
    };
});

const chartOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 10,
            cornerRadius: 8,
            callbacks: {
                label: (ctx) => ` $${Number(ctx.raw).toLocaleString()}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', font: { size: 11 } },
            border: { display: false },
        },
        y: {
            grid: { color: 'rgba(148,163,184,0.1)' },
            ticks: {
                color: '#94a3b8',
                font: { size: 11 },
                callback: (v) => '$' + Number(v).toLocaleString(),
            },
            border: { display: false },
        },
    },
};

const doughnutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            position: 'right',
            labels: { color: '#475569', font: { size: 11 }, padding: 14, boxWidth: 12, borderRadius: 4 },
        },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 10,
            cornerRadius: 8,
        },
    },
};

// ── Helpers ───────────────────────────────────────────────
const fmt    = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n ?? 0);
const fmtNum = (n) => Number(n ?? 0).toLocaleString();
const fmtDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }); }
    catch { return d; }
};

const payClass = (s) => ({
    paid:    'bg-emerald-50 text-emerald-700',
    pending: 'bg-amber-50 text-amber-700',
    failed:  'bg-red-50 text-red-600',
})[s] ?? 'bg-gray-100 text-gray-600';

// KPI card definitions
const kpiCards = computed(() => [
    {
        label:  'Total Revenue',
        value:  fmt(kpis.value.total_revenue),
        icon:   CurrencyDollarIcon,
        color:  'text-emerald-600',
        bg:     'bg-emerald-100',
        change: '+12.4%',
        up:     true,
    },
    {
        label:  'Total Orders',
        value:  fmtNum(kpis.value.total_orders),
        icon:   ShoppingCartIcon,
        color:  'text-blue-600',
        bg:     'bg-blue-100',
        change: '+8.1%',
        up:     true,
    },
    {
        label:  'Active Partners',
        value:  `${kpis.value.active_partners ?? 0} / ${kpis.value.total_partners ?? 0}`,
        icon:   BuildingOffice2Icon,
        color:  'text-indigo-600',
        bg:     'bg-indigo-100',
        change: '+2',
        up:     true,
    },
    {
        label:  'Customers',
        value:  fmtNum(kpis.value.total_customers),
        icon:   UserGroupIcon,
        color:  'text-purple-600',
        bg:     'bg-purple-100',
        change: '+5.3%',
        up:     true,
    },
    {
        label:  'Active Subscriptions',
        value:  fmtNum(kpis.value.active_subscriptions),
        icon:   ArrowPathIcon,
        color:  'text-sky-600',
        bg:     'bg-sky-100',
        change: 'Recurring',
        up:     true,
    },
]);
</script>

<template>
    <div class="space-y-6">
        <!-- ── Tab Selector ────────────────────────────────── -->
        <div class="flex items-center gap-1 p-1 bg-gray-100/80 rounded-xl w-fit">
            <button
                v-for="tab in tabs"
                :key="tab"
                @click="activeTab = tab"
                :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all duration-150 cursor-pointer', activeTab === tab ? 'bg-white text-blue-700 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-800']"
            >
                {{ tab }}
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!-- TAB: Overview                                      -->
        <!-- ══════════════════════════════════════════════════ -->
        <div v-if="activeTab === 'Overview'" class="space-y-6">
            <!-- KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4">
                <div
                    v-for="card in kpiCards"
                    :key="card.label"
                    class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md hover:border-blue-200/60 transition-all duration-200"
                >
                    <!-- Row 1: icon + label -->
                    <div class="flex items-center gap-2">
                        <span :class="['w-7 h-7 rounded-lg flex items-center justify-center shrink-0', card.bg]">
                            <component :is="card.icon" :class="['w-3.5 h-3.5', card.color]" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide truncate">{{ card.label }}</p>
                    </div>
                    <!-- Row 2: value + badge -->
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ card.value }}</p>
                        <span :class="['text-[10px] font-semibold px-1.5 py-0.5 rounded-full flex items-center gap-0.5 shrink-0', card.up ? 'text-emerald-700 bg-emerald-50' : 'text-red-600 bg-red-50']">
                            <ArrowUpIcon v-if="card.up" class="w-2.5 h-2.5" />
                            <ArrowDownIcon v-else class="w-2.5 h-2.5" />
                            {{ card.change }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Revenue Bar -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Monthly Revenue</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Last 6 months</p>
                        </div>
                        <ChartBarIcon class="w-5 h-5 text-blue-300" />
                    </div>
                    <div class="h-52">
                        <Bar :data="revenueBarData" :options="chartOpts" />
                    </div>
                </div>

                <!-- Lead Status Doughnut -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-gray-900">Lead Status</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Breakdown by stage</p>
                    </div>
                    <div class="h-52">
                        <Doughnut :data="leadStatusData" :options="doughnutOpts" />
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">Recent Orders</h3>
                    <a href="/orders" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all cursor-pointer">View all</a>
                </div>
                <div v-if="recentOrders.length === 0" class="py-10 text-center text-gray-400 text-sm">No orders yet.</div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/60">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Order</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/80">
                            <tr v-for="o in recentOrders" :key="o.order_number" class="hover:bg-blue-50/20 transition-colors">
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-blue-600">{{ o.order_number }}</td>
                                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">{{ o.customer_name }}</td>
                                <td class="px-5 py-3.5 font-mono font-semibold text-emerald-700">${{ Number(o.total_amount).toFixed(2) }}</td>
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium capitalize', payClass(o.payment_status)]">
                                        {{ o.payment_status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-gray-500">{{ fmtDate(o.ordered_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!-- TAB: Revenue                                       -->
        <!-- ══════════════════════════════════════════════════ -->
        <div v-else-if="activeTab === 'Revenue'" class="space-y-5">
            <!-- Revenue Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-emerald-100">
                            <CurrencyDollarIcon class="w-3.5 h-3.5 text-emerald-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Total Revenue</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ fmt(kpis.total_revenue) }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full flex items-center gap-0.5 text-emerald-700 bg-emerald-50 shrink-0">
                            <ArrowUpIcon class="w-2.5 h-2.5" /> +12.4%
                        </span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-blue-100">
                            <ShoppingCartIcon class="w-3.5 h-3.5 text-blue-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Total Orders</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ fmtNum(kpis.total_orders) }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full flex items-center gap-0.5 text-emerald-700 bg-emerald-50 shrink-0">
                            <ArrowUpIcon class="w-2.5 h-2.5" /> +8.1%
                        </span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-indigo-100">
                            <ChartBarIcon class="w-3.5 h-3.5 text-indigo-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Avg. Order Value</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ kpis.total_orders ? fmt(kpis.total_revenue / kpis.total_orders) : '$0' }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full text-gray-500 bg-gray-100 shrink-0">Per order</span>
                    </div>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Revenue Trend</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Monthly revenue over the last 6 months</p>
                    </div>
                </div>
                <div class="h-72">
                    <Line :data="revenueLineData" :options="chartOpts" />
                </div>
            </div>

            <!-- Bar Chart -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Revenue by Month</h3>
                <div class="h-64">
                    <Bar :data="revenueBarData" :options="chartOpts" />
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════ -->
        <!-- TAB: Partners & Leads                             -->
        <!-- ══════════════════════════════════════════════════ -->
        <div v-else-if="activeTab === 'Partners & Leads'" class="space-y-5">
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-indigo-100">
                            <BuildingOffice2Icon class="w-3.5 h-3.5 text-indigo-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide truncate">Total Partners</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ fmtNum(kpis.total_partners) }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full text-indigo-700 bg-indigo-50 shrink-0">All types</span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-emerald-100">
                            <UsersIcon class="w-3.5 h-3.5 text-emerald-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide truncate">Active Partners</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ fmtNum(kpis.active_partners) }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full flex items-center gap-0.5 text-emerald-700 bg-emerald-50 shrink-0">
                            <ArrowUpIcon class="w-2.5 h-2.5" /> +2 new
                        </span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-amber-100">
                            <UserPlusIcon class="w-3.5 h-3.5 text-amber-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide truncate">Total Leads</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ fmtNum(kpis.total_leads) }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full text-amber-700 bg-amber-50 shrink-0">In pipeline</span>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-4 flex flex-col gap-2.5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 bg-emerald-100">
                            <UserGroupIcon class="w-3.5 h-3.5 text-emerald-600" />
                        </span>
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide truncate">Converted Leads</p>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ fmtNum(kpis.converted_leads) }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full text-emerald-700 bg-emerald-50 shrink-0">
                            {{ kpis.total_leads ? Math.round((kpis.converted_leads / kpis.total_leads) * 100) : 0 }}% rate
                        </span>
                    </div>
                </div>
            </div>

            <!-- Side-by-side Doughnuts -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Partner Types</h3>
                    <div class="h-56">
                        <Doughnut :data="partnerTypeData" :options="doughnutOpts" />
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Lead Status Breakdown</h3>
                    <div class="h-56">
                        <Doughnut :data="leadStatusData" :options="doughnutOpts" />
                    </div>
                </div>
            </div>

            <!-- Conversion Rate Card -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-6 py-5 flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center shrink-0">
                    <span class="text-xl font-extrabold text-emerald-700">
                        {{ kpis.total_leads ? Math.round((kpis.converted_leads / kpis.total_leads) * 100) : 0 }}%
                    </span>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">Lead Conversion Rate</p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ kpis.converted_leads ?? 0 }} out of {{ kpis.total_leads ?? 0 }} leads have been converted to customers.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
