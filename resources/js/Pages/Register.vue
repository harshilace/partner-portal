<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '../Layouts/GuestLayout.vue';

const props = defineProps({
    partner: {
        type: String,
        default: '',
    },
});

const form = useForm({
    name: '',
    email: '',
    mobile: '',
    partner: props.partner || '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <h2 class="text-xl font-semibold text-white mb-2 text-center">Registration</h2>
        <p class="text-xs text-slate-400 mb-6 text-center">
            Register via Partner Referral or Public Access
        </p>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autocomplete="name"
                    autofocus
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    placeholder="Jane Doe"
                />
                <div v-if="form.errors.name" class="mt-1 text-xs text-rose-400">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1">Email Address</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    placeholder="jane@example.com"
                />
                <div v-if="form.errors.email" class="mt-1 text-xs text-rose-400">
                    {{ form.errors.email }}
                </div>
            </div>

            <div>
                <label for="mobile" class="block text-sm font-medium text-slate-300 mb-1">Mobile Number</label>
                <input
                    id="mobile"
                    v-model="form.mobile"
                    type="tel"
                    required
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    placeholder="9876543210"
                />
                <div v-if="form.errors.mobile" class="mt-1 text-xs text-rose-400">
                    {{ form.errors.mobile }}
                </div>
            </div>

            <div>
                <label for="partner" class="block text-sm font-medium text-slate-300 mb-1">
                    Referral Code
                    <span v-if="partner" class="text-xs text-emerald-400 ml-1">(Pre-filled)</span>
                </label>
                <input
                    id="partner"
                    v-model="form.partner"
                    type="text"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                    placeholder="Optional partner code"
                />
                <div v-if="form.errors.partner" class="mt-1 text-xs text-rose-400">
                    {{ form.errors.partner }}
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium rounded-lg text-sm transition-colors cursor-pointer"
                >
                    <span v-if="form.processing">Registering...</span>
                    <span v-else>Register</span>
                </button>
            </div>

            <div class="text-center pt-2">
                <Link href="/login" class="text-xs text-slate-400 hover:text-indigo-400">
                    Already registered? Sign in here
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
