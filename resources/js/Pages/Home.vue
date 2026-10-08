<script setup>
import {
    ref
} from 'vue';

import {
    Head,
    Link,
    router
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
    },

    feedFilters: {
        type: Object,
        default: () => ({
            sort: 'latest',
            genre: null
        })
    },

    genreCounts: {
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

function applyFeedFilter(
    changes
) {
    const nextFilters = {
        sort:
            props.feedFilters.sort
                ?? 'latest',
        genre:
            props.feedFilters.genre
                ?? null,
        ...changes
    };

    if (!nextFilters.genre) {
        delete nextFilters.genre;
    }

    router.get(
        route('dashboard'),
        nextFilters,
        {
            preserveState: false,
            preserveScroll: true,
            replace: true
        }
    );
}
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-hidden bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto grid h-full max-w-[1500px] gap-4 px-4 py-4 lg:grid-cols-[230px_minmax(0,1fr)_230px]"
            >
                <aside
                    class="hidden min-h-0 flex-col gap-4 lg:flex"
                >
                    <section
                        class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-3"
                    >
                        <div
                            class="mb-3 px-1 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--poet-muted)]"
                        >
                            Feed
                        </div>

                        <div class="space-y-1">
                            <button
                                type="button"
                                @click="
                                    applyFeedFilter({
                                        sort: 'latest'
                                    })
                                "
                                :class="[
                                    'flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm transition',
                                    feedFilters.sort === 'latest'
                                        ? 'bg-[var(--poet-accent-soft)] font-semibold text-[var(--poet-accent-strong)]'
                                        : 'text-[var(--poet-muted)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]'
                                ]"
                            >
                                <span>Latest</span>
                            </button>

                            <button
                                type="button"
                                @click="
                                    applyFeedFilter({
                                        sort: 'trending'
                                    })
                                "
                                :class="[
                                    'flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm transition',
                                    feedFilters.sort === 'trending'
                                        ? 'bg-[var(--poet-accent-soft)] font-semibold text-[var(--poet-accent-strong)]'
                                        : 'text-[var(--poet-muted)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]'
                                ]"
                            >
                                <span>Trending</span>
                            </button>
                        </div>
                    </section>

                    <section
                        class="min-h-0 flex-1 rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-3"
                    >
                        <div
                            class="mb-2 flex items-center justify-between px-1"
                        >
                            <div
                                class="text-xs font-semibold uppercase tracking-[0.2em] text-[var(--poet-muted)]"
                            >
                                Genres
                            </div>

                            <button
                                v-if="feedFilters.genre"
                                type="button"
                                @click="
                                    applyFeedFilter({
                                        genre: null
                                    })
                                "
                                class="text-[10px] font-medium text-[var(--poet-accent)] hover:underline"
                            >
                                Clear
                            </button>
                        </div>

                        <div
                            class="scrollbar-hidden max-h-full space-y-1 overflow-y-auto"
                        >
                            <button
                                v-for="genre in genreCounts"
                                :key="genre.name"
                                type="button"
                                @click="
                                    applyFeedFilter({
                                        genre:
                                            feedFilters.genre ===
                                            genre.name
                                                ? null
                                                : genre.name
                                    })
                                "
                                :class="[
                                    'flex w-full items-center justify-between gap-2 rounded-xl px-3 py-2 text-left text-xs transition',
                                    feedFilters.genre === genre.name
                                        ? 'bg-[var(--poet-accent-soft)] text-[var(--poet-accent-strong)]'
                                        : 'text-[var(--poet-muted)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]'
                                ]"
                            >
                                <span class="truncate">
                                    {{ genre.name }}
                                </span>

                                <span
                                    class="shrink-0 rounded-full bg-[var(--poet-surface-soft)] px-2 py-0.5 text-[10px]"
                                >
                                    {{ genre.count }}
                                </span>
                            </button>

                            <div
                                v-if="!genreCounts.length"
                                class="px-3 py-4 text-xs leading-5 text-[var(--poet-muted)]"
                            >
                                Genres will appear here as poems are published.
                            </div>
                        </div>
                    </section>

                    <CreatePost compact />
                </aside>

                <main
                    class="flex min-h-0 min-w-0 flex-col"
                >
                    <div
                        class="mb-2 flex shrink-0 items-end justify-between gap-4 px-[7%]"
                    >
                        <div>
                            <div
                                class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                            >
                                {{
                                    feedFilters.sort === 'trending'
                                        ? 'Trending'
                                        : 'Latest'
                                }}
                                <template
                                    v-if="feedFilters.genre"
                                >
                                    · {{ feedFilters.genre }}
                                </template>
                            </div>

                            <h1
                                class="mt-1 font-serif text-2xl font-semibold text-[var(--poet-text)]"
                            >
                                Read one thing at a time.
                            </h1>
                        </div>

                        <div
                            class="hidden text-xs text-[var(--poet-muted)] sm:block"
                        >
                            swipe · wheel · ← →
                        </div>
                    </div>

                    <div
                        class="min-h-0 flex-1"
                    >
                        <PostList
                            :key="
                                `${feedFilters.sort}-${feedFilters.genre ?? 'all'}`
                            "
                            :posts="posts"
                            mode="deck"
                            fill-height
                        />
                    </div>
                </main>

                <aside
                    class="hidden min-h-0 flex-col gap-4 lg:flex"
                >
                    <section
                        class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-3"
                    >
                        <div
                            class="mb-3 flex items-center justify-between px-1"
                        >
                            <div>
                                <div
                                    class="font-serif text-base font-semibold text-[var(--poet-text)]"
                                >
                                    Circles
                                </div>
                                <div
                                    class="text-[10px] text-[var(--poet-muted)]"
                                >
                                    Your writing spaces
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="
                                    showNewGroupModal = true
                                "
                                class="flex h-8 w-8 items-center justify-center rounded-full border border-dashed border-[var(--poet-border-strong)] text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                                aria-label="Create circle"
                            >
                                <PlusIcon
                                    class="h-4 w-4"
                                />
                            </button>
                        </div>

                        <div
                            v-if="localGroups.length"
                            class="space-y-1.5"
                        >
                            <Link
                                v-for="group in localGroups.slice(0, 6)"
                                :key="group.id"
                                :href="
                                    route(
                                        'group.profile',
                                        group.slug
                                    )
                                "
                                class="flex items-center gap-2 rounded-xl px-2 py-2 transition hover:bg-[var(--poet-surface-soft)]"
                            >
                                <img
                                    v-if="group.thumbnail_url"
                                    :src="group.thumbnail_url"
                                    :alt="group.name"
                                    class="h-8 w-8 rounded-full object-cover"
                                />

                                <div
                                    v-else
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--poet-accent-soft)] font-serif text-xs text-[var(--poet-accent)]"
                                >
                                    {{
                                        group.name
                                            ?.charAt(0)
                                            .toUpperCase()
                                    }}
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="truncate text-xs font-medium text-[var(--poet-text)]"
                                    >
                                        {{ group.name }}
                                    </div>

                                    <div
                                        v-if="group.role === 'admin'"
                                        class="text-[9px] uppercase tracking-wide text-[var(--poet-accent)]"
                                    >
                                        Admin
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="flex items-center gap-2 px-2 py-3 text-xs leading-5 text-[var(--poet-muted)]"
                        >
                            <UserGroupIcon
                                class="h-4 w-4 shrink-0"
                            />
                            No circles yet.
                        </div>
                    </section>

                    <section
                        class="min-h-0 flex-1 rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-3"
                    >
                        <div
                            class="mb-3 px-1"
                        >
                            <div
                                class="font-serif text-base font-semibold text-[var(--poet-text)]"
                            >
                                Following
                            </div>

                            <div
                                class="text-[10px] text-[var(--poet-muted)]"
                            >
                                Writers you keep close
                            </div>
                        </div>

                        <div
                            v-if="followings.length"
                            class="scrollbar-hidden grid grid-cols-3 gap-3 overflow-y-auto"
                        >
                            <Link
                                v-for="user in followings"
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
                                class="group min-w-0 text-center"
                                :title="user.name"
                            >
                                <img
                                    :src="
                                        user.avatar_url ||
                                        '/img/default_avatar.webp'
                                    "
                                    :alt="user.name"
                                    class="mx-auto h-10 w-10 rounded-full object-cover ring-1 ring-[var(--poet-border)] transition group-hover:-translate-y-0.5 group-hover:ring-[var(--poet-accent)]"
                                />

                                <div
                                    class="mt-1 truncate text-[10px] text-[var(--poet-muted)]"
                                >
                                    {{ user.name }}
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="px-2 py-3 text-xs text-[var(--poet-muted)]"
                        >
                            You are not following anyone yet.
                        </div>
                    </section>
                </aside>
            </div>
        </div>

        <GroupModal
            v-model="showNewGroupModal"
            @created="onGroupCreated"
        />
    </AuthenticatedLayout>
</template>
