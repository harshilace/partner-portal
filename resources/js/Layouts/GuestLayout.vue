<script setup>
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-white flex flex-col justify-center items-center p-4 sm:p-6">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-white">Partner Portal</h1>
            <p class="text-slate-400 text-xs mt-1">Management Portal</p>
        </div>

        <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-xl p-6 sm:p-8 shadow-2xl">
            <slot />
        </div>
    </div>
</template>
