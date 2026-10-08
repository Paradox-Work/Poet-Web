<script setup>
import {
    XMarkIcon
} from '@heroicons/vue/24/outline';

import CommentList
    from '@/Components/app/CommentList.vue';

defineProps({
    post: {
        type: Object,
        required: true
    }
});

defineEmits([
    'close'
]);
</script>

<template>
    <div
        class="comment-list fixed inset-0 z-50 flex items-end justify-center bg-black/55 p-3 backdrop-blur-[1px] lg:static lg:z-auto lg:block lg:bg-transparent lg:p-0 lg:backdrop-blur-none"
        @click.self="$emit('close')"
        @wheel.stop
    >
        <section
            class="flex h-[min(82vh,720px)] w-full max-w-xl min-w-0 flex-col overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] shadow-2xl lg:h-[min(52vh,520px)] lg:max-w-none lg:shadow-[0_10px_35px_rgba(15,23,42,0.08)]"
        >
            <header
                class="flex shrink-0 items-center justify-between border-b border-[var(--poet-border)] px-4 py-3"
            >
                <div>
                    <div
                        class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                    >
                        Comments
                    </div>

                    <div
                        class="text-xs text-[var(--poet-muted)]"
                    >
                        {{ post.num_of_comments ?? 0 }}
                        {{
                            (post.num_of_comments ?? 0) === 1
                                ? 'comment'
                                : 'comments'
                        }}
                    </div>
                </div>

                <button
                    type="button"
                    @click="$emit('close')"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                    aria-label="Close comments"
                >
                    <XMarkIcon
                        class="h-5 w-5"
                    />
                </button>
            </header>

            <div
                class="min-h-0 min-w-0 flex-1 overflow-hidden p-4"
            >
                <CommentList
                    :post="post"
                    :comments="post.comments ?? []"
                    panel-mode
                />
            </div>
        </section>
    </div>
</template>
