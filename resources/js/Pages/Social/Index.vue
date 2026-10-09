<script setup>
import {
    computed,
    ref
} from 'vue';

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

const activeTab =
    ref('following');

const followerRail = ref(null);

function scrollFollowerRail(event) {
    const rail =
        followerRail.value;

    if (!rail) {
        return;
    }

    if (
        Math.abs(event.deltaY) <=
        Math.abs(event.deltaX)
    ) {
        return;
    }

    event.preventDefault();

    rail.scrollBy({
        left: event.deltaY,
        behavior: 'smooth'
    });
}

const tabs = computed(() => [
    {
        value: 'following',
        label: 'Following',
        count: props.followings.length
    },
    {
        value: 'groups',
        label: 'Groups',
        count: props.groups.length
    }
]);
</script>

<template>
    <Head title="Social" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-hidden bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto flex h-full max-w-6xl flex-col px-4 pb-4 pt-6 sm:px-6 lg:px-8"
            >
                <header class="shrink-0">
                    <div
                        class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                    >
                        Social
                    </div>

                    <div
                        class="mt-1 flex flex-wrap items-end justify-between gap-3"
                    >
                        <div>
                            <h1
                                class="font-serif text-3xl font-semibold text-[var(--poet-text)]"
                            >
                                Your writing circle.
                            </h1>

                            <p
                                class="mt-1 text-sm text-[var(--poet-muted)]"
                            >
                                Keep up with writers you follow and the spaces you share.
                            </p>
                        </div>

                        <div
                            class="text-xs text-[var(--poet-muted)]"
                        >
                            {{
                                followings.length
                            }}
                            following ·
                            {{
                                followers.length
                            }}
                            followers ·
                            {{
                                groups.length
                            }}
                            groups
                        </div>
                    </div>
                </header>

                <section
                    class="mt-5 shrink-0 rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] px-4 py-4"
                >
                    <div
                        class="mb-3 flex items-center justify-between gap-3"
                    >
                        <div>
                            <h2
                                class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                            >
                                Followers
                            </h2>

                            <p
                                class="text-xs text-[var(--poet-muted)]"
                            >
                                People who follow your writing.
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-[var(--poet-surface-soft)] px-2.5 py-1 text-xs text-[var(--poet-muted)]"
                        >
                            {{ followers.length }}
                        </span>
                    </div>

                    <div
                        v-if="followers.length"
                        ref="followerRail"
                        class="scrollbar-hidden flex snap-x gap-4 overflow-x-auto pb-1"
                        @wheel="scrollFollowerRail"
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
                            class="group w-20 shrink-0 snap-start text-center"
                        >
                            <div
                                class="mx-auto h-14 w-14 overflow-hidden rounded-full p-[2px] ring-1 ring-[var(--poet-border)] transition group-hover:ring-[var(--poet-accent)]"
                            >
                                <img
                                    :src="user.avatar_url"
                                    :alt="user.name"
                                    class="h-full w-full rounded-full object-cover"
                                />
                            </div>

                            <div
                                class="mt-2 truncate text-xs font-medium text-[var(--poet-text)]"
                            >
                                {{ user.name }}
                            </div>

                            <div
                                class="truncate text-[10px] text-[var(--poet-muted)]"
                            >
                                @{{ user.username }}
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
                    class="mt-5 flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)]"
                >
                    <div
                        class="flex shrink-0 items-center border-b border-[var(--poet-border)] px-4"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            type="button"
                            @click="
                                activeTab =
                                    tab.value
                            "
                            :class="[
                                'relative flex items-center gap-2 px-4 py-4 text-sm font-medium transition',
                                activeTab === tab.value
                                    ? 'text-[var(--poet-text)]'
                                    : 'text-[var(--poet-muted)] hover:text-[var(--poet-text)]'
                            ]"
                        >
                            {{ tab.label }}

                            <span
                                class="rounded-full bg-[var(--poet-surface-soft)] px-2 py-0.5 text-[11px]"
                            >
                                {{ tab.count }}
                            </span>

                            <span
                                v-if="
                                    activeTab ===
                                    tab.value
                                "
                                class="absolute inset-x-4 bottom-0 h-0.5 rounded-full bg-[var(--poet-accent)]"
                            />
                        </button>
                    </div>

                    <div
                        class="scrollbar-hidden min-h-0 flex-1 overflow-y-auto p-4"
                    >
                        <div
                            v-if="
                                activeTab ===
                                    'following' &&
                                followings.length
                            "
                            class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
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
                                class="group flex min-w-0 items-center gap-3 rounded-xl border border-transparent px-3 py-3 transition hover:border-[var(--poet-border)] hover:bg-[var(--poet-surface-soft)]"
                            >
                                <img
                                    :src="user.avatar_url"
                                    :alt="user.name"
                                    class="h-11 w-11 shrink-0 rounded-full object-cover"
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
                            v-else-if="
                                activeTab ===
                                    'groups' &&
                                groups.length
                            "
                            class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3"
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
                                class="group flex min-w-0 items-center gap-3 rounded-xl border border-transparent px-3 py-3 transition hover:border-[var(--poet-border)] hover:bg-[var(--poet-surface-soft)]"
                            >
                                <img
                                    v-if="group.thumbnail_url"
                                    :src="group.thumbnail_url"
                                    :alt="group.name"
                                    class="h-11 w-11 shrink-0 rounded-full object-cover"
                                />

                                <div
                                    v-else
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[var(--poet-accent-soft)] font-serif text-sm font-semibold text-[var(--poet-accent)]"
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
                                            group.role ===
                                                'admin'
                                                ? 'Admin'
                                                : 'Member'
                                        }}
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-else
                            class="flex h-full min-h-52 items-center justify-center"
                        >
                            <div
                                class="max-w-sm text-center"
                            >
                                <div
                                    class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                                >
                                    {{
                                        activeTab ===
                                            'following'
                                            ? 'Not following anyone yet.'
                                            : 'No groups yet.'
                                    }}
                                </div>

                                <p
                                    class="mt-1 text-sm text-[var(--poet-muted)]"
                                >
                                    {{
                                        activeTab ===
                                            'following'
                                            ? 'Writers you choose to follow will appear here.'
                                            : 'Groups you join will appear here.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
