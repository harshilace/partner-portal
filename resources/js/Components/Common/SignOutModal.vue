<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    ArrowRightOnRectangleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const page = usePage();
const user = page.props.auth?.user;

const isLoggingOut = ref(false);

const confirmSignOut = () => {
    isLoggingOut.value = true;
    router.post('/logout', {}, {
        onFinish: () => {
            isLoggingOut.value = false;
            emit('close');
        },
    });
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="sign-out-modal-title"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                    @click="!isLoggingOut && emit('close')"
                />

                <!-- Modal Dialog Container -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2"
                >
                    <div
                        v-if="show"
                        class="relative w-full max-w-sm sm:max-w-md bg-white rounded-3xl shadow-2xl border border-gray-100 p-6 sm:p-7 z-10 overflow-hidden text-center transform transition-all"
                    >
                        <!-- Close Button -->
                        <button
                            type="button"
                            @click="emit('close')"
                            :disabled="isLoggingOut"
                            class="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors cursor-pointer disabled:opacity-40"
                            aria-label="Close modal"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>

                        <!-- Icon Badge -->
                        <div class="mx-auto w-14 h-14 rounded-2xl bg-red-50 border border-red-100 text-red-600 flex items-center justify-center shadow-xs mb-4">
                            <ArrowRightOnRectangleIcon class="w-7 h-7" />
                        </div>

                        <!-- Title & Description -->
                        <h3 id="sign-out-modal-title" class="text-xl font-bold text-gray-900 tracking-tight">
                            Sign Out of Partner Portal?
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1.5 leading-relaxed">
                            Are you sure you want to end your session? You will need to log back in to manage your partners and track activity.
                        </p>

                        <!-- Signed-in User Info Badge -->
                        <div v-if="user" class="my-5 p-3.5 bg-gray-50/80 border border-gray-100 rounded-2xl flex items-center gap-3 text-left">
                            <div class="w-10 h-10 rounded-full bg-linear-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                {{ user.name ? user.name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase() : 'U' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-gray-900 truncate">{{ user.name }}</p>
                                <p class="text-[11px] text-gray-500 truncate mt-0.5">{{ user.email }}</p>
                            </div>
                        </div>

                        <!-- Buttons Row -->
                        <div class="flex flex-col sm:flex-row items-center gap-2.5 pt-1">
                            <button
                                type="button"
                                @click="emit('close')"
                                :disabled="isLoggingOut"
                                class="w-full sm:flex-1 py-2.5 px-4 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-200 rounded-xl transition-colors cursor-pointer disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="confirmSignOut"
                                :disabled="isLoggingOut"
                                class="w-full sm:flex-1 py-2.5 px-4 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md shadow-red-600/25 transition-all cursor-pointer flex items-center justify-center gap-2 disabled:opacity-75"
                            >
                                <span v-if="isLoggingOut" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                <span>{{ isLoggingOut ? 'Signing Out...' : 'Yes, Sign Out' }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
