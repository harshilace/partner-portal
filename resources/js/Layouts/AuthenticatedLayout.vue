<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppSidebar from '../Components/Navigation/AppSidebar.vue';
import AppTopBar from '../Components/Navigation/AppTopBar.vue';

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
    <div class="min-h-screen bg-slate-950 text-white flex overflow-x-hidden">
        <!-- Accessible Skip to Main Content link -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-md focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-white"
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

            <!-- Page Content -->
            <main id="main-content" class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 focus:outline-none" tabindex="-1">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-800/80 py-4 px-6 text-center text-xs text-slate-500">
                Partner Portal &bull; Phase 12 UI/UX
            </footer>
        </div>
    </div>
</template>

