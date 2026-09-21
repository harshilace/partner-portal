<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    products: {
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
        <Head title="Products & Plans" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Products &amp; Plans</h1>
                <p class="text-slate-400 text-sm mt-1">Manage catalog offerings, pricing plans, and activation status.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                    Total: {{ products.length }}
                </span>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div v-if="products.length === 0" class="py-12 text-center text-slate-400 text-sm">
                <p>No products found.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 bg-slate-800/80 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th scope="col" class="px-6 py-3.5">Code</th>
                            <th scope="col" class="px-6 py-3.5">Name</th>
                            <th scope="col" class="px-6 py-3.5">Plans</th>
                            <th scope="col" class="px-6 py-3.5">Status</th>
                            <th scope="col" class="px-6 py-3.5">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/60">
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="hover:bg-slate-700/30 transition-colors"
                        >
                            <td class="px-6 py-4 font-mono text-xs font-medium text-indigo-400 whitespace-nowrap">
                                {{ product.code }}
                            </td>
                            <td class="px-6 py-4 font-medium text-white">
                                <div>{{ product.name }}</div>
                                <div v-if="product.description" class="text-xs text-slate-400 mt-0.5 line-clamp-1">
                                    {{ product.description }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="plan in product.plans || []"
                                        :key="plan.id"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-300 border border-slate-600"
                                    >
                                        {{ plan.name }}: ${{ Number(plan.price).toFixed(2) }}
                                    </span>
                                    <span v-if="!product.plans || product.plans.length === 0" class="text-xs text-slate-500">
                                        No plans
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    v-if="product.is_active"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                                >
                                    Active
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20"
                                >
                                    Inactive
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ formatDate(product.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
