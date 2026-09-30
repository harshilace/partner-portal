<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import GuestLayout from '../Layouts/GuestLayout.vue';
import { UserIcon, EnvelopeIcon, PhoneIcon, TagIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    partner: {
        type: String,
        default: '',
    },
});

const page = usePage();

const form = useForm({
    name: '',
    email: '',
    mobile: '',
    partner: props.partner || '',
});

const submit = () => {
    form.post('/register', {
        onSuccess: () => {
            form.reset('name', 'email', 'mobile');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register — Partner Portal" />

        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Create Account</h1>
            <p class="text-gray-500 text-sm mt-1.5">Register via Partner Referral or Public Access</p>
        </div>

        <div
            v-if="page.props.flash?.success || page.props.flash?.message"
            class="mb-6 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center gap-2.5 font-medium shadow-2xs"
        >
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ page.props.flash?.success || page.props.flash?.message }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Full Name
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <UserIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="name"
                        autofocus
                        :class="[
                            'w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 transition-all outline-none',
                            form.errors.name
                                ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/15'
                                : 'border-gray-200 bg-gray-50 hover:border-blue-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/15'
                        ]"
                        placeholder="Jane Doe"
                    />
                </div>
                <div v-if="form.errors.name" class="mt-1.5 flex items-center gap-1 text-xs text-red-500 font-medium">
                    <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0 text-red-500" />
                    <span>{{ form.errors.name }}</span>
                </div>
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <EnvelopeIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        :class="[
                            'w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 transition-all outline-none',
                            form.errors.email
                                ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/15'
                                : 'border-gray-200 bg-gray-50 hover:border-blue-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/15'
                        ]"
                        placeholder="jane@example.com"
                    />
                </div>
                <div v-if="form.errors.email" class="mt-1.5 flex items-center gap-1 text-xs text-red-500 font-medium">
                    <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0 text-red-500" />
                    <span>{{ form.errors.email }}</span>
                </div>
            </div>

            <!-- Mobile Number -->
            <div>
                <label for="mobile" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                    Mobile Number
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <PhoneIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        id="mobile"
                        v-model="form.mobile"
                        type="tel"
                        required
                        autocomplete="tel"
                        :class="[
                            'w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 transition-all outline-none',
                            form.errors.mobile
                                ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/15'
                                : 'border-gray-200 bg-gray-50 hover:border-blue-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/15'
                        ]"
                        placeholder="9876543210"
                    />
                </div>
                <div v-if="form.errors.mobile" class="mt-1.5 flex items-center gap-1 text-xs text-red-500 font-medium">
                    <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0 text-red-500" />
                    <span>{{ form.errors.mobile }}</span>
                </div>
            </div>

            <!-- Referral Code -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="partner" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Referral Code
                    </label>
                    <span v-if="props.partner" class="text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                        Pre-filled
                    </span>
                    <span v-else class="text-[11px] text-gray-400 font-normal">
                        Optional
                    </span>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <TagIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        id="partner"
                        v-model="form.partner"
                        type="text"
                        :class="[
                            'w-full pl-10 pr-4 py-2.5 rounded-xl border text-sm text-gray-900 placeholder-gray-400 transition-all outline-none',
                            form.errors.partner
                                ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/15'
                                : 'border-gray-200 bg-gray-50 hover:border-blue-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/15'
                        ]"
                        placeholder="Optional partner referral code"
                    />
                </div>
                <div v-if="form.errors.partner" class="mt-1.5 flex items-center gap-1 text-xs text-red-500 font-medium">
                    <ExclamationCircleIcon class="w-3.5 h-3.5 shrink-0 text-red-500" />
                    <span>{{ form.errors.partner }}</span>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl text-sm shadow-sm shadow-blue-600/20 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/15 cursor-pointer"
                >
                    <span v-if="form.processing">Registering...</span>
                    <span v-else>Register</span>
                </button>
            </div>

            <div class="text-center pt-2">
                <Link
                    href="/login"
                    class="text-xs text-gray-500 hover:text-blue-600 font-medium transition-colors"
                >
                    Already registered? Sign in here
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
