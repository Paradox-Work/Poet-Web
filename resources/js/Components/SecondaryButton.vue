<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="poet-focus inline-flex items-center justify-center rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] px-4 py-2 text-sm font-semibold text-[var(--poet-text)] shadow-sm transition duration-150 ease-out hover:-translate-y-0.5 hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)] disabled:pointer-events-none disabled:opacity-40"
    >
        <slot />
    </button>
</template>
