<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { UserPlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    leads: { type: Array, default: () => [] },
});

const columns = [
    { key: 'name',       label: 'Name',      sortable: true,  filterable: true },
    { key: 'email',      label: 'Email',      sortable: true,  filterable: true },
    { key: 'mobile',     label: 'Mobile',     sortable: false, filterable: false },
    {
        key: 'status',
        label: 'Status',
        sortable: true,
        filterable: true,
        options: [
            { label: 'New',       value: 'new' },
            { label: 'Contacted', value: 'contacted' },
            { label: 'Qualified', value: 'qualified' },
            { label: 'Converted', value: 'converted' },
            { label: 'Lost',      value: 'lost' },
        ],
    },
    { key: 'created_at', label: 'Created',    sortable: true,  filterable: false },
];

const statusClass = (s) => ({
    converted: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    qualified:  'bg-blue-50 text-blue-700 border-blue-200',
    contacted:  'bg-amber-50 text-amber-700 border-amber-200',
    lost:       'bg-red-50 text-red-600 border-red-200',
    new:        'bg-gray-100 text-gray-600 border-gray-200',
})[s] ?? 'bg-gray-100 text-gray-600 border-gray-200';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
    catch { return d; }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Leads" />

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center shadow-sm shadow-amber-500/30">
                    <UserPlusIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Leads</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Track prospective customers, lead status, and partner attribution.</p>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :rows="leads" :columns="columns" empty-message="No leads found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">{{ row.name }}</td>
                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">{{ row.email || '—' }}</td>
                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">{{ row.mobile || '—' }}</td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', statusClass(row.status)]">
                        {{ row.status || 'New' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(row.created_at) }}</td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
