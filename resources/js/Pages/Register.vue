<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import GuestLayout from '../Layouts/GuestLayout.vue';

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
        <Head title="Register" />

        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold tracking-tight text-white">Create Account</h2>
            <p class="text-xs text-slate-400 mt-1">Register via Partner Referral or Public Access</p>
        </div>

        <div
            v-if="page.props.flash?.success || page.props.flash?.message"
            class="mb-6 p-3.5 bg-emerald-950/60 border border-emerald-500/40 rounded-xl text-emerald-300 text-sm flex items-center gap-2.5 font-medium shadow-sm"
        >
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ page.props.flash?.success || page.props.flash?.message }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Full Name
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autocomplete="name"
                    autofocus
                    class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm transition-all focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 hover:border-slate-700"
                    placeholder="Jane Doe"
                />
                <div v-if="form.errors.name" class="mt-1.5 text-xs text-rose-400 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ form.errors.name }}</span>
                </div>
            </div>

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
                    class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm transition-all focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 hover:border-slate-700"
                    placeholder="jane@example.com"
                />
                <div v-if="form.errors.email" class="mt-1.5 text-xs text-rose-400 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ form.errors.email }}</span>
                </div>
            </div>

            <div>
                <label for="mobile" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Mobile Number
                </label>
                <input
                    id="mobile"
                    v-model="form.mobile"
                    type="tel"
                    required
                    autocomplete="tel"
                    class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm transition-all focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 hover:border-slate-700"
                    placeholder="9876543210"
                />
                <div v-if="form.errors.mobile" class="mt-1.5 text-xs text-rose-400 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ form.errors.mobile }}</span>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="partner" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Referral Code
                    </label>
                    <span v-if="props.partner" class="text-[11px] font-medium text-emerald-400 bg-emerald-950/50 px-2 py-0.5 rounded-md border border-emerald-500/30">
                        Pre-filled
                    </span>
                    <span v-else class="text-[11px] text-slate-500 font-normal">
                        Optional
                    </span>
                </div>
                <input
                    id="partner"
                    v-model="form.partner"
                    type="text"
                    class="w-full px-3.5 py-2.5 bg-slate-950/70 border border-slate-800 rounded-xl text-white placeholder-slate-500 text-sm transition-all focus:outline-none focus:border-indigo-500/80 focus:ring-2 focus:ring-indigo-500/20 hover:border-slate-700"
                    placeholder="Optional partner referral code"
                />
                <div v-if="form.errors.partner" class="mt-1.5 text-xs text-rose-400 flex items-center gap-1 font-medium">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ form.errors.partner }}</span>
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-xl text-sm shadow-md shadow-indigo-600/20 transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 cursor-pointer"
                >
                    <span v-if="form.processing">Registering...</span>
                    <span v-else>Register</span>
                </button>
            </div>

            <div class="text-center pt-2">
                <Link
                    href="/login"
                    class="text-xs text-slate-400 hover:text-indigo-400 font-medium transition-colors focus-visible:outline-none focus-visible:underline"
                >
                    Already registered? Sign in here
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
