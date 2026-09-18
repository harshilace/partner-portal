<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';

defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
    unread_count: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Notifications" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-white">Notifications</h1>
            <p class="text-slate-400 text-sm mt-1">In-app notifications presentation shell</p>
        </div>

        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700">
                <span class="text-sm font-medium text-slate-300">Notification History</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300">
                    Unread: {{ unread_count }}
                </span>
            </div>

            <div class="py-8 text-center text-slate-400 text-sm">
                <p>Notification delivery and recipient triggers preserve Phase 9 backend behavior.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
