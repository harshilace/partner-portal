<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    subscriptions: { type: Array, default: () => [] },
});

const columns = [
    { key: 'subscription_number', label: 'Sub #',        sortable: true,  filterable: true },
    { key: 'customer',            label: 'Customer',      sortable: false, filterable: false },
    { key: 'product',             label: 'Product / Plan',sortable: false, filterable: false },
    { key: 'auto_debit_enabled',  label: 'Auto-Debit',   sortable: true,  filterable: false },
    { key: 'status',              label: 'Status',        sortable: true,  filterable: false },
    { key: 'expires_at',          label: 'Expires',       sortable: true,  filterable: false },
];

const statusClass = (s) => ({
    active:    'bg-emerald-50 text-emerald-700 border-emerald-200',
    cancelled: 'bg-red-50 text-red-600 border-red-200',
    expired:   'bg-amber-50 text-amber-700 border-amber-200',
})[s] ?? 'bg-gray-100 text-gray-600 border-gray-200';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
    catch { return d; }
};

const stats = computed(() => ({
    total:     props.subscriptions.length,
    active:    props.subscriptions.filter((s) => s.status === 'active').length,
    autoDebit: props.subscriptions.filter((s) => s.auto_debit_enabled).length,
    expired:   props.subscriptions.filter((s) => s.status === 'expired').length,
}));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Subscriptions" />

        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-violet-600 flex items-center justify-center shadow-sm shadow-violet-600/30">
                    <ArrowPathIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Subscriptions</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Manage active customer subscriptions, plans, and renewal status.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Active</p>
                <p class="text-2xl font-bold text-emerald-600">{{ stats.active }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Auto-Debit On</p>
                <p class="text-2xl font-bold text-indigo-600">{{ stats.autoDebit }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm px-4 py-3.5">
                <p class="text-[11px] text-gray-500 font-medium">Expired</p>
                <p class="text-2xl font-bold text-amber-600">{{ stats.expired }}</p>
            </div>
        </div>

        <DataTable :rows="subscriptions" :columns="columns" empty-message="No subscriptions found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-violet-600 whitespace-nowrap">
                    {{ row.subscription_number }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                    {{ row.customer?.name || '—' }}
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                    <div class="font-medium text-gray-800">{{ row.product?.name || '—' }}</div>
                    <div v-if="row.product_plan" class="text-xs text-gray-400">{{ row.product_plan.name }}</div>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span v-if="row.auto_debit_enabled" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">Enabled</span>
                    <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">Disabled</span>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', statusClass(row.status)]">
                        {{ row.status || 'Active' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(row.expires_at) }}</td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
