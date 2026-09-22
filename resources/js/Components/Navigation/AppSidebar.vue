<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const props = defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
    isMobileOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle-collapsed', 'close-mobile']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role);

const isAdmin = computed(() => role.value === 'admin');
const isMainPartner = computed(() => role.value === 'main_partner');
const isSubPartner = computed(() => role.value === 'sub_partner');

const roleBadgeLabel = computed(() => {
    switch (role.value) {
        case 'admin':
            return 'Admin';
        case 'main_partner':
            return 'Main Partner';
        case 'sub_partner':
            return 'Sub-Partner';
        default:
            return role.value || 'User';
    }
});

const roleBadgeClass = computed(() => {
    switch (role.value) {
        case 'admin':
            return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'main_partner':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'sub_partner':
            return 'bg-sky-500/10 text-sky-400 border-sky-500/20';
        default:
            return 'bg-slate-800 text-slate-400 border-slate-700';
    }
});

const isCurrent = (path) => {
    if (path === '/partners') {
        return page.url === '/partners' || (page.url.startsWith('/partners/') && !page.url.startsWith('/partners/sub-partners'));
    }
    return page.url === path || page.url.startsWith(path + '/') || page.url.startsWith(path + '?');
};

const navItemClass = (active) => {
    const base = 'group flex items-center gap-3 rounded-lg text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500';
    if (props.isCollapsed) {
        return `${base} justify-center p-2.5 ${
            active
                ? 'bg-indigo-600/15 text-indigo-400 font-semibold'
                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
        }`;
    }
    return `${base} px-3 py-2 ${
        active
            ? 'bg-indigo-600/15 text-indigo-400 font-semibold border-l-2 border-indigo-500 -ml-[2px]'
            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
    }`;
};
</script>

