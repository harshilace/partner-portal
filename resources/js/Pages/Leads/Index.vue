<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    leads: {
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

const statusClasses = (status) => {
    switch (status) {
        case 'converted':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'qualified':
            return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'contacted':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        case 'lost':
            return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        default:
            return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Leads" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Leads</h1>
                <p class="text-slate-400 text-sm mt-1">Track prospective customers, lead status, and partner attribution.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                    Total: {{ leads.length }}
                </span>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div v-if="leads.length === 0" class="py-12 text-center text-slate-400 text-sm">
                <p>No leads found.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="px-6 py-3.5">Name</th>
                            <th scope="col" class="px-6 py-3.5">Contact</th>
                            <th scope="col" class="px-6 py-3.5">Partner</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <tr
                            v-for="lead in leads"
                            :key="lead.id"
                            class="hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ lead.name }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300 whitespace-nowrap">
                                <div>{{ lead.email }}</div>
                                <div v-if="lead.mobile" class="text-slate-400">{{ lead.mobile }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300 whitespace-nowrap">
                                <div>{{ lead.partner?.name || '—' }}</div>
                                <div v-if="lead.sub_partner" class="text-slate-400">{{ lead.sub_partner.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="statusClasses(lead.status)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize"
                                >
                                    {{ lead.status || 'New' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDate(lead.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
