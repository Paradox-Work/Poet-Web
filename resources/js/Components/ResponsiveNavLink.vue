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
            'block w-full border-l-2 px-4 py-2.5 text-start text-sm font-medium transition duration-150',
            props.active
                ? 'border-[var(--poet-accent)] bg-[var(--poet-accent-soft)] text-[var(--poet-accent-strong)]'
                : 'border-transparent text-[var(--poet-muted)] hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]'
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
