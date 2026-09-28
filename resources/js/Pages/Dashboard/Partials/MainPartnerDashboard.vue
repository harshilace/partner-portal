<script setup>
import { ref, computed } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import {
    CurrencyDollarIcon,
    UsersIcon,
    UserPlusIcon,
    ArrowPathIcon,
    UserGroupIcon,
    ArrowUpIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

const props = defineProps({
    data: { type: Object, default: () => ({}) },
    pending_confirmation: { type: Array, default: () => [] },
});

const tabs = ['Overview', 'Sub-Partners'];
const activeTab = ref('Overview');

const kpis = computed(() => props.data?.kpis ?? {});
const recentOrders = computed(() => props.data?.recent_orders ?? []);

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
})[s] ?? 'bg-gray-100 text-gray-600';

const leadStatusData = computed(() => {
    const d = props.data?.leads_by_status ?? {};
    return {
        labels: Object.keys(d).length ? Object.keys(d).map((k) => k.charAt(0).toUpperCase() + k.slice(1)) : ['No data'],
        datasets: [{
            data: Object.values(d).length ? Object.values(d) : [1],
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#6b7280'],
            borderColor: '#fff',
            borderWidth: 2,
        }],
    };
});

const revenueLabels = computed(() => Object.keys(props.data?.revenue_by_month ?? {}));
const revenueValues = computed(() => Object.values(props.data?.revenue_by_month ?? {}).map(Number));

const revenueBarData = computed(() => ({
    labels: revenueLabels.value.length ? revenueLabels.value : ['No data'],
    datasets: [{
        label: 'Revenue ($)',
        data: revenueValues.value.length ? revenueValues.value : [0],
        backgroundColor: 'rgba(16,185,129,0.15)',
        borderColor: '#10b981',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
    }],
}));

const chartOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8, padding: 10 } },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } }, border: { display: false } },
        y: { grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { color: '#94a3b8', font: { size: 11 }, callback: (v) => '$' + Number(v).toLocaleString() }, border: { display: false } },
    },
};

const doughnutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: { legend: { position: 'right', labels: { color: '#475569', font: { size: 11 }, padding: 12, boxWidth: 12, borderRadius: 4 } }, tooltip: { backgroundColor: '#1e293b', cornerRadius: 8 } },
};

const kpiCards = computed(() => [
    { label: 'Revenue', value: fmt(kpis.value.total_revenue), icon: CurrencyDollarIcon, color: 'text-emerald-600', bg: 'bg-emerald-100' },
    { label: 'Orders', value: fmtNum(kpis.value.total_orders), icon: ArrowPathIcon, color: 'text-blue-600', bg: 'bg-blue-100' },
    { label: 'Sub-Partners', value: fmtNum(kpis.value.sub_partners), icon: UsersIcon, color: 'text-indigo-600', bg: 'bg-indigo-100' },
    { label: 'Leads', value: fmtNum(kpis.value.total_leads), icon: UserPlusIcon, color: 'text-amber-600', bg: 'bg-amber-100' },
    { label: 'Customers', value: fmtNum(kpis.value.total_customers), icon: UserGroupIcon, color: 'text-purple-600', bg: 'bg-purple-100' },
]);
</script>

<template>
    <div class="space-y-6">
        <!-- Tab Selector -->
        <div class="flex items-center gap-1 p-1 bg-gray-100/80 rounded-xl w-fit">
            <button
                v-for="tab in tabs"
                :key="tab"
                @click="activeTab = tab"
                :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all cursor-pointer', activeTab === tab ? 'bg-white text-emerald-700 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-800']"
            >
                {{ tab }}
            </button>
        </div>

        <!-- Overview Tab -->
        <div v-if="activeTab === 'Overview'" class="space-y-5">
            <!-- KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div
                    v-for="card in kpiCards"
                    :key="card.label"
                    class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-4 py-4 hover:shadow-md hover:border-emerald-200/60 transition-all"
                >
                    <div class="flex items-center justify-between mb-3">
                        <span :class="['w-9 h-9 rounded-xl flex items-center justify-center', card.bg]">
                            <component :is="card.icon" :class="['w-5 h-5', card.color]" />
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ card.value }}</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Monthly Revenue</h3>
                    <p class="text-xs text-gray-400 mb-4">Last 6 months</p>
                    <div class="h-52">
                        <Bar :data="revenueBarData" :options="chartOpts" />
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Lead Status</h3>
                    <p class="text-xs text-gray-400 mb-4">Breakdown</p>
                    <div class="h-52">
                        <Doughnut :data="leadStatusData" :options="doughnutOpts" />
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div v-if="recentOrders.length > 0" class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900">Recent Orders</h3>
                    <a href="/orders" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all cursor-pointer">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/60">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Order</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Customer</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Amount</th>
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/80">
                            <tr v-for="o in recentOrders" :key="o.order_number" class="hover:bg-emerald-50/20 transition-colors">
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-blue-600">{{ o.order_number }}</td>
                                <td class="px-5 py-3.5 font-medium text-gray-900">{{ o.customer_name }}</td>
                                <td class="px-5 py-3.5 font-mono font-semibold text-emerald-700">${{ Number(o.total_amount).toFixed(2) }}</td>
                                <td class="px-5 py-3.5">
                                    <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium capitalize', payClass(o.payment_status)]">
                                        {{ o.payment_status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sub-Partners Tab -->
        <div v-else-if="activeTab === 'Sub-Partners'" class="space-y-5">
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-6 py-12 text-center text-gray-400">
                <UsersIcon class="w-12 h-12 mx-auto mb-3 text-gray-200 stroke-1" />
                <p class="text-sm font-medium text-gray-600">Sub-Partner Analytics</p>
                <p class="text-xs mt-1">Detailed sub-partner performance metrics coming soon.</p>
                <a href="/partners" class="inline-flex mt-4 px-4 py-2 rounded-xl text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">View Sub-Partners</a>
            </div>
        </div>
    </div>
</template>