<template>
    <!-- Desktop Sidebar Rail / Expanded Panel -->
    <aside
        :class="[
            'hidden lg:flex flex-col bg-slate-900 border-r border-slate-800 transition-all duration-200 select-none z-30 shrink-0 sticky top-0 h-screen',
            isCollapsed ? 'w-16' : 'w-64'
        ]"
        aria-label="Sidebar Navigation"
    >
        <!-- Brand / Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 shrink-0">
            <Link href="/dashboard" class="flex items-center gap-2.5 min-w-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 rounded">
                <img
                    src="/assets/logo.png"
                    alt="Partner Portal"
                    class="w-8 h-8 rounded-lg object-contain shrink-0"
                />
                <div v-if="!isCollapsed" class="min-w-0 truncate">
                    <span class="font-bold text-sm tracking-tight text-white block truncate">Partner Portal</span>
                </div>
            </Link>

            <!-- Role Badge when Expanded -->
            <span
                v-if="!isCollapsed && roleBadgeLabel"
                :class="['px-2 py-0.5 text-[10px] font-medium rounded-full border shrink-0', roleBadgeClass]"
            >
                {{ roleBadgeLabel }}
            </span>
        </div>

        <!-- Navigation Scroll Area -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6 scrollbar-thin">
            <!-- SECTION 1: OVERVIEW -->
            <div>
                <div v-if="!isCollapsed" class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">
                    Overview
                </div>
                <div class="space-y-1">
                    <Link
                        href="/dashboard"
                        :class="navItemClass(isCurrent('/dashboard'))"
                        :title="isCollapsed ? 'Dashboard' : undefined"
                        :aria-current="isCurrent('/dashboard') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Dashboard</span>
                    </Link>
                </div>
            </div>

            <!-- SECTION 2: NETWORK -->
            <div>
                <div v-if="!isCollapsed" class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">
                    Network
                </div>
                <div class="space-y-1">
                    <!-- Partners: Admin sees Partners, Main Partner sees Sub-Partners -->
                    <Link
                        v-if="isAdmin"
                        href="/partners"
                        :class="navItemClass(isCurrent('/partners'))"
                        :title="isCollapsed ? 'Partners' : undefined"
                        :aria-current="isCurrent('/partners') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Partners</span>
                    </Link>

                    <Link
                        v-if="isMainPartner"
                        href="/partners"
                        :class="navItemClass(isCurrent('/partners'))"
                        :title="isCollapsed ? 'Sub-Partners' : undefined"
                        :aria-current="isCurrent('/partners') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Sub-Partners</span>
                    </Link>

                    <Link
                        href="/referral-codes"
                        :class="navItemClass(isCurrent('/referral-codes'))"
                        :title="isCollapsed ? 'Referral Codes' : undefined"
                        :aria-current="isCurrent('/referral-codes') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Referral Codes</span>
                    </Link>

                    <Link
                        href="/leads"
                        :class="navItemClass(isCurrent('/leads'))"
                        :title="isCollapsed ? 'Leads' : undefined"
                        :aria-current="isCurrent('/leads') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Leads</span>
                    </Link>

                    <Link
                        href="/customers"
                        :class="navItemClass(isCurrent('/customers'))"
                        :title="isCollapsed ? 'Customers' : undefined"
                        :aria-current="isCurrent('/customers') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Customers</span>
                    </Link>
                </div>
            </div>

            <!-- SECTION 3: COMMERCE & LIFECYCLE -->
            <div>
                <div v-if="!isCollapsed" class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">
                    Commerce &amp; Lifecycle
                </div>
                <div class="space-y-1">
                    <Link
                        href="/products"
                        :class="navItemClass(isCurrent('/products'))"
                        :title="isCollapsed ? 'Products & Plans' : undefined"
                        :aria-current="isCurrent('/products') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <div v-if="!isCollapsed" class="flex items-center justify-between flex-1 min-w-0">
                            <span class="truncate">Products &amp; Plans</span>
                            <span v-if="isMainPartner || isSubPartner" class="text-[10px] text-slate-500 font-normal ml-1">View</span>
                        </div>
                    </Link>

                    <Link
                        href="/orders"
                        :class="navItemClass(isCurrent('/orders'))"
                        :title="isCollapsed ? 'Orders' : undefined"
                        :aria-current="isCurrent('/orders') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Orders</span>
                    </Link>

                    <Link
                        href="/subscriptions"
                        :class="navItemClass(isCurrent('/subscriptions'))"
                        :title="isCollapsed ? 'Subscriptions' : undefined"
                        :aria-current="isCurrent('/subscriptions') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Subscriptions</span>
                    </Link>

                    <Link
                        v-if="isAdmin"
                        href="/auto-debit-mandates"
                        :class="navItemClass(isCurrent('/auto-debit-mandates'))"
                        :title="isCollapsed ? 'Auto-Debit' : undefined"
                        :aria-current="isCurrent('/auto-debit-mandates') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Auto-Debit</span>
                    </Link>

                    <Link
                        href="/renewals"
                        :class="navItemClass(isCurrent('/renewals'))"
                        :title="isCollapsed ? 'Renewals' : undefined"
                        :aria-current="isCurrent('/renewals') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Renewals</span>
                    </Link>
                </div>
            </div>

            <!-- SECTION 4: SYSTEM & GOVERNANCE -->
            <div>
                <div v-if="!isCollapsed" class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">
                    System &amp; Governance
                </div>
                <div class="space-y-1">
                    <Link
                        href="/notifications"
                        :class="navItemClass(isCurrent('/notifications'))"
                        :title="isCollapsed ? 'Notifications' : undefined"
                        :aria-current="isCurrent('/notifications') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Notifications</span>
                    </Link>

                    <Link
                        href="/reports/sales"
                        :class="navItemClass(isCurrent('/reports'))"
                        :title="isCollapsed ? 'Reports' : undefined"
                        :aria-current="isCurrent('/reports') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div v-if="!isCollapsed" class="flex items-center justify-between flex-1 min-w-0">
                            <span class="truncate">Reports</span>
                            <span class="text-[9px] font-medium bg-slate-800 text-indigo-400 px-1.5 py-0.5 rounded border border-indigo-500/20">Pending</span>
                        </div>
                    </Link>

                    <Link
                        v-if="isAdmin"
                        href="/audit"
                        :class="navItemClass(isCurrent('/audit'))"
                        :title="isCollapsed ? 'Audit' : undefined"
                        :aria-current="isCurrent('/audit') ? 'page' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div v-if="!isCollapsed" class="flex items-center justify-between flex-1 min-w-0">
                            <span class="truncate">Audit</span>
                            <span class="text-[9px] font-medium bg-slate-800 text-amber-400 px-1.5 py-0.5 rounded border border-amber-500/20">Restricted</span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Sidebar Footer / Collapse Toggle -->
        <div class="p-3 border-t border-slate-800 shrink-0">
            <button
                type="button"
                @click="emit('toggle-collapsed')"
                :class="[
                    'w-full flex items-center rounded-lg text-xs font-medium text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500',
                    isCollapsed ? 'justify-center p-2.5' : 'gap-2 px-3 py-2'
                ]"
                :title="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                :aria-label="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
            >
                <svg v-if="!isCollapsed" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
                <span v-if="!isCollapsed">Collapse Sidebar</span>
            </button>
        </div>
    </aside>

    <!-- Mobile Slide-Over Drawer with Backdrop -->
    <div
        v-if="isMobileOpen"
        class="fixed inset-0 z-50 flex lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Mobile Navigation Drawer"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
            @click="emit('close-mobile')"
            aria-hidden="true"
        />

        <!-- Drawer Content Panel -->
        <div class="relative flex flex-col w-72 max-w-[85vw] bg-slate-900 border-r border-slate-800 shadow-2xl h-full select-none z-10">
            <!-- Mobile Drawer Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 shrink-0">
                <Link href="/dashboard" @click="emit('close-mobile')" class="flex items-center gap-2.5 min-w-0">
                    <img
                        src="/assets/logo.png"
                        alt="Partner Portal"
                        class="w-8 h-8 rounded-lg object-contain shrink-0"
                    />
                    <span class="font-bold text-sm text-white truncate">Partner Portal</span>
                </Link>

                <button
                    type="button"
                    @click="emit('close-mobile')"
                    class="p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    aria-label="Close Navigation Drawer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Links -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-6">
                <!-- Overview -->
                <div>
                    <div class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">Overview</div>
                    <div class="space-y-1">
                        <Link href="/dashboard" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/dashboard'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </Link>
                    </div>
                </div>

                <!-- Network -->
                <div>
                    <div class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">Network</div>
                    <div class="space-y-1">
                        <Link v-if="isAdmin" href="/partners" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/partners'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Partners</span>
                        </Link>
                        <Link v-if="isMainPartner" href="/partners" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/partners'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Sub-Partners</span>
                        </Link>
                        <Link href="/referral-codes" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/referral-codes'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>Referral Codes</span>
                        </Link>
                        <Link href="/leads" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/leads'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>Leads</span>
                        </Link>
                        <Link href="/customers" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/customers'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Customers</span>
                        </Link>
                    </div>
                </div>

                <!-- Commerce & Lifecycle -->
                <div>
                    <div class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">Commerce &amp; Lifecycle</div>
                    <div class="space-y-1">
                        <Link href="/products" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/products'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Products &amp; Plans</span>
                        </Link>
                        <Link href="/orders" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/orders'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Orders</span>
                        </Link>
                        <Link href="/subscriptions" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/subscriptions'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Subscriptions</span>
                        </Link>
                        <Link v-if="isAdmin" href="/auto-debit-mandates" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/auto-debit-mandates'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Auto-Debit</span>
                        </Link>
                        <Link href="/renewals" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/renewals'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Renewals</span>
                        </Link>
                    </div>
                </div>

                <!-- System & Governance -->
                <div>
                    <div class="text-[10px] font-semibold tracking-wider text-slate-500 uppercase px-2 mb-2">System &amp; Governance</div>
                    <div class="space-y-1">
                        <Link href="/notifications" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/notifications'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span>Notifications</span>
                        </Link>
                        <Link href="/reports/sales" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/reports'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <div class="flex items-center justify-between flex-1">
                                <span>Reports</span>
                                <span class="text-[9px] font-medium bg-slate-800 text-indigo-400 px-1.5 py-0.5 rounded border border-indigo-500/20">Pending</span>
                            </div>
                        </Link>
                        <Link v-if="isAdmin" href="/audit" @click="emit('close-mobile')" :class="navItemClass(isCurrent('/audit'))">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div class="flex items-center justify-between flex-1">
                                <span>Audit</span>
                                <span class="text-[9px] font-medium bg-slate-800 text-amber-400 px-1.5 py-0.5 rounded border border-amber-500/20">Restricted</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Mobile Drawer User Footer -->
            <div class="p-4 border-t border-slate-800 shrink-0 bg-slate-900/60">
                <div v-if="user" class="mb-3">
                    <div class="text-sm font-medium text-white truncate">{{ user.name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ roleBadgeLabel }}</div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="w-full py-2 px-3 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-md transition-colors cursor-pointer text-center"
                >
                    Sign Out
                </Link>
            </div>
        </div>
    </div>
</template>
