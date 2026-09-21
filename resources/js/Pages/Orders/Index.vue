<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    try {
        return new Date(dateStr).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Orders" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Orders</h1>
                <p class="text-slate-400 text-sm mt-1">View product sales, order status, and customer payments.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                    Total: {{ orders.length }}
                </span>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div v-if="orders.length === 0" class="py-12 text-center text-slate-400 text-sm">
                <p>No orders found.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="px-6 py-3.5">Order #</th>
                            <th scope="col" class="px-6 py-3.5">Customer</th>
                            <th scope="col" class="px-6 py-3.5">Amount</th>
                            <th scope="col" class="px-6 py-3.5">Payment</th>
                            <th scope="col" class="px-6 py-3.5">Order Status</th>
                            <th scope="col" class="px-6 py-3.5">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-indigo-400 whitespace-nowrap">
                                {{ order.order_number }}
                            </td>
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ order.customer?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-sm text-emerald-400 whitespace-nowrap font-medium">
                                ${{ Number(order.total_amount).toFixed(2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="order.payment_status === 'paid'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                >
                                    Paid
                                </span>
                                <span
                                    v-else-if="order.payment_status === 'pending'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20"
                                >
                                    Pending
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20"
                                >
                                    {{ order.payment_status || 'Unpaid' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300 capitalize">
                                    {{ order.status || 'Completed' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDate(order.ordered_at || order.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
