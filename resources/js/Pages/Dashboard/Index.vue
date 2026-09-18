<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
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
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <header class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-white">Dashboard</h1>
            <p class="text-slate-400 text-sm mt-1">Role-aware management shell</p>
        </header>

        <section>
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
        </section>
    </AuthenticatedLayout>
</template>
