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
    <section class="bg-slate-800 rounded-xl p-8 border border-slate-700 shadow-xl" aria-label="Sub-Partner Dashboard">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-white">Sub-Partner Dashboard Shell</h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                Sub-Partner
            </span>
        </div>
        <p class="text-slate-400 text-sm">
            Dashboard metrics are pending business confirmation.
        </p>
    </section>
</template>
