<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import {
    Squares2X2Icon,
    UsersIcon,
    ShareIcon,
    UserPlusIcon,
    UserGroupIcon,
    CubeIcon,
    ShoppingCartIcon,
    ArrowPathIcon,
    CreditCardIcon,
    CalendarDaysIcon,
    BellIcon,
    ChartBarIcon,
    ShieldCheckIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    XMarkIcon,
    Bars3Icon,
    ArrowLeftOnRectangleIcon,
} from '@heroicons/vue/24/outline';

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
        case 'admin': return 'Admin';
        case 'main_partner': return 'Main Partner';
        case 'sub_partner': return 'Sub-Partner';
        default: return role.value || 'User';
    }
});

const roleBadgeClass = computed(() => {
    switch (role.value) {
        case 'admin': return 'bg-blue-100 text-blue-600 border-blue-300';
        case 'main_partner': return 'bg-emerald-100 text-emerald-600 border-emerald-300';
        case 'sub_partner': return 'bg-sky-100 text-sky-600 border-sky-300';
        default: return 'bg-gray-100 text-gray-500 border-gray-300';
    }
});

const isCurrent = (path) => {
    if (path === '/partners') {
        return page.url === '/partners' || (page.url.startsWith('/partners/') && !page.url.startsWith('/partners/sub-partners'));
    }
    return page.url === path || page.url.startsWith(path + '/') || page.url.startsWith(path + '?');
};

const navItemClass = (active) => {
    const base = 'group flex items-center gap-3 rounded-lg text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500';
    if (props.isCollapsed) {
        return `${base} justify-center p-2.5 ${active ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-blue-50'}`;
    }
    return `${base} px-3 py-2 ${active ? 'bg-blue-100 text-blue-700 font-semibold border-l-2 border-blue-500 -ml-[2px]' : 'text-gray-500 hover:text-gray-800 hover:bg-blue-50'}`;
};

const navSections = computed(() => [
    {
        label: 'Overview',
        items: [
            { href: '/dashboard', label: 'Dashboard', icon: Squares2X2Icon, show: true },
        ],
    },
    {
        label: 'Network',
        items: [
            { href: '/partners', label: 'Partners', icon: UsersIcon, show: isAdmin.value },
            { href: '/partners', label: 'Sub-Partners', icon: UsersIcon, show: isMainPartner.value },
            { href: '/referral-codes', label: 'Referral Codes', icon: ShareIcon, show: true },
            { href: '/leads', label: 'Leads', icon: UserPlusIcon, show: true },
            { href: '/customers', label: 'Customers', icon: UserGroupIcon, show: true },
        ],
    },
    {
        label: 'Commerce & Lifecycle',
        items: [
            { href: '/products', label: 'Products & Plans', icon: CubeIcon, show: true, badge: (isMainPartner.value || isSubPartner.value) ? 'View' : null },
            { href: '/orders', label: 'Orders', icon: ShoppingCartIcon, show: true },
            { href: '/subscriptions', label: 'Subscriptions', icon: ArrowPathIcon, show: true },
            { href: '/auto-debit-mandates', label: 'Auto-Debit', icon: CreditCardIcon, show: isAdmin.value },
            { href: '/renewals', label: 'Renewals', icon: CalendarDaysIcon, show: true },
        ],
    },
    {
        label: 'System & Governance',
        items: [
            { href: '/reports/sales', label: 'Reports', icon: ChartBarIcon, show: true, badge: 'Pending', badgeClass: 'bg-blue-100 text-blue-600 border-blue-300' },
            { href: '/audit', label: 'Audit', icon: ShieldCheckIcon, show: isAdmin.value, badge: 'Restricted', badgeClass: 'bg-amber-50 text-amber-600 border-amber-300' },
        ],
    },
]);

const activeSection = (section) => {
    if (section.label === 'Overview') return isCurrent('/dashboard');
    if (section.label === 'Network') return ['/partners', '/referral-codes', '/leads', '/customers'].some(isCurrent);
    if (section.label === 'Commerce & Lifecycle') return ['/products', '/orders', '/subscriptions', '/auto-debit-mandates', '/renewals'].some(isCurrent);
    if (section.label === 'System & Governance') return ['/notifications', '/reports', '/audit'].some(isCurrent);
    return false;
};
</script>

