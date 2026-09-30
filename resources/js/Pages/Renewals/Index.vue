<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
    renewals: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { key: 'subscription', label: 'Subscription', sortable: false, filterable: false },
    { key: 'customer',     label: 'Customer',     sortable: false, filterable: false },
    { key: 'partner',      label: 'Partner',      sortable: false, filterable: false },
    {
        key: 'status',
        label: 'Status',
        sortable: true,
        filterable: true,
        options: [
            { label: 'Processed', value: 'processed' },
            { label: 'Pending',   value: 'pending' },
            { label: 'Lapsed',    value: 'lapsed' },
        ],
    },
    { key: 'due_date',     label: 'Due Date',     sortable: true,  filterable: false },
];

const statusClass = (s) => ({
    processed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    pending:   'bg-amber-50 text-amber-700 border-amber-200',
    lapsed:    'bg-red-50 text-red-600 border-red-200',
})[s] ?? 'bg-gray-100 text-gray-600 border-gray-200';

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
        <Head title="Renewals" />

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-teal-600 flex items-center justify-center shadow-sm shadow-teal-600/30">
                    <ArrowPathIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Renewals</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Monitor upcoming subscription renewals, reminders, and processing.</p>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :rows="renewals" :columns="columns" empty-message="No renewals found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-teal-700 whitespace-nowrap">
                    {{ row.subscription?.subscription_number || `#${row.subscription_id}` }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                    {{ row.customer?.name || '—' }}
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-600 whitespace-nowrap">
                    <div class="font-medium text-gray-900">{{ row.partner?.name || '—' }}</div>
                    <div v-if="row.sub_partner" class="text-gray-400 mt-0.5">{{ row.sub_partner.name }}</div>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', statusClass(row.status)]">
                        <span :class="['w-1.5 h-1.5 rounded-full', row.status === 'processed' ? 'bg-emerald-500' : row.status === 'lapsed' ? 'bg-red-400' : 'bg-amber-400']" />
                        {{ row.status || 'Pending' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                    {{ formatDate(row.due_date) }}
                </td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
