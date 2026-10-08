<script setup>
import {
    Head
} from '@inertiajs/vue3';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import PostList
    from '@/Components/app/PostList.vue';

defineProps({
    posts: Object
});
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-hidden bg-[var(--poet-bg)]"
        >
            <section
                class="mx-auto flex h-full max-w-[1560px] flex-col px-4 pb-3 pt-3 sm:px-6 lg:px-8"
            >
                <div
                    class="flex shrink-0 items-end justify-between px-[2%] lg:px-[7%]"
                >
                    <div>
                        <div
                            class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                        >
                            Feed
                        </div>

                        <div
                            class="mt-1 font-serif text-2xl font-semibold text-[var(--poet-text)]"
                        >
                            Read one thing at a time.
                        </div>
                    </div>

                    <div
                        class="hidden text-xs text-[var(--poet-muted)] sm:block"
                    >
                        Drag, swipe, wheel or use ← →
                    </div>
                </div>

                <div class="min-h-0 flex-1">
                    <PostList
                        :posts="posts"
                        mode="deck"
                    />
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
