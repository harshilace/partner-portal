<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Reports" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-white">Reports</h1>
            <p class="text-slate-400 text-sm mt-1">Sales, Customer, and Renewal reports</p>
        </div>

        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl">
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    Status: 501 Not Implemented
                </span>
            </div>
            <h2 class="text-lg font-semibold text-white mb-2">Report Data Retrieval Blocked</h2>
            <p class="text-slate-400 text-sm leading-relaxed">
                Report data retrieval and scoping remain blocked pending business confirmation (BC-10-01 through BC-10-10).
                No report columns, filters, metrics, or export downloads are implemented in this release.
            </p>
        </div>
    </AuthenticatedLayout>
</template>
