<script setup>
import {
    Head,
    Link
} from '@inertiajs/vue3';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    followings: {
        type: Array,
        default: () => []
    },

    followers: {
        type: Array,
        default: () => []
    },

    groups: {
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Social" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-y-auto bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8"
            >
                <header class="mb-6">
                    <div
                        class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                    >
                        Social
                    </div>

                    <h1
                        class="mt-2 font-serif text-3xl font-semibold text-[var(--poet-text)]"
                    >
                        People and groups around you.
                    </h1>
                </header>

                <section
                    class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-4"
                >
                    <div class="mb-4">
                        <h2
                            class="font-serif text-xl font-semibold text-[var(--poet-text)]"
                        >
                            Following
                        </h2>

                        <p
                            class="mt-1 text-sm text-[var(--poet-muted)]"
                        >
                            Writers you chose to keep close.
                        </p>
                    </div>

                    <div
                        v-if="followings.length"
                        class="scrollbar-hidden flex gap-5 overflow-x-auto pb-2"
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
                            class="group flex w-20 shrink-0 flex-col items-center text-center"
                        >
                            <div
                                class="rounded-full p-[2px] ring-1 ring-[var(--poet-border)] transition group-hover:ring-[var(--poet-accent)]"
                            >
                                <img
                                    :src="
                                        user.avatar_url ||
                                        '/img/default_avatar.webp'
                                    "
                                    :alt="user.name"
                                    class="h-14 w-14 rounded-full object-cover"
                                />
                            </div>

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
                        class="rounded-xl bg-[var(--poet-surface-soft)] px-4 py-5 text-sm text-[var(--poet-muted)]"
                    >
                        You are not following anyone yet.
                    </div>
                </section>

                <div
                    class="mt-6 grid gap-6 lg:grid-cols-2"
                >
                    <section
                        class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-4"
                    >
                        <div class="mb-4">
                            <h2
                                class="font-serif text-xl font-semibold text-[var(--poet-text)]"
                            >
                                Followers
                            </h2>

                            <p
                                class="mt-1 text-sm text-[var(--poet-muted)]"
                            >
                                People who follow your writing.
                            </p>
                        </div>

                        <div
                            v-if="followers.length"
                            class="grid gap-2 sm:grid-cols-2"
                        >
                            <Link
                                v-for="user in followers"
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
                                class="flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-[var(--poet-surface-soft)]"
                            >
                                <img
                                    :src="
                                        user.avatar_url ||
                                        '/img/default_avatar.webp'
                                    "
                                    :alt="user.name"
                                    class="h-10 w-10 rounded-full object-cover"
                                />

                                <div class="min-w-0">
                                    <div
                                        class="truncate text-sm font-medium text-[var(--poet-text)]"
                                    >
                                        {{ user.name }}
                                    </div>

                                    <div
                                        class="truncate text-xs text-[var(--poet-muted)]"
                                    >
                                        @{{ user.username }}
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="rounded-xl bg-[var(--poet-surface-soft)] px-4 py-5 text-sm text-[var(--poet-muted)]"
                        >
                            No followers yet.
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-4"
                    >
                        <div class="mb-4">
                            <h2
                                class="font-serif text-xl font-semibold text-[var(--poet-text)]"
                            >
                                Groups
                            </h2>

                            <p
                                class="mt-1 text-sm text-[var(--poet-muted)]"
                            >
                                Shared spaces you belong to.
                            </p>
                        </div>

                        <div
                            v-if="groups.length"
                            class="grid gap-2 sm:grid-cols-2"
                        >
                            <Link
                                v-for="group in groups"
                                :key="group.id"
                                :href="
                                    route(
                                        'group.profile',
                                        group.slug
                                    )
                                "
                                class="flex items-center gap-3 rounded-xl px-3 py-3 transition hover:bg-[var(--poet-surface-soft)]"
                            >
                                <img
                                    v-if="group.thumbnail_url"
                                    :src="group.thumbnail_url"
                                    :alt="group.name"
                                    class="h-10 w-10 rounded-full object-cover"
                                />

                                <div
                                    v-else
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--poet-accent-soft)] font-serif text-sm text-[var(--poet-accent)]"
                                >
                                    {{
                                        group.name
                                            ?.charAt(0)
                                            .toUpperCase()
                                    }}
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="truncate text-sm font-medium text-[var(--poet-text)]"
                                    >
                                        {{ group.name }}
                                    </div>

                                    <div
                                        class="truncate text-xs text-[var(--poet-muted)]"
                                    >
                                        {{
                                            group.role === 'admin'
                                                ? 'Admin'
                                                : 'Member'
                                        }}
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="rounded-xl bg-[var(--poet-surface-soft)] px-4 py-5 text-sm text-[var(--poet-muted)]"
                        >
                            You are not in any groups yet.
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
