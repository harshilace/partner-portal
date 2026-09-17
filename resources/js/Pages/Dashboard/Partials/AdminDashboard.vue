<script setup>
defineProps({
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
    <section class="bg-slate-800 rounded-xl p-8 border border-slate-700 shadow-xl" aria-label="Admin Dashboard">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-white">Admin Dashboard Shell</h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                Admin
            </span>
        </div>
        <p class="text-slate-400 text-sm">
            Dashboard metrics are pending business confirmation.
        </p>
    </section>
</template>
