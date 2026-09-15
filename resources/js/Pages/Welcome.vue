<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    version: {
        type: String,
        default: '13.x',
    },
});
</script>

<template>
    <Head title="Partner Portal - Foundation" />
    <main class="min-h-screen flex items-center justify-center bg-slate-900 text-white p-6">
        <div class="max-w-md w-full bg-slate-800 rounded-xl p-8 shadow-2xl border border-slate-700 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-white mb-2">Partner Portal</h1>
            <p class="text-slate-400 text-sm mb-6">Phase 0: Project Foundation</p>
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                Stack: Laravel 13 • Inertia.js • Vue 3 • Vite
            </div>
        </div>
    </main>
</template>
