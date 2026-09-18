<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import NavLink from './NavLink.vue';

const page = usePage();
const mobileMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role);

const isAdmin = computed(() => role.value === 'admin');
const isMainPartner = computed(() => role.value === 'main_partner');
const isSubPartner = computed(() => role.value === 'sub_partner');

const roleLabel = computed(() => {
    switch (role.value) {
        case 'admin':
            return 'Admin';
        case 'main_partner':
            return 'Main Partner';
        case 'sub_partner':
            return 'Sub-Partner';
        default:
            return role.value || '';
    }
});

const isCurrent = (path) => {
    return page.url === path || page.url.startsWith(path + '/');
};
</script>

<template>
    <nav class="bg-slate-900 border-b border-slate-800" aria-label="Main Navigation">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand and Desktop Navigation Links -->
                <div class="flex items-center gap-6">
                    <Link href="/dashboard" class="flex items-center gap-2 text-white font-bold tracking-tight text-lg">
                        <span>Partner Portal</span>
                    </Link>

                    <div class="hidden md:flex items-center space-x-1">
                        <NavLink href="/dashboard" :active="isCurrent('/dashboard')">
                            Dashboard
                        </NavLink>

                        <!-- Admin Only: Partner Management -->
                        <NavLink v-if="isAdmin" href="/partners" :active="isCurrent('/partners') && !isCurrent('/partners/sub-partners')">
                            Partners
                        </NavLink>

                        <!-- Main Partner Only: Sub-Partner Management -->
                        <NavLink v-if="isMainPartner" href="/partners" :active="isCurrent('/partners')">
                            Sub-Partners
                        </NavLink>

                        <!-- Products & Plans: Admin manages, Main/Sub-Partner active read-only access -->
                        <NavLink v-if="isAdmin || isMainPartner || isSubPartner" href="/products" :active="isCurrent('/products')">
                            Products &amp; Plans
                            <span v-if="isMainPartner || isSubPartner" class="ml-1 text-[10px] text-slate-400 font-normal">(Read-Only)</span>
                        </NavLink>

                        <NavLink href="/referral-codes" :active="isCurrent('/referral-codes')">
                            Referrals
                        </NavLink>

                        <NavLink href="/leads" :active="isCurrent('/leads')">
                            Leads
                        </NavLink>

                        <NavLink href="/customers" :active="isCurrent('/customers')">
                            Customers
                        </NavLink>

                        <NavLink href="/orders" :active="isCurrent('/orders')">
                            Orders
                        </NavLink>

                        <NavLink href="/subscriptions" :active="isCurrent('/subscriptions')">
                            Subscriptions
                        </NavLink>

                        <!-- Admin Only: Auto-Debit Mandate Management -->
                        <NavLink v-if="isAdmin" href="/auto-debit-mandates" :active="isCurrent('/auto-debit-mandates')">
                            Auto-Debit
                        </NavLink>

                        <NavLink href="/renewals" :active="isCurrent('/renewals')">
                            Renewals
                        </NavLink>

                        <NavLink href="/notifications" :active="isCurrent('/notifications')">
                            Notifications
                        </NavLink>

                        <NavLink href="/reports/sales" :active="isCurrent('/reports')">
                            Reports
                        </NavLink>

                        <!-- Admin Only: Audit History (Main/Sub-Partner temporary deny pending BC-11-01) -->
                        <NavLink v-if="isAdmin" href="/audit" :active="isCurrent('/audit')">
                            Audit
                        </NavLink>
                    </div>
                </div>

                <!-- User Profile & Session Controls -->
                <div class="hidden md:flex items-center gap-4">
                    <div v-if="user" class="text-right">
                        <div class="text-sm font-medium text-white">{{ user.name }}</div>
                        <div class="text-xs text-slate-400">{{ roleLabel }}</div>
                    </div>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="px-3 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-md transition-colors cursor-pointer"
                    >
                        Sign Out
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center">
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none"
                        aria-label="Toggle Navigation Menu"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-slate-800 px-4 pt-2 pb-4 space-y-1 bg-slate-900">
            <NavLink href="/dashboard" :active="isCurrent('/dashboard')" class="block w-full">
                Dashboard
            </NavLink>

            <NavLink v-if="isAdmin" href="/partners" :active="isCurrent('/partners')" class="block w-full">
                Partners
            </NavLink>

            <NavLink v-if="isMainPartner" href="/partners" :active="isCurrent('/partners')" class="block w-full">
                Sub-Partners
            </NavLink>

            <NavLink v-if="isAdmin || isMainPartner || isSubPartner" href="/products" :active="isCurrent('/products')" class="block w-full">
                Products &amp; Plans
                <span v-if="isMainPartner || isSubPartner" class="text-xs text-slate-400">(Read-Only)</span>
            </NavLink>

            <NavLink href="/referral-codes" :active="isCurrent('/referral-codes')" class="block w-full">
                Referrals
            </NavLink>

            <NavLink href="/leads" :active="isCurrent('/leads')" class="block w-full">
                Leads
            </NavLink>

            <NavLink href="/customers" :active="isCurrent('/customers')" class="block w-full">
                Customers
            </NavLink>

            <NavLink href="/orders" :active="isCurrent('/orders')" class="block w-full">
                Orders
            </NavLink>

            <NavLink href="/subscriptions" :active="isCurrent('/subscriptions')" class="block w-full">
                Subscriptions
            </NavLink>

            <NavLink v-if="isAdmin" href="/auto-debit-mandates" :active="isCurrent('/auto-debit-mandates')" class="block w-full">
                Auto-Debit
            </NavLink>

            <NavLink href="/renewals" :active="isCurrent('/renewals')" class="block w-full">
                Renewals
            </NavLink>

            <NavLink href="/notifications" :active="isCurrent('/notifications')" class="block w-full">
                Notifications
            </NavLink>

            <NavLink href="/reports/sales" :active="isCurrent('/reports')" class="block w-full">
                Reports
            </NavLink>

            <NavLink v-if="isAdmin" href="/audit" :active="isCurrent('/audit')" class="block w-full">
                Audit
            </NavLink>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <div v-if="user">
                    <div class="text-sm font-medium text-white">{{ user.name }}</div>
                    <div class="text-xs text-slate-400">{{ roleLabel }}</div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="px-3 py-1.5 text-xs font-medium text-slate-300 hover:text-white bg-slate-800 rounded-md"
                >
                    Sign Out
                </Link>
            </div>
        </div>
    </nav>
</template>
