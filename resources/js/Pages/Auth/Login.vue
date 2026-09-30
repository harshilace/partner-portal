<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import {
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    ArrowRightIcon,
    ShieldCheckIcon,
    LockClosedIcon as LockIcon,
    ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline';
import { ExclamationCircleIcon } from '@heroicons/vue/24/solid';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In — Partner Portal" />

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Welcome back 👋</h1>
            <p class="text-gray-500 text-sm mt-1.5">Sign in to your partner account to continue.</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" novalidate>

            <!-- Email -->
            <div class="mb-5">
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Email address
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
                        autofocus
                        :class="[
                            'w-full pl-10 pr-4 py-3 rounded-xl border text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 outline-none',
                            form.errors.email
                                ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/15'
                                : 'border-gray-200 bg-gray-50 hover:border-blue-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/15'
                        ]"
                        placeholder="you@example.com"
                    />
                </div>
                <div v-if="form.errors.email" class="mt-2 flex items-center gap-1.5 text-xs text-red-500 font-medium">
                    <ExclamationCircleIcon class="w-4 h-4 shrink-0 text-red-500" />
                    {{ form.errors.email }}
                </div>
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <LockClosedIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        :class="[
                            'w-full pl-10 pr-11 py-3 rounded-xl border text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 outline-none',
                            form.errors.password
                                ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-500/15'
                                : 'border-gray-200 bg-gray-50 hover:border-blue-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/15'
                        ]"
                        placeholder="••••••••"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-blue-500 transition-colors"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    >
                        <EyeSlashIcon v-if="showPassword" class="w-4 h-4" />
                        <EyeIcon v-else class="w-4 h-4" />
                    </button>
                </div>
                <div v-if="form.errors.password" class="mt-2 flex items-center gap-1.5 text-xs text-red-500 font-medium">
                    <ExclamationCircleIcon class="w-4 h-4 shrink-0 text-red-500" />
                    {{ form.errors.password }}
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="relative w-full py-3 px-5 rounded-xl text-sm font-semibold text-white transition-all duration-200 outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 overflow-hidden group"
                style="background: linear-gradient(135deg, #1d4ed8, #2563eb);"
                :style="form.processing ? 'opacity: 0.7; cursor: not-allowed;' : ''"
            >
                <span class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-xl" />
                <span class="relative flex items-center justify-center gap-2">
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    <span>{{ form.processing ? 'Signing in…' : 'Sign In' }}</span>
                    <ArrowRightIcon v-if="!form.processing" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform duration-200" />
                </span>
            </button>

            <!-- Divider -->
            <div class="my-6 flex items-center gap-3">
                <div class="flex-1 h-px bg-gray-200" />
                <span class="text-xs text-gray-400 font-medium">New to Partner Portal?</span>
                <div class="flex-1 h-px bg-gray-200" />
            </div>

            <!-- Register Link -->
            <Link
                href="/register"
                class="flex items-center justify-center gap-2 w-full py-3 px-5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:border-blue-300 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
            >
                Register with a referral code
            </Link>

        </form>

        <!-- Trust badges -->
        <div class="mt-8 flex items-center justify-center gap-5">
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <ShieldCheckIcon class="w-3.5 h-3.5 text-green-500" />
                <span>256-bit SSL</span>
            </div>
            <div class="w-px h-3 bg-gray-200" />
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <LockIcon class="w-3.5 h-3.5 text-blue-400" />
                <span>Role-based access</span>
            </div>
            <div class="w-px h-3 bg-gray-200" />
            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                <ClipboardDocumentCheckIcon class="w-3.5 h-3.5 text-blue-400" />
                <span>Audit trail</span>
            </div>
        </div>

    </GuestLayout>
</template>
