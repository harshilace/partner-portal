<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppSidebar from '../Components/Navigation/AppSidebar.vue';
import AppTopBar from '../Components/Navigation/AppTopBar.vue';
import ToastNotification from '../Components/Common/ToastNotification.vue';

const isMobileOpen = ref(false);
const isCollapsed = ref(false);

const toggleMobile = () => {
    isMobileOpen.value = !isMobileOpen.value;
};

const closeMobile = () => {
    isMobileOpen.value = false;
};

const toggleCollapsed = () => {
    isCollapsed.value = !isCollapsed.value;
    try {
        localStorage.setItem('sidebar_collapsed', isCollapsed.value ? 'true' : 'false');
    } catch {
        // Ignore local storage restrictions
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Escape' && isMobileOpen.value) {
        closeMobile();
    }
};

onMounted(() => {
    try {
        const saved = localStorage.getItem('sidebar_collapsed');
        if (saved !== null) {
            isCollapsed.value = saved === 'true';
        }
    } catch {
        // Ignore
    }
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="min-h-screen bg-slate-50/70 text-gray-800 flex overflow-x-hidden selection:bg-blue-500 selection:text-white">
        <!-- Global Toast Notification Container -->
        <ToastNotification />

        <!-- Accessible Skip to Main Content link -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-md focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-white"
        >
            Skip to main content
        </a>

        <!-- Left Collapsible Sidebar -->
        <AppSidebar
            :is-collapsed="isCollapsed"
            :is-mobile-open="isMobileOpen"
            @toggle-collapsed="toggleCollapsed"
            @close-mobile="closeMobile"
        />

        <!-- Main Canvas -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <!-- Compact Top Bar -->
            <AppTopBar @toggle-mobile="toggleMobile" />

            <!-- Page Content (Uses Full Available Width) -->
            <main id="main-content" class="flex-1 w-full px-4 sm:px-6 lg:px-8 xl:px-10 py-6 sm:py-8 focus:outline-none" tabindex="-1">
                <slot />
            </main>

            <!-- Modern SaaS Footer -->
            <footer class="border-t border-gray-200/80 bg-white/70 backdrop-blur-sm px-4 sm:px-6 lg:px-8 xl:px-10 py-4 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500 font-medium gap-3">
                <div class="flex items-center gap-2 text-gray-500">
                    <span class="font-bold text-gray-900">Partner Portal</span>
                    <span>&bull;</span>
                    <span>&copy; {{ new Date().getFullYear() }} All rights reserved.</span>
                </div>
                <div class="flex items-center gap-2 text-gray-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Connected to Partner Network</span>
                    <span class="text-gray-300">&bull;</span>
                    <span class="text-gray-400 font-mono text-[11px]">v2.4.0</span>
                </div>
                <div class="flex items-center gap-4 text-gray-500">
                    <a href="#" class="hover:text-blue-600 transition-colors">Documentation</a>
                    <a href="#" class="hover:text-blue-600 transition-colors">Support</a>
                    <a href="#" class="hover:text-blue-600 transition-colors">System Status</a>
                </div>
            </footer>
        </div>
    </div>
</template>
