<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    subscriptions: {
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
        case 'active':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'cancelled':
            return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        case 'expired':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        default:
            return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Subscriptions" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Subscriptions</h1>
                <p class="text-slate-400 text-sm mt-1">Manage active customer subscriptions, plans, and renewal status.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                    Total: {{ subscriptions.length }}
                </span>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div v-if="subscriptions.length === 0" class="py-12 text-center text-slate-400 text-sm">
                <p>No subscriptions found.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="px-6 py-3.5">Subscription #</th>
                            <th scope="col" class="px-6 py-3.5">Customer</th>
                            <th scope="col" class="px-6 py-3.5">Product &amp; Plan</th>
                            <th scope="col" class="px-6 py-3.5">Auto-Debit</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5">Expires</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <tr
                            v-for="sub in subscriptions"
                            :key="sub.id"
                            class="hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-indigo-400 whitespace-nowrap">
                                {{ sub.subscription_number }}
                            </td>
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                                {{ sub.customer?.name || '—' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300 whitespace-nowrap">
                                <div>{{ sub.product?.name || '—' }}</div>
                                <div v-if="sub.product_plan" class="text-slate-400">{{ sub.product_plan.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="sub.auto_debit_enabled"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20"
                                >
                                    Enabled
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-400"
                                >
                                    Disabled
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="statusClasses(sub.status)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize"
                                >
                                    {{ sub.status || 'Active' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDate(sub.expires_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
