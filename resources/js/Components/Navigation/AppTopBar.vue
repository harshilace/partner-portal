<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-mobile']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role);

const isAdmin = computed(() => role.value === 'admin');

const roleLabel = computed(() => {
    switch (role.value) {
        case 'admin':
            return 'Administrator';
        case 'main_partner':
            return 'Main Partner';
        case 'sub_partner':
            return 'Sub-Partner';
        default:
            return role.value || 'User';
    }
});

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const breadcrumb = computed(() => {
    const url = page.url;
    if (url.startsWith('/dashboard')) return { section: 'Overview', title: 'Dashboard' };
    if (url.startsWith('/partners')) return { section: 'Network', title: isAdmin.value ? 'Partners' : 'Sub-Partners' };
    if (url.startsWith('/referral-codes')) return { section: 'Network', title: 'Referral Codes' };
    if (url.startsWith('/leads')) return { section: 'Network', title: 'Leads' };
    if (url.startsWith('/customers')) return { section: 'Network', title: 'Customers' };
    if (url.startsWith('/products')) return { section: 'Commerce & Lifecycle', title: 'Products & Plans' };
    if (url.startsWith('/orders')) return { section: 'Commerce & Lifecycle', title: 'Orders' };
    if (url.startsWith('/subscriptions')) return { section: 'Commerce & Lifecycle', title: 'Subscriptions' };
    if (url.startsWith('/auto-debit-mandates')) return { section: 'Commerce & Lifecycle', title: 'Auto-Debit' };
    if (url.startsWith('/renewals')) return { section: 'Commerce & Lifecycle', title: 'Renewals' };
    if (url.startsWith('/notifications')) return { section: 'System & Governance', title: 'Notifications' };
    if (url.startsWith('/reports')) return { section: 'System & Governance', title: 'Reports' };
    if (url.startsWith('/audit')) return { section: 'System & Governance', title: 'Audit' };
    return { section: '', title: 'Partner Portal' };
});
</script>

<template>
    <header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Left: Mobile Toggle + Breadcrumb & Title -->
        <div class="flex items-center gap-3 min-w-0">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                @click="emit('toggle-mobile')"
                class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                aria-label="Toggle Navigation Drawer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Contextual Breadcrumb & Page Title -->
            <div class="min-w-0">
                <div v-if="breadcrumb.section" class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <span>{{ breadcrumb.section }}</span>
                    <span class="text-slate-600">/</span>
                    <span class="text-slate-400 truncate">{{ breadcrumb.title }}</span>
                </div>
                <h1 class="text-base sm:text-lg font-semibold text-white tracking-tight truncate leading-tight">
                    {{ breadcrumb.title }}
                </h1>
            </div>
        </div>

        <!-- Right: Notifications + User Controls -->
        <div class="flex items-center gap-2 sm:gap-4 shrink-0">
            <!-- Notifications Link -->
            <Link
                href="/notifications"
                class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 relative"
                aria-label="View Notifications"
                title="Notifications"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </Link>

            <div class="h-6 w-px bg-slate-800 hidden sm:block" aria-hidden="true" />

            <!-- User Context & Profile Controls -->
            <div v-if="user" class="flex items-center gap-3">
                <!-- User Avatar / Initials -->
                <div class="w-8 h-8 rounded-full bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-xs font-semibold shrink-0" aria-hidden="true">
                    {{ userInitials }}
                </div>

                <!-- User Name and Role Text (Desktop) -->
                <div class="hidden md:block text-left min-w-0 max-w-[160px]">
                    <div class="text-xs font-medium text-slate-200 truncate">{{ user.name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ roleLabel }}</div>
                </div>

                <!-- Sign Out Button -->
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="px-2.5 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700/80 rounded-lg border border-slate-700/60 transition-colors cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 shrink-0"
                    aria-label="Sign out of Partner Portal"
                >
                    Sign Out
                </Link>
            </div>
        </div>
    </header>
</template>
