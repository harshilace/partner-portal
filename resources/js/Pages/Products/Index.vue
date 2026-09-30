<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { CubeIcon } from '@heroicons/vue/24/outline';

defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const columns = [
    { key: 'code',      label: 'Code',    sortable: true,  filterable: true },
    { key: 'name',      label: 'Name',    sortable: true,  filterable: true },
    { key: 'plans',     label: 'Plans',   sortable: false, filterable: false },
    {
        key: 'is_active',
        label: 'Status',
        sortable: true,
        filterable: true,
        options: [
            { label: 'Active',   value: 'true' },
            { label: 'Inactive', value: 'false' },
        ],
    },
    { key: 'created_at', label: 'Created', sortable: true,  filterable: false },
];

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
        <Head title="Products & Plans" />

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-purple-600 flex items-center justify-center shadow-sm shadow-purple-600/30">
                    <CubeIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Products &amp; Plans</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Manage catalog offerings, pricing plans, and activation status.</p>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :rows="products" :columns="columns" empty-message="No products found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-purple-600 whitespace-nowrap">
                    {{ row.code }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900">
                    <div>{{ row.name }}</div>
                    <div v-if="row.description" class="text-xs text-gray-400 mt-0.5 line-clamp-1">
                        {{ row.description }}
                    </div>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="plan in row.plans || []"
                            :key="plan.id"
                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200"
                        >
                            {{ plan.name }}: ${{ Number(plan.price).toFixed(2) }}
                        </span>
                        <span v-if="!row.plans || row.plans.length === 0" class="text-xs text-gray-400">
                            No plans
                        </span>
                    </div>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap">
                    <span v-if="row.is_active" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" />
                        Active
                    </span>
                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400" />
                        Inactive
                    </span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                    {{ formatDate(row.created_at) }}
                </td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
