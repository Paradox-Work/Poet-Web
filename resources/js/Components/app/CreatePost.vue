<script setup>
import { ref } from 'vue';

import {
    Link,
    usePage
} from '@inertiajs/vue3';

import {
    ChatBubbleBottomCenterTextIcon,
    PencilSquareIcon
} from '@heroicons/vue/24/outline';

import PostModal
    from '@/Components/app/PostModal.vue';

const authUser =
    usePage().props.auth.user;

const showModal =
    ref(false);

const props = defineProps({
    group: {
        type: Object,
        default: null
    }
});

const newPost = {
    id: null,
    type: 'post',
    body: '',
    user: authUser,
    group: props.group ?? null,
    updated_at: null
};

function showCreatePostModal() {
    showModal.value = true;
}
</script>

<template>
    <div
        class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-3 shadow-sm"
    >
        <div
            class="grid gap-2 sm:grid-cols-2"
        >
            <button
                type="button"
                @click="showCreatePostModal"
                class="group flex items-center gap-3 rounded-xl px-4 py-3 text-left transition hover:bg-[var(--poet-surface-soft)]"
            >
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[var(--poet-border)] text-[var(--poet-muted)] transition group-hover:text-[var(--poet-text)]"
                >
                    <ChatBubbleBottomCenterTextIcon
                        class="h-5 w-5"
                    />
                </span>

                <div>
                    <div
                        class="text-sm font-semibold text-[var(--poet-text)]"
                    >
                        Quick post
                    </div>

                    <div
                        class="mt-0.5 text-xs text-[var(--poet-muted)]"
                    >
                        Share a thought or discussion
                    </div>
                </div>
            </button>

            <Link
                :href="
                    route(
                        'poem.write',
                        group
                            ? { group: group.id }
                            : {}
                    )
                "
                class="group flex items-center gap-3 rounded-xl bg-[var(--poet-accent-soft)] px-4 py-3 text-left transition hover:-translate-y-0.5"
            >
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--poet-accent)] text-white shadow-sm"
                >
                    <PencilSquareIcon
                        class="h-5 w-5"
                    />
                </span>

                <div>
                    <div
                        class="text-sm font-semibold text-[var(--poet-accent-strong)]"
                    >
                        Write poem
                    </div>

                    <div
                        class="mt-0.5 text-xs text-[var(--poet-muted)]"
                    >
                        Open the writing studio
                    </div>
                </div>
            </Link>
        </div>

        <PostModal
            :post="newPost"
            :group="group"
            :allow-poem-mode="false"
            v-model="showModal"
        />
    </div>
</template>
