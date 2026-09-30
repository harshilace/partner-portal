<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { ShoppingCartIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    orders: { type: Array, default: () => [] },
});

const columns = [
    { key: 'order_number',   label: 'Order #',        sortable: true,  filterable: true },
    { key: 'customer',       label: 'Customer',        sortable: false, filterable: false },
    { key: 'total_amount',   label: 'Amount',          sortable: true,  filterable: false },
    { key: 'payment_status', label: 'Payment',         sortable: true,  filterable: false },
    { key: 'status',         label: 'Order Status',    sortable: true,  filterable: false },
    { key: 'ordered_at',     label: 'Date',            sortable: true,  filterable: false },
];

const paymentClass = (s) => ({
    paid:    'bg-emerald-50 text-emerald-700 border-emerald-200',
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    failed:  'bg-red-50 text-red-600 border-red-200',
})[s] ?? 'bg-gray-100 text-gray-600 border-gray-200';

const orderClass = (s) => ({
    completed: 'bg-blue-50 text-blue-700 border-blue-200',
    pending:   'bg-amber-50 text-amber-700 border-amber-200',
    cancelled: 'bg-red-50 text-red-600 border-red-200',
})[s] ?? 'bg-gray-100 text-gray-600 border-gray-200';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
    catch { return d; }
};

const stats = computed(() => {
    const total = props.orders.reduce((s, o) => s + parseFloat(o.total_amount || 0), 0);
    return {
        count:   props.orders.length,
        revenue: total,
        paid:    props.orders.filter((o) => o.payment_status === 'paid').length,
        pending: props.orders.filter((o) => o.payment_status === 'pending').length,
    };
});

const fmt = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Orders" />

        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center shadow-sm shadow-emerald-600/30">
                    <ShoppingCartIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Orders</h1>
                    <p class="text-gray-500 text-xs mt-0.5">View product sales, order status, and customer payments.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Total Orders</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.count }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Revenue</p>
                <p class="text-2xl font-bold text-emerald-600">{{ fmt(stats.revenue) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Paid</p>
                <p class="text-2xl font-bold text-blue-600">{{ stats.paid }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Pending</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats.pending }}</p>
            </div>
        </div>

        <DataTable :rows="orders" :columns="columns" empty-message="No orders found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-blue-600 whitespace-nowrap">
                    {{ row.order_number }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                    {{ row.customer?.name || '—' }}
                </td>
                <td class="px-5 py-3.5 font-mono text-sm font-semibold text-emerald-700 whitespace-nowrap">
                    ${{ Number(row.total_amount).toFixed(2) }}
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', paymentClass(row.payment_status)]">
                        {{ row.payment_status || 'Unpaid' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', orderClass(row.status)]">
                        {{ row.status || 'Completed' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                    {{ formatDate(row.ordered_at || row.created_at) }}
                </td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
