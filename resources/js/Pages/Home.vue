<script setup>
import {
    ref
} from 'vue';

import {
    Head,
    Link
} from '@inertiajs/vue3';

import {
    PlusIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import CreatePost
    from '@/Components/app/CreatePost.vue';

import GroupModal
    from '@/Components/app/GroupModal.vue';

import PostList
    from '@/Components/app/PostList.vue';

const props = defineProps({
    posts: Object,

    groups: {
        type: Array,
        default: () => []
    },

    followings: {
        type: Array,
        default: () => []
    }
});

const localGroups =
    ref([
        ...props.groups
    ]);

const showNewGroupModal =
    ref(false);

function onGroupCreated(group) {
    localGroups.value.unshift(
        group
    );
}
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-y-auto bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto max-w-6xl px-4 pb-10 pt-5 sm:px-6 lg:px-8"
            >
                <div
                    class="mx-auto max-w-4xl"
                >
                    <CreatePost />

                    <div
                        class="mt-4 flex items-center gap-3 overflow-x-auto rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] px-4 py-3 scrollbar-hidden"
                    >
                        <button
                            type="button"
                            @click="showNewGroupModal = true"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-dashed border-[var(--poet-border-strong)] text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                            aria-label="Create circle"
                        >
                            <PlusIcon
                                class="h-4 w-4"
                            />
                        </button>

                        <Link
                            v-for="group in localGroups.slice(0, 6)"
                            :key="`g-${group.id}`"
                            :href="
                                route(
                                    'group.profile',
                                    group.slug
                                )
                            "
                            class="flex shrink-0 items-center gap-2 rounded-full bg-[var(--poet-surface-soft)] py-1.5 pl-1.5 pr-3 text-xs font-medium text-[var(--poet-text)] transition hover:-translate-y-0.5"
                        >
                            <img
                                v-if="group.thumbnail_url"
                                :src="group.thumbnail_url"
                                :alt="group.name"
                                class="h-7 w-7 rounded-full object-cover"
                            />

                            <span
                                v-else
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--poet-accent-soft)] font-serif text-[var(--poet-accent)]"
                            >
                                {{
                                    group.name
                                        ?.charAt(0)
                                        .toUpperCase()
                                }}
                            </span>

                            {{ group.name }}
                        </Link>

                        <div
                            v-if="
                                localGroups.length &&
                                followings.length
                            "
                            class="h-7 w-px shrink-0 bg-[var(--poet-border)]"
                        />

                        <Link
                            v-for="user in followings.slice(0, 8)"
                            :key="`u-${user.id}`"
                            :href="
                                route(
                                    'profile',
                                    {
                                        username:
                                            user.username
                                    }
                                )
                            "
                            class="group shrink-0"
                            :title="user.name"
                        >
                            <img
                                :src="
                                    user.avatar_url ||
                                    '/img/default_avatar.webp'
                                "
                                :alt="user.name"
                                class="h-9 w-9 rounded-full object-cover ring-1 ring-[var(--poet-border)] transition group-hover:-translate-y-0.5 group-hover:ring-[var(--poet-accent)]"
                            />
                        </Link>
                    </div>
                </div>

                <section
                    class="mx-auto mt-6 max-w-5xl"
                >
                    <div
                        class="mb-2 flex items-center justify-between px-[7%]"
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

                    <PostList
                        :posts="posts"
                        mode="deck"
                    />
                </section>
            </div>
        </div>

        <GroupModal
            v-model="showNewGroupModal"
            @created="onGroupCreated"
        />
    </AuthenticatedLayout>
</template>
