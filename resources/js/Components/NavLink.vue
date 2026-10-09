<script setup>
import {
    computed
} from 'vue';

import {
    Link
} from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },

    active: {
        type: Boolean,
    },
});

const classes =
    computed(() =>
        [
            'relative inline-flex items-center px-1 pt-1 text-sm font-medium transition duration-150 ease-out',
            'after:absolute after:inset-x-1 after:bottom-0 after:h-0.5 after:origin-left after:rounded-full after:bg-[var(--poet-accent)] after:transition-transform after:duration-200',
            props.active
                ? 'text-[var(--poet-text)] after:scale-x-100'
                : 'text-[var(--poet-muted)] after:scale-x-0 hover:text-[var(--poet-text)] hover:after:scale-x-100'
        ]
    );
</script>

<template>
    <Link
        :href="href"
        :class="classes"
    >
        <slot />
    </Link>
</template>
