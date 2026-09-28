<script setup>
import { ref, computed } from 'vue';
import {
    CurrencyDollarIcon,
    ArrowPathIcon,
    UserPlusIcon,
    UserGroupIcon,
    ShareIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    data: { type: Object, default: () => ({}) },
    pending_confirmation: { type: Array, default: () => [] },
});

const tabs = ['Overview'];
const activeTab = ref('Overview');

const kpis = computed(() => props.data?.kpis ?? {});

const fmt    = (n) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(n ?? 0);
const fmtNum = (n) => Number(n ?? 0).toLocaleString();

const kpiCards = computed(() => [
    { label: 'Revenue', value: fmt(kpis.value.total_revenue), icon: CurrencyDollarIcon, color: 'text-emerald-600', bg: 'bg-emerald-100', desc: 'Total earnings' },
    { label: 'Orders', value: fmtNum(kpis.value.total_orders), icon: ArrowPathIcon, color: 'text-blue-600', bg: 'bg-blue-100', desc: 'This period' },
    { label: 'Leads', value: fmtNum(kpis.value.total_leads), icon: UserPlusIcon, color: 'text-amber-600', bg: 'bg-amber-100', desc: `${kpis.value.converted_leads ?? 0} converted` },
    { label: 'Customers', value: fmtNum(kpis.value.total_customers), icon: UserGroupIcon, color: 'text-purple-600', bg: 'bg-purple-100', desc: 'Attributed to you' },
    { label: 'Referral Codes', value: fmtNum(kpis.value.referral_codes), icon: ShareIcon, color: 'text-sky-600', bg: 'bg-sky-100', desc: 'Active codes' },
]);
</script>

<template>
    <div class="space-y-6">
        <!-- Tab Selector -->
        <div class="flex items-center gap-1 p-1 bg-gray-100/80 rounded-xl w-fit">
            <button
                v-for="tab in tabs"
                :key="tab"
                @click="activeTab = tab"
                :class="['px-4 py-2 text-sm font-medium rounded-lg transition-all cursor-pointer', activeTab === tab ? 'bg-white text-sky-700 shadow-sm font-semibold' : 'text-gray-500 hover:text-gray-800']"
            >
                {{ tab }}
            </button>
        </div>

        <!-- Overview -->
        <div v-if="activeTab === 'Overview'" class="space-y-5">
            <!-- KPI Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div
                    v-for="card in kpiCards"
                    :key="card.label"
                    class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-4 py-4 hover:shadow-md hover:border-sky-200/60 transition-all"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <span :class="['w-9 h-9 rounded-xl flex items-center justify-center', card.bg]">
                            <component :is="card.icon" :class="['w-5 h-5', card.color]" />
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">{{ card.label }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ card.value }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">{{ card.desc }}</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="/leads" class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-5 py-4 hover:shadow-md hover:border-amber-200/60 transition-all flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                        <UserPlusIcon class="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-amber-700 transition-colors">View Leads</p>
                        <p class="text-xs text-gray-400">Track & manage leads</p>
                    </div>
                </a>
                <a href="/orders" class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-5 py-4 hover:shadow-md hover:border-blue-200/60 transition-all flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                        <ArrowPathIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">View Orders</p>
                        <p class="text-xs text-gray-400">Sales & payments</p>
                    </div>
                </a>
                <a href="/referral-codes" class="bg-white rounded-2xl border border-gray-200/80 shadow-sm px-5 py-4 hover:shadow-md hover:border-sky-200/60 transition-all flex items-center gap-4 group">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center shrink-0">
                        <ShareIcon class="w-5 h-5 text-sky-600" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-sky-700 transition-colors">Referral Codes</p>
                        <p class="text-xs text-gray-400">Manage codes</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</template>
