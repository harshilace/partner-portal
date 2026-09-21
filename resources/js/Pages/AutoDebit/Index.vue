<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    mandates: {
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
        <Head title="Auto-Debit Mandates" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Auto-Debit Mandates</h1>
                <p class="text-slate-400 text-sm mt-1">Manage recurring payment mandates and auto-debit statuses.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                    Total: {{ mandates.length }}
                </span>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div v-if="mandates.length === 0" class="py-12 text-center text-slate-400 text-sm">
                <p>No auto-debit mandates found.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="px-6 py-3.5">Mandate Ref</th>
                            <th scope="col" class="px-6 py-3.5">Customer</th>
                            <th scope="col" class="px-6 py-3.5">Subscription #</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <tr
                            v-for="mandate in mandates"
                            :key="mandate.id"
                            class="hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-indigo-400 whitespace-nowrap">
                                {{ mandate.mandate_reference }}
                            </td>
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ mandate.customer?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-300 whitespace-nowrap">
                                {{ mandate.subscription?.subscription_number || '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="mandate.status === 'active'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                >
                                    Active
                                </span>
                                <span
                                    v-else-if="mandate.status === 'stopped'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20"
                                >
                                    Stopped
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/10 text-slate-400 border border-slate-500/20 capitalize"
                                >
                                    {{ mandate.status || 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDate(mandate.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
