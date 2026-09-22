<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-4 sm:p-6 selection:bg-indigo-500 selection:text-white relative overflow-hidden">
        <!-- Subtle Ambient Background Glows -->
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true" />
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true" />

        <!-- Brand / Logo Treatment -->
        <div class="mb-6 text-center z-10 flex flex-col items-center">
            <Link href="/" class="flex items-center gap-3 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl p-1">
                <img
                    src="/assets/logo.png"
                    alt="Partner Portal"
                    class="w-10 h-10 rounded-xl object-contain shadow-lg shadow-indigo-600/20 shrink-0"
                />
                <div class="text-left">
                    <h1 class="text-xl font-bold tracking-tight text-white leading-tight">Partner Portal</h1>
                    <p class="text-slate-400 text-xs font-medium">Management &amp; Distribution</p>
                </div>
            </Link>
        </div>

        <!-- Centered Card Surface -->
        <div class="w-full max-w-md bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60 backdrop-blur-xl ring-1 ring-white/5 z-10">
            <slot />
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-slate-500 z-10">
            Partner Portal &bull; Secure Access
        </div>
    </div>
</template>

