<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import {
    BellIcon,
    MagnifyingGlassIcon,
    Bars3Icon,
    ChevronRightIcon,
    ChevronDownIcon,
    ArrowRightOnRectangleIcon,
    UserIcon,
    Cog6ToothIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

const emit = defineEmits(['toggle-mobile']);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role);

const isAdmin = computed(() => role.value === 'admin');

const isNotificationOpen = ref(false);
const isProfileOpen = ref(false);

const notifications = ref([
    {
        id: 1,
        title: 'New Lead Assigned',
        description: 'TechCorp Inc. signed up using your referral code.',
        time: '10m ago',
        unread: true,
    },
    {
        id: 2,
        title: 'Commission Payout Processed',
        description: '$450.00 transferred for Q3 referrals.',
        time: '1h ago',
        unread: true,
    },
    {
        id: 3,
        title: 'Subscription Renewed',
        description: 'Global Logistics renewed Enterprise Annual Plan.',
        time: '4h ago',
        unread: false,
    },
]);

const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);

const toggleNotification = (e) => {
    e.stopPropagation();
    isNotificationOpen.value = !isNotificationOpen.value;
    if (isNotificationOpen.value) isProfileOpen.value = false;
};

const toggleProfile = (e) => {
    e.stopPropagation();
    isProfileOpen.value = !isProfileOpen.value;
    if (isProfileOpen.value) isNotificationOpen.value = false;
};

const markAllRead = () => {
    notifications.value.forEach(n => n.unread = false);
};

const closeDropdowns = (e) => {
    if (!e.target.closest('.notification-dropdown-container')) {
        isNotificationOpen.value = false;
    }
    if (!e.target.closest('.profile-dropdown-container')) {
        isProfileOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeDropdowns);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns);
});

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
    if (url.startsWith('/reports')) return { section: 'System & Governance', title: 'Reports' };
    if (url.startsWith('/audit')) return { section: 'System & Governance', title: 'Audit' };
    return { section: 'Portal', title: 'Dashboard' };
});
</script>

