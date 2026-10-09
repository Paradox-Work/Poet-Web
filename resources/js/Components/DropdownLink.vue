<script setup>
import {
    Link
} from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2.5 text-start text-sm leading-5 text-[var(--poet-text)] transition duration-150 hover:bg-[var(--poet-surface-soft)] focus:bg-[var(--poet-surface-soft)] focus:outline-none"
    >
        <slot />
    </Link>
</template>
