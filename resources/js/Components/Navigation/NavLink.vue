<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-slate-800'
        : 'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-slate-300 hover:text-white hover:bg-slate-800/60 transition-colors'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
