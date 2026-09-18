<script setup>
import AppNavbar from '../Components/Navigation/AppNavbar.vue';
</script>

<template>
    <div class="min-h-screen bg-slate-900 text-white flex flex-col">
        <AppNavbar />

        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
            <slot />
        </main>

        <footer class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            Partner Portal &bull; Phase 12 UI/UX
        </footer>
    </div>
</template>
