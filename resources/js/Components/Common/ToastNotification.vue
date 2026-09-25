<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/solid';

const toasts = ref([]);

const addToast = (type, message, duration = 4000) => {
    if (!message) return;
    const id = Date.now() + Math.random().toString(36).substring(2, 9);
    
    // Avoid duplicate identical toasts within 1 sec
    const existingIndex = toasts.value.findIndex(t => t.message === message);
    if (existingIndex !== -1) {
        toasts.value.splice(existingIndex, 1);
    }

    const toast = { id, type, message };
    toasts.value.push(toast);

    if (duration > 0) {
        setTimeout(() => {
            removeToast(id);
        }, duration);
    }
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

// Listen to Inertia flash props
const page = usePage();

const checkFlashProps = (flash) => {
    if (!flash) return;
    if (flash.success) addToast('success', flash.success);
    if (flash.error) addToast('error', flash.error);
    if (flash.warning) addToast('warning', flash.warning);
    if (flash.message) addToast('info', flash.message);
    if (flash.info) addToast('info', flash.info);
};

watch(
    () => page.props.flash,
    (newFlash) => {
        checkFlashProps(newFlash);
    },
    { deep: true, immediate: true }
);

onMounted(() => {
    checkFlashProps(page.props.flash);
});
</script>

<template>
    <div
        class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none px-4 sm:px-0"
        aria-live="polite"
    >
        <TransitionGroup
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'pointer-events-auto flex items-start gap-3 p-4 rounded-xl shadow-lg border backdrop-blur-md transition-all duration-200',
                    toast.type === 'success' ? 'bg-white/95 border-emerald-200 text-gray-800 shadow-emerald-500/10' : '',
                    toast.type === 'error' ? 'bg-white/95 border-red-200 text-gray-800 shadow-red-500/10' : '',
                    toast.type === 'warning' ? 'bg-white/95 border-amber-200 text-gray-800 shadow-amber-500/10' : '',
                    toast.type === 'info' ? 'bg-white/95 border-blue-200 text-gray-800 shadow-blue-500/10' : '',
                ]"
            >
                <!-- Icon -->
                <div class="shrink-0 mt-0.5">
                    <CheckCircleIcon v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-500" />
                    <XCircleIcon v-else-if="toast.type === 'error'" class="w-5 h-5 text-red-500" />
                    <ExclamationTriangleIcon v-else-if="toast.type === 'warning'" class="w-5 h-5 text-amber-500" />
                    <InformationCircleIcon v-else class="w-5 h-5 text-blue-500" />
                </div>

                <!-- Message -->
                <div class="flex-1 text-sm font-medium leading-snug text-gray-800">
                    {{ toast.message }}
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="removeToast(toast.id)"
                    class="shrink-0 text-gray-400 hover:text-gray-600 p-0.5 rounded-lg transition-colors"
                    aria-label="Close notification"
                >
                    <XMarkIcon class="w-4 h-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
