<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { UserGroupIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    customers: { type: Array, default: () => [] },
});

const columns = [
    { key: 'customer_code', label: 'Code',    sortable: true,  filterable: true },
    { key: 'name',          label: 'Name',    sortable: true,  filterable: true },
    { key: 'email',         label: 'Email',   sortable: true,  filterable: true },
    {
        key: 'status',
        label: 'Status',
        sortable: true,
        filterable: true,
        options: [
            { label: 'Active',   value: 'active' },
            { label: 'Inactive', value: 'inactive' },
        ],
    },
    { key: 'created_at',    label: 'Created', sortable: true,  filterable: false },
];

const statusClass = (s) =>
    s === 'active'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
        : 'bg-gray-100 text-gray-600 border-gray-200';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
    catch { return d; }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Customers" />

        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shadow-sm shadow-indigo-600/30">
                    <UserGroupIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Customers</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Manage attributed customers and partner organization assignments.</p>
                </div>
            </div>
        </div>

        <DataTable :rows="customers" :columns="columns" empty-message="No customers found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-indigo-600 whitespace-nowrap">
                    {{ row.customer_code || '—' }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">{{ row.name }}</td>
                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                    <div>{{ row.email }}</div>
                    <div v-if="row.mobile" class="text-xs text-gray-400">{{ row.mobile }}</div>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', statusClass(row.status)]">
                        <span :class="['w-1.5 h-1.5 rounded-full', row.status === 'active' ? 'bg-emerald-500' : 'bg-gray-400']" />
                        {{ row.status || 'Inactive' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(row.created_at) }}</td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
