<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

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
        <Head title="Sign In" />

        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold tracking-tight text-white">Sign In</h2>
            <p class="text-xs text-slate-400 mt-1">Enter your credentials to access your portal</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Email Address
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                    autofocus
                    class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm transition-all focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 hover:border-slate-700"
                    placeholder="user@example.com"
                />
                <div v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ form.errors.email }}</span>
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Password
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm transition-all focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 hover:border-slate-700"
                    placeholder="••••••••"
                />
                <div v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ form.errors.password }}</span>
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl text-sm shadow-md shadow-indigo-600/20 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 cursor-pointer"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign In</span>
                </button>
            </div>

            <div class="text-center pt-2">
                <Link
                    href="/register"
                    class="text-xs text-slate-400 hover:text-indigo-400 font-medium transition-colors focus-visible:outline-none focus-visible:underline"
                >
                    Need an account? Register with referral code
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
