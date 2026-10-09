<script setup>
import {
    computed
} from 'vue';

const props = defineProps({
    meta: {
        type: Object,
        default: null
    },

    label: {
        type: String,
        default: 'Pages'
    }
});

defineEmits([
    'page'
]);

const pages =
    computed(() => {
        if (!props.meta) {
            return [];
        }

        const current =
            Number(
                props.meta.current_page
                ?? 1
            );

        const last =
            Number(
                props.meta.last_page
                ?? 1
            );

        if (last <= 7) {
            return Array.from(
                { length: last },
                (_, index) =>
                    index + 1
            );
        }

        const result = [1];

        if (current > 4) {
            result.push('left-ellipsis');
        }

        const start =
            Math.max(
                2,
                current - 1
            );

        const end =
            Math.min(
                last - 1,
                current + 1
            );

        for (
            let page = start;
            page <= end;
            page += 1
        ) {
            result.push(page);
        }

        if (current < last - 3) {
            result.push('right-ellipsis');
        }

        result.push(last);

        return result;
    });

const currentPage =
    computed(() =>
        Number(
            props.meta?.current_page
            ?? 1
        )
    );

const lastPage =
    computed(() =>
        Number(
            props.meta?.last_page
            ?? 1
        )
    );
</script>

<template>
    <nav
        v-if="meta && lastPage > 1"
        class="flex flex-wrap items-center justify-center gap-1"
        :aria-label="label"
    >
        <button
            type="button"
            :disabled="currentPage <= 1"
            @click="$emit('page', currentPage - 1)"
            class="flex h-9 min-w-9 items-center justify-center rounded-lg border border-[var(--poet-border)] px-2 text-sm text-[var(--poet-muted)] transition hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)] disabled:pointer-events-none disabled:opacity-30"
            aria-label="Previous page"
        >
            ‹
        </button>

        <template
            v-for="page in pages"
            :key="page"
        >
            <span
                v-if="
                    typeof page ===
                    'string'
                "
                class="px-1 text-sm text-[var(--poet-muted)]"
            >
                …
            </span>

            <button
                v-else
                type="button"
                @click="$emit('page', page)"
                :class="[
                    'flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition',
                    page === currentPage
                        ? 'border-[var(--poet-accent)] bg-[var(--poet-accent-soft)] text-[var(--poet-accent)]'
                        : 'border-[var(--poet-border)] text-[var(--poet-muted)] hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]'
                ]"
                :aria-current="
                    page === currentPage
                        ? 'page'
                        : undefined
                "
            >
                {{ page }}
            </button>
        </template>

        <button
            type="button"
            :disabled="
                currentPage >= lastPage
            "
            @click="$emit('page', currentPage + 1)"
            class="flex h-9 min-w-9 items-center justify-center rounded-lg border border-[var(--poet-border)] px-2 text-sm text-[var(--poet-muted)] transition hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)] disabled:pointer-events-none disabled:opacity-30"
            aria-label="Next page"
        >
            ›
        </button>
    </nav>
</template>