<template>
    <header class="h-16 bg-white/95 backdrop-blur-xl border-b border-gray-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 xl:px-10 transition-all duration-200">
        <!-- Left: Mobile Toggle + Breadcrumbs & Title -->
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                @click="emit('toggle-mobile')"
                class="lg:hidden p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-colors"
                aria-label="Toggle Navigation Drawer"
            >
                <Bars3Icon class="w-5 h-5" />
            </button>

            <!-- Contextual Breadcrumbs & Title -->
            <div class="min-w-0">
                <nav v-if="breadcrumb.section" aria-label="Breadcrumb" class="hidden sm:flex items-center gap-1.5 text-xs text-gray-500 font-medium">
                    <span class="hover:text-blue-600 transition-colors">{{ breadcrumb.section }}</span>
                    <ChevronRightIcon class="w-3 h-3 text-gray-400 shrink-0" />
                    <span class="text-gray-900 font-semibold truncate">{{ breadcrumb.title }}</span>
                </nav>
                <h1 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight truncate leading-tight">
                    {{ breadcrumb.title }}
                </h1>
            </div>
        </div>

        <!-- Middle: Global Quick Search Input -->
        <div class="hidden md:flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-gray-50 border border-gray-200/80 text-gray-400 text-xs w-64 lg:w-80 focus-within:bg-white focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/15 transition-all">
            <MagnifyingGlassIcon class="w-4 h-4 text-gray-400 shrink-0" />
            <input
                type="text"
                placeholder="Search portal, partners, orders..."
                class="w-full bg-transparent border-0 p-0 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0"
            />
            <kbd class="hidden lg:inline-block px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 bg-gray-200/60 rounded border border-gray-300/60">⌘K</kbd>
        </div>

        <!-- Right: Notifications & User Profile Dropdowns -->
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">

            <!-- 1. NOTIFICATIONS DROPDOWN -->
            <div class="relative notification-dropdown-container">
                <button
                    type="button"
                    @click="toggleNotification"
                    class="relative p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 cursor-pointer"
                    aria-label="View Notifications"
                >
                    <BellIcon class="w-5 h-5" />
                    <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-blue-600 ring-2 ring-white animate-pulse"></span>
                </button>

                <!-- Notifications Dropdown Panel -->
                <Transition
                    enter-active-class="transition ease-out duration-150"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-100"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                >
                    <div
                        v-if="isNotificationOpen"
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-200/80 overflow-hidden z-50 focus:outline-none"
                    >
                        <!-- Header -->
                        <div class="px-4 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-gray-900">Notifications</span>
                                <span v-if="unreadCount > 0" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">
                                    {{ unreadCount }} new
                                </span>
                            </div>
                            <button
                                v-if="unreadCount > 0"
                                @click="markAllRead"
                                class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors cursor-pointer"
                            >
                                Mark all read
                            </button>
                        </div>

                        <!-- List -->
                        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                            <div
                                v-for="n in notifications"
                                :key="n.id"
                                :class="['p-3.5 flex gap-3 hover:bg-gray-50 transition-colors cursor-pointer', n.unread ? 'bg-blue-50/40' : '']"
                            >
                                <div class="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center text-blue-600 bg-blue-100/70 mt-0.5">
                                    <SparklesIcon class="w-4 h-4" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-gray-900 truncate">{{ n.title }}</span>
                                        <span class="text-[10px] text-gray-400 shrink-0 ml-2">{{ n.time }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5 leading-snug line-clamp-2">{{ n.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-2.5 bg-gray-50/80 border-t border-gray-100 text-center">
                            <span class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors cursor-pointer block py-1">
                                View all notifications
                            </span>
                        </div>
                    </div>
                </Transition>
            </div>

            <div class="h-5 w-px bg-gray-200 hidden sm:block" aria-hidden="true" />

            <!-- 2. USER PROFILE DROPDOWN MENU -->
            <div v-if="user" class="relative profile-dropdown-container">
                <button
                    type="button"
                    @click="toggleProfile"
                    class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-gray-100/80 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 cursor-pointer"
                    aria-label="User Menu"
                >
                    <!-- User Avatar / Initials with Status Ring -->
                    <div class="relative shrink-0">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center ring-2 ring-blue-500/20">
                            {{ userInitials }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    </div>

                    <!-- User Name & Role Pill (Desktop) -->
                    <div class="hidden md:flex flex-col text-left min-w-0 max-w-[150px]">
                        <div class="text-xs font-bold text-gray-900 truncate leading-tight">{{ user.name }}</div>
                        <div class="text-[10px] font-semibold text-blue-700 bg-blue-50 border border-blue-200/70 px-1.5 py-0.5 rounded-md w-fit mt-0.5 truncate">
                            {{ roleLabel }}
                        </div>
                    </div>

                    <ChevronDownIcon :class="['w-4 h-4 text-gray-400 transition-transform duration-200', isProfileOpen ? 'rotate-180' : '']" />
                </button>

                <!-- Profile Dropdown Panel -->
                <Transition
                    enter-active-class="transition ease-out duration-150"
                    enter-from-class="transform opacity-0 scale-95"
                    enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-100"
                    leave-from-class="transform opacity-100 scale-100"
                    leave-to-class="transform opacity-0 scale-95"
                >
                    <div
                        v-if="isProfileOpen"
                        class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-200/80 py-2 z-50 focus:outline-none"
                    >
                        <!-- User Card Header -->
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                            <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Signed in as</p>
                            <p class="text-sm font-bold text-gray-900 truncate mt-0.5">{{ user.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                            <div class="mt-2 inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">
                                {{ roleLabel }}
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-1">
                            <button
                                type="button"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors text-left cursor-pointer"
                            >
                                <UserIcon class="w-4 h-4 text-gray-400" />
                                <span>Profile &amp; Account</span>
                            </button>
                            <button
                                type="button"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors text-left cursor-pointer"
                            >
                                <Cog6ToothIcon class="w-4 h-4 text-gray-400" />
                                <span>Partner Settings</span>
                            </button>
                        </div>

                        <div class="border-t border-gray-100 my-1" />

                        <!-- Sign Out Action -->
                        <div class="px-1">
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-xl transition-colors text-left cursor-pointer"
                            >
                                <ArrowRightOnRectangleIcon class="w-4 h-4 text-red-500" />
                                <span>Sign Out</span>
                            </Link>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>
