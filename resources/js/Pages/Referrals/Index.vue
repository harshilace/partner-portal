<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import DataTable from '../../Components/Common/DataTable.vue';
import { ShareIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    referral_codes: { type: Array, default: () => [] },
});

const columns = [
    { key: 'code',      label: 'Code',          sortable: true,  filterable: true },
    { key: 'partner',   label: 'Main Partner',   sortable: false, filterable: false },
    { key: 'sub_partner', label: 'Sub-Partner',  sortable: false, filterable: false },
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
    { key: 'created_at', label: 'Created',       sortable: true,  filterable: false },
];

const formatDate = (d) => {
    if (!d) return '—';
    try { return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }); }
    catch { return d; }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Referral Codes" />

        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-sky-500 flex items-center justify-center shadow-sm shadow-sky-500/30">
                    <ShareIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 tracking-tight">Referral Codes</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Manage partner and sub-partner attribution referral codes.</p>
                </div>
            </div>
        </div>

        <DataTable :rows="referral_codes" :columns="columns" empty-message="No referral codes found.">
            <template #row="{ row }">
                <td class="px-5 py-3.5 font-mono text-sm font-bold text-sky-600 whitespace-nowrap tracking-wider">
                    {{ row.code }}
                </td>
                <td class="px-5 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                    {{ row.partner?.name || '—' }}
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                    {{ row.sub_partner?.name || '—' }}
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
                <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">{{ formatDate(row.created_at) }}</td>
            </template>
        </DataTable>
    </AuthenticatedLayout>
</template>
