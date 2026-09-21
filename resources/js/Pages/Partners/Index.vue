<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    partners: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const role = computed(() => page.props.auth?.user?.role);

const title = computed(() => (role.value === 'main_partner' ? 'Sub-Partners' : 'Partners'));
const subtitle = computed(() =>
    role.value === 'main_partner'
        ? 'Manage and monitor your assigned sub-partner organizations.'
        : 'Manage partner organizations, hierarchy, and system relationships.'
);

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
        <Head :title="title" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ title }}</h1>
                <p class="text-slate-400 text-sm mt-1">{{ subtitle }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                    Total: {{ partners.length }}
                </span>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div v-if="partners.length === 0" class="py-12 text-center text-slate-400 text-sm">
                <p>No {{ title.toLowerCase() }} found.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="px-6 py-3.5">Code</th>
                            <th scope="col" class="px-6 py-3.5">Name</th>
                            <th scope="col" class="px-6 py-3.5">Type</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <tr
                            v-for="partner in partners"
                            :key="partner.id"
                            class="hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4 font-mono text-xs font-medium text-indigo-400 whitespace-nowrap">
                                {{ partner.partner_code || '—' }}
                            </td>
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ partner.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="partner.type === 'main'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
                                >
                                    Main
                                </span>
                                <span
                                    v-else-if="partner.type === 'sub'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20"
                                >
                                    Sub
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20"
                                >
                                    {{ partner.type || '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="partner.status === 'active'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                >
                                    Active
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20"
                                >
                                    {{ partner.status || 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDate(partner.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
