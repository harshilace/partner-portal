<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import { SparklesIcon, XMarkIcon, BellIcon, CheckIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
    unread_count: {
        type: Number,
        default: 0,
    },
});

const defaultNotifications = [
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
];

const list = ref(props.notifications && props.notifications.length > 0 ? [...props.notifications] : defaultNotifications);

const unreadCount = computed(() => list.value.filter(n => n.unread).length);

const clearNotification = (id) => {
    list.value = list.value.filter(n => n.id !== id);
};

const clearAllNotifications = () => {
    list.value = [];
};

const markAllRead = () => {
    list.value.forEach(n => n.unread = false);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Notifications" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">Notifications</h1>
                <p class="text-slate-400 text-sm mt-1">Manage and view your system notifications</p>
            </div>
            <div class="flex items-center gap-3">
                <button
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="px-3.5 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <CheckIcon class="w-4 h-4" />
                    Mark all read
                </button>
                <button
                    v-if="list.length > 0"
                    @click="clearAllNotifications"
                    class="px-3.5 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <TrashIcon class="w-4 h-4" />
                    Clear all notifications
                </button>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-slate-700 bg-slate-800/50">
                <span class="text-sm font-semibold text-slate-200">Notification Activity</span>
                <span v-if="unreadCount > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                    {{ unreadCount }} unread
                </span>
            </div>

            <div v-if="list.length > 0" class="divide-y divide-slate-700/60">
                <div
                    v-for="n in list"
                    :key="n.id"
                    :class="['p-4 flex items-start gap-4 transition-colors group relative', n.unread ? 'bg-slate-700/30' : 'hover:bg-slate-700/20']"
                >
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-blue-400 bg-blue-500/10 border border-blue-500/20">
                        <SparklesIcon class="w-5 h-5" />
                    </div>
                    <div class="flex-1 min-w-0 pr-8">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-200">{{ n.title }}</h3>
                            <span class="text-xs text-slate-400">{{ n.time }}</span>
                        </div>
                        <p class="text-sm text-slate-400 mt-1 leading-relaxed">{{ n.description }}</p>
                    </div>
                    <button
                        type="button"
                        @click="clearNotification(n.id)"
                        class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded-lg transition-colors absolute right-4 top-4 cursor-pointer"
                        title="Clear notification"
                    >
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <div v-else class="py-16 text-center text-slate-400">
                <BellIcon class="w-12 h-12 mx-auto mb-3 text-slate-600 stroke-1" />
                <p class="text-base font-medium text-slate-300">No notifications found</p>
                <p class="text-xs text-slate-500 mt-1">You have cleared all your notifications.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
