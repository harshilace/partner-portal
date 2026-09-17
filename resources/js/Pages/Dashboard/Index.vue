<script setup>
import { Head } from '@inertiajs/vue3';
import AdminDashboard from './Partials/AdminDashboard.vue';
import MainPartnerDashboard from './Partials/MainPartnerDashboard.vue';
import SubPartnerDashboard from './Partials/SubPartnerDashboard.vue';

defineProps({
    role: {
        type: String,
        required: true,
    },
    data: {
        type: Object,
        default: () => ({}),
    },
    pending_confirmation: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Dashboard" />
    <div class="min-h-screen bg-slate-900 text-white p-6">
        <div class="max-w-7xl mx-auto">
            <header class="mb-8">
                <h1 class="text-3xl font-bold tracking-tight text-white">Dashboard</h1>
                <p class="text-slate-400 text-sm mt-1">Role-aware management shell</p>
            </header>

            <main>
                <AdminDashboard
                    v-if="role === 'admin'"
                    :data="data"
                    :pending_confirmation="pending_confirmation"
                />
                <MainPartnerDashboard
                    v-else-if="role === 'main_partner'"
                    :data="data"
                    :pending_confirmation="pending_confirmation"
                />
                <SubPartnerDashboard
                    v-else-if="role === 'sub_partner'"
                    :data="data"
                    :pending_confirmation="pending_confirmation"
                />
            </main>
        </div>
    </div>
</template>
