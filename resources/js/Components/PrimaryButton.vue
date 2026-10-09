<template>
    <button
        class="poet-focus inline-flex items-center justify-center rounded-full border border-transparent bg-[var(--poet-accent)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-150 ease-out hover:-translate-y-0.5 hover:bg-[var(--poet-accent-strong)] hover:shadow-md disabled:pointer-events-none disabled:opacity-50"
    >
        <slot />
    </button>
</template>