<template>
    <!-- ══════════════════════════════════════════ -->
    <!-- Desktop Sidebar                           -->
    <!-- ══════════════════════════════════════════ -->
    <aside
        :class="['hidden lg:flex flex-col bg-white border-r border-blue-100 transition-all duration-200 select-none z-30 shrink-0 sticky top-0 h-screen', isCollapsed ? 'w-16' : 'w-64']"
        aria-label="Sidebar Navigation"
    >
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-blue-100 shrink-0">
            <Link href="/dashboard" class="flex items-center gap-2.5 min-w-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded">
                <img src="/assets/logo.png" alt="Partner Portal" class="w-8 h-8 rounded-lg object-contain shrink-0" />
                <div v-if="!isCollapsed" class="min-w-0 truncate">
                    <span class="font-bold text-sm tracking-tight text-gray-900 block truncate">Partner Portal</span>
                </div>
            </Link>
            <span v-if="!isCollapsed && roleBadgeLabel" :class="['px-2 py-0.5 text-[10px] font-medium rounded-full border shrink-0', roleBadgeClass]">
                {{ roleBadgeLabel }}
            </span>
        </div>

        <!-- Nav Scroll Area -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 scrollbar-thin">
            <template v-for="section in navSections" :key="section.label">
                <div>
                    <div v-if="!isCollapsed" class="text-[10px] font-semibold tracking-wider text-blue-400 uppercase px-2 mb-2">
                        {{ section.label }}
                    </div>
                    <div class="space-y-0.5">
                        <template v-for="item in section.items" :key="item.href + item.label">
                            <Link
                                v-if="item.show"
                                :href="item.href"
                                :class="navItemClass(isCurrent(item.href))"
                                :title="isCollapsed ? item.label : undefined"
                                :aria-current="isCurrent(item.href) ? 'page' : undefined"
                            >
                                <component :is="item.icon" class="w-5 h-5 shrink-0" />
                                <template v-if="!isCollapsed">
                                    <div class="flex items-center justify-between flex-1 min-w-0">
                                        <span class="truncate">{{ item.label }}</span>
                                        <span v-if="item.badge" :class="['text-[9px] font-medium px-1.5 py-0.5 rounded border ml-1 shrink-0', item.badgeClass || 'bg-blue-50 text-blue-500 border-blue-200']">
                                            {{ item.badge }}
                                        </span>
                                    </div>
                                </template>
                            </Link>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Collapse Toggle -->
        <div class="p-3 border-t border-blue-100 shrink-0">
            <button
                type="button"
                @click="emit('toggle-collapsed')"
                :class="['w-full flex items-center rounded-lg text-xs font-medium text-gray-500 hover:text-gray-800 hover:bg-blue-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500', isCollapsed ? 'justify-center p-2.5' : 'gap-2 px-3 py-2']"
                :title="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
                :aria-label="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
            >
                <ChevronDoubleLeftIcon v-if="!isCollapsed" class="w-4 h-4 shrink-0" />
                <ChevronDoubleRightIcon v-else class="w-4 h-4 shrink-0" />
                <span v-if="!isCollapsed">Collapse Sidebar</span>
            </button>
        </div>
    </aside>

    <!-- ══════════════════════════════════════════ -->
    <!-- Mobile Drawer                             -->
    <!-- ══════════════════════════════════════════ -->
    <div v-if="isMobileOpen" class="fixed inset-0 z-50 flex lg:hidden" role="dialog" aria-modal="true" aria-label="Mobile Navigation Drawer">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-blue-900/40 backdrop-blur-sm" @click="emit('close-mobile')" aria-hidden="true" />

        <!-- Drawer Panel -->
        <div class="relative flex flex-col w-72 max-w-[85vw] bg-white border-r border-blue-100 shadow-2xl h-full select-none z-10">
            <!-- Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-blue-100 shrink-0">
                <Link href="/dashboard" @click="emit('close-mobile')" class="flex items-center gap-2.5 min-w-0">
                    <img src="/assets/logo.png" alt="Partner Portal" class="w-8 h-8 rounded-lg object-contain shrink-0" />
                    <span class="font-bold text-sm text-gray-900 truncate">Partner Portal</span>
                </Link>
                <button type="button" @click="emit('close-mobile')" class="p-2 rounded-md text-gray-500 hover:text-gray-800 hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" aria-label="Close Navigation Drawer">
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Nav Links -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-5">
                <template v-for="section in navSections" :key="section.label">
                    <div>
                        <div class="text-[10px] font-semibold tracking-wider text-blue-400 uppercase px-2 mb-2">{{ section.label }}</div>
                        <div class="space-y-0.5">
                            <template v-for="item in section.items" :key="item.href + item.label">
                                <Link
                                    v-if="item.show"
                                    :href="item.href"
                                    @click="emit('close-mobile')"
                                    :class="navItemClass(isCurrent(item.href))"
                                    :aria-current="isCurrent(item.href) ? 'page' : undefined"
                                >
                                    <component :is="item.icon" class="w-5 h-5 shrink-0" />
                                    <div class="flex items-center justify-between flex-1 min-w-0">
                                        <span class="truncate">{{ item.label }}</span>
                                        <span v-if="item.badge" :class="['text-[9px] font-medium px-1.5 py-0.5 rounded border ml-1 shrink-0', item.badgeClass || 'bg-blue-50 text-blue-500 border-blue-200']">
                                            {{ item.badge }}
                                        </span>
                                    </div>
                                </Link>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- User Footer -->
            <div class="p-4 border-t border-blue-100 shrink-0 bg-blue-50">
                <div v-if="user" class="mb-3">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</div>
                    <div class="text-xs text-blue-500 truncate">{{ roleBadgeLabel }}</div>
                </div>
                <Link href="/logout" method="post" as="button" class="w-full flex items-center justify-center gap-2 py-2 px-3 text-xs font-medium text-gray-600 hover:text-gray-900 bg-white hover:bg-blue-50 border border-blue-200 rounded-md transition-colors cursor-pointer">
                    <ArrowLeftOnRectangleIcon class="w-4 h-4" />
                    Sign Out
                </Link>
            </div>
        </div>
    </div>
</template>
