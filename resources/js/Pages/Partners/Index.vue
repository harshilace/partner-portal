<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { UsersIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    partners: { type: Array, default: () => [] },
});

const page = usePage();
const role = computed(() => page.props.auth?.user?.role);

const title = computed(() => (role.value === 'main_partner' ? 'Sub-Partners' : 'Partners'));
const subtitle = computed(() =>
    role.value === 'main_partner'
        ? 'Manage and monitor your assigned sub-partner organizations.'
        : 'Manage partner organizations, hierarchy, and system relationships.'
);

const columns = [
    { key: 'partner_code', label: 'Code',    sortable: true,  filterable: true },
    { key: 'name',         label: 'Name',    sortable: true,  filterable: true },
    {
        key: 'type',
        label: 'Type',
        sortable: true,
        filterable: true,
        options: [
            { label: 'Main', value: 'main' },
            { label: 'Sub',  value: 'sub' },
        ],
    },
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
    { key: 'created_at',   label: 'Created', sortable: true,  filterable: false },
];

const typeClass = (t) => ({
    main: 'bg-blue-50 text-blue-700 border-blue-200',
    sub:  'bg-purple-50 text-purple-700 border-purple-200',
})[t] ?? 'bg-gray-100 text-gray-600 border-gray-200';

const statusClass = (s) =>
    s === 'active'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
        : 'bg-red-50 text-red-600 border-red-200';

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
    catch { return d; }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="title" />

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm shadow-blue-600/30">
                    <UsersIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">{{ title }}</h1>
                    <p class="text-gray-500 text-xs mt-0.5">{{ subtitle }}</p>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :rows="partners" :columns="columns" empty-message="No partners found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-blue-600 whitespace-nowrap">
                    {{ row.partner_code || '—' }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                    {{ row.name }}
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', typeClass(row.type)]">
                        {{ row.type || '—' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize', statusClass(row.status)]">
                        <span :class="['w-1.5 h-1.5 rounded-full', row.status === 'active' ? 'bg-emerald-500' : 'bg-red-400']" />
                        {{ row.status || 'Inactive' }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                    {{ formatDate(row.created_at) }}
                </td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
