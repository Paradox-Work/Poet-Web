<script setup>
import {
    ref
} from 'vue';

import {
    Head,
    Link
} from '@inertiajs/vue3';

import {
    PlusIcon,
    UserGroupIcon
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
                class="mx-auto max-w-6xl px-4 pb-12 pt-7 sm:px-6 lg:px-8"
            >
                <section
                    class="mx-auto max-w-3xl"
                >
                    <div class="mb-6">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--poet-muted)]"
                        >
                            Your reading room
                        </p>

                        <h1
                            class="mt-2 font-serif text-3xl font-semibold tracking-tight text-[var(--poet-text)]"
                        >
                            A quieter place to read and write.
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-[var(--poet-muted)]"
                        >
                            Share something small, work on a poem, or settle into what people you follow have written.
                        </p>
                    </div>

                    <CreatePost />
                </section>

                <section
                    class="mx-auto mt-7 max-w-5xl"
                >
                    <div
                        class="grid gap-4 lg:grid-cols-2"
                    >
                        <div
                            class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-4 shadow-sm"
                        >
                            <div
                                class="mb-3 flex items-center justify-between gap-3"
                            >
                                <div>
                                    <div
                                        class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                                    >
                                        Your circles
                                    </div>

                                    <div
                                        class="text-xs text-[var(--poet-muted)]"
                                    >
                                        Groups you belong to
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="showNewGroupModal = true"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[var(--poet-border)] text-[var(--poet-muted)] transition hover:-translate-y-0.5 hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                                    aria-label="Create group"
                                >
                                    <PlusIcon
                                        class="h-4 w-4"
                                    />
                                </button>
                            </div>

                            <div
                                v-if="localGroups.length"
                                class="flex gap-3 overflow-x-auto pb-1 scrollbar-hidden"
                            >
                                <Link
                                    v-for="group in localGroups.slice(0, 8)"
                                    :key="group.id"
                                    :href="
                                        route(
                                            'group.profile',
                                            group.slug
                                        )
                                    "
                                    class="group flex min-w-[92px] flex-col items-center rounded-xl px-2 py-2 text-center transition hover:bg-[var(--poet-surface-soft)]"
                                >
                                    <img
                                        v-if="group.thumbnail_url"
                                        :src="group.thumbnail_url"
                                        :alt="group.name"
                                        class="h-12 w-12 rounded-full object-cover ring-1 ring-[var(--poet-border)]"
                                    />

                                    <div
                                        v-else
                                        class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--poet-accent-soft)] font-serif font-semibold text-[var(--poet-accent)]"
                                    >
                                        {{
                                            group.name
                                                ?.charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>

                                    <div
                                        class="mt-2 w-full truncate text-xs font-medium text-[var(--poet-text)]"
                                    >
                                        {{ group.name }}
                                    </div>

                                    <div
                                        v-if="group.role === 'admin'"
                                        class="mt-0.5 text-[10px] uppercase tracking-wide text-[var(--poet-accent)]"
                                    >
                                        Admin
                                    </div>
                                </Link>
                            </div>

                            <div
                                v-else
                                class="flex items-center gap-3 rounded-xl bg-[var(--poet-surface-soft)] px-4 py-4 text-sm text-[var(--poet-muted)]"
                            >
                                <UserGroupIcon
                                    class="h-5 w-5"
                                />
                                No circles yet. Create one when you want a shared space for writing.
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-4 shadow-sm"
                        >
                            <div class="mb-3">
                                <div
                                    class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                                >
                                    Following
                                </div>

                                <div
                                    class="text-xs text-[var(--poet-muted)]"
                                >
                                    People whose writing you keep close
                                </div>
                            </div>

                            <div
                                v-if="followings.length"
                                class="flex gap-4 overflow-x-auto pb-1 scrollbar-hidden"
                            >
                                <Link
                                    v-for="user in followings.slice(0, 10)"
                                    :key="user.id"
                                    :href="
                                        route(
                                            'profile',
                                            {
                                                username:
                                                    user.username
                                            }
                                        )
                                    "
                                    class="group flex min-w-[76px] flex-col items-center text-center"
                                >
                                    <img
                                        :src="
                                            user.avatar_url ||
                                            '/img/default_avatar.webp'
                                        "
                                        :alt="user.name"
                                        class="h-12 w-12 rounded-full object-cover ring-1 ring-[var(--poet-border)] transition group-hover:-translate-y-0.5 group-hover:ring-[var(--poet-accent)]"
                                    />

                                    <div
                                        class="mt-2 w-full truncate text-xs font-medium text-[var(--poet-text)]"
                                    >
                                        {{ user.name }}
                                    </div>

                                    <div
                                        class="w-full truncate text-[10px] text-[var(--poet-muted)]"
                                    >
                                        @{{ user.username }}
                                    </div>
                                </Link>
                            </div>

                            <div
                                v-else
                                class="rounded-xl bg-[var(--poet-surface-soft)] px-4 py-4 text-sm text-[var(--poet-muted)]"
                            >
                                You are not following anyone yet.
                            </div>
                        </div>
                    </div>
                </section>

                <section
                    class="mx-auto mt-8 max-w-3xl"
                >
                    <div
                        class="mb-4 flex items-end justify-between gap-4 px-1"
                    >
                        <div>
                            <div
                                class="font-serif text-2xl font-semibold text-[var(--poet-text)]"
                            >
                                Recent writing
                            </div>

                            <div
                                class="mt-1 text-sm text-[var(--poet-muted)]"
                            >
                                Poems and posts from the people and circles around you.
                            </div>
                        </div>
                    </div>

                    <PostList
                        :posts="posts"
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
