<script setup>
import {
    computed,
    ref
} from 'vue';

import {
    Head,
    Link,
    router
} from '@inertiajs/vue3';

import {
    MagnifyingGlassIcon,
    PlusIcon,
    UserGroupIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import CompactPaginator
    from '@/Components/app/CompactPaginator.vue';

import GroupModal
    from '@/Components/app/GroupModal.vue';

const props = defineProps({
    joinedGroups: {
        type: Array,
        default: () => []
    },

    discoverGroups: {
        type: Object,
        default: () => ({
            data: [],
            meta: null
        })
    },

    joinedCount: {
        type: Number,
        default: 0
    },

    search: {
        type: String,
        default: ''
    },

    success: {
        type: String,
        default: ''
    }
});

const searchInput =
    ref(props.search);

const showGroupModal =
    ref(false);

const joiningGroupId =
    ref(null);

const joinedRail =
    ref(null);

const hasSearch =
    computed(() =>
        props.search.trim() !== ''
    );

function scrollJoinedRail(event) {
    const rail =
        joinedRail.value;

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

function searchGroups() {
    const value =
        searchInput.value.trim();

    router.get(
        route('group.index'),
        value
            ? {
                search: value
            }
            : {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}

function clearSearch() {
    searchInput.value = '';

    router.get(
        route('group.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}

function goToPage(page) {
    const query = {
        groups_page: page
    };

    if (searchInput.value.trim()) {
        query.search =
            searchInput.value.trim();
    }

    router.get(
        route('group.index'),
        query,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}

function joinGroup(group) {
    if (
        joiningGroupId.value ||
        group.status === 'pending'
    ) {
        return;
    }

    joiningGroupId.value =
        group.id;

    router.post(
        route(
            'group.join',
            group.slug
        ),
        {},
        {
            preserveScroll: true,

            onFinish: () => {
                joiningGroupId.value =
                    null;
            }
        }
    );
}

function onGroupCreated() {
    router.reload({
        only: [
            'joinedGroups',
            'discoverGroups',
            'joinedCount'
        ]
    });
}

function groupActionLabel(group) {
    if (
        joiningGroupId.value ===
        group.id
    ) {
        return group.auto_approval
            ? 'Joining...'
            : 'Sending...';
    }

    if (group.status === 'pending') {
        return 'Pending';
    }

    return group.auto_approval
        ? 'Join'
        : 'Request to join';
}

function membershipLabel(group) {
    if (group.role === 'admin') {
        return 'Admin';
    }

    return 'Member';
}
</script>

<template>
    <Head title="Groups" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-y-auto bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto w-full max-w-6xl px-4 pb-10 pt-6 sm:px-6 lg:px-8"
            >
                <div
                    v-if="success"
                    class="mb-4 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-medium text-white"
                >
                    {{ success }}
                </div>

                <header
                    class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <div
                            class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                        >
                            Groups
                        </div>

                        <h1
                            class="mt-1 font-serif text-3xl font-semibold text-[var(--poet-text)]"
                        >
                            Your writing spaces.
                        </h1>

                        <p
                            class="mt-1 max-w-xl text-sm text-[var(--poet-muted)]"
                        >
                            Return to the groups you know, or find a new place to write with others.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="
                            showGroupModal = true
                        "
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-[var(--poet-accent)] px-4 py-2.5 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <PlusIcon class="h-4 w-4" />
                        New group
                    </button>
                </header>

                <section class="mt-7">
                    <div
                        class="mb-3 flex items-end justify-between gap-3"
                    >
                        <div>
                            <h2
                                class="font-serif text-xl font-semibold text-[var(--poet-text)]"
                            >
                                Your groups
                            </h2>

                            <p
                                class="text-xs text-[var(--poet-muted)]"
                            >
                                {{ joinedCount }}
                                joined writing space{{ joinedCount === 1 ? '' : 's' }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="joinedGroups.length"
                        ref="joinedRail"
                        class="scrollbar-hidden flex snap-x gap-3 overflow-x-auto pb-2"
                        @wheel="scrollJoinedRail"
                    >
                        <Link
                            v-for="group in joinedGroups"
                            :key="group.id"
                            :href="
                                route(
                                    'group.profile',
                                    group.slug
                                )
                            "
                            class="group w-44 shrink-0 snap-start overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] transition hover:-translate-y-0.5 hover:border-[var(--poet-border-strong)] hover:shadow-md sm:w-52"
                        >
                            <div
                                class="relative h-20 overflow-hidden bg-[var(--poet-surface-soft)]"
                            >
                                <img
                                    v-if="group.cover_url"
                                    :src="group.cover_url"
                                    :alt="group.name"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                />

                                <div
                                    v-else
                                    class="flex h-full items-center justify-center"
                                >
                                    <UserGroupIcon
                                        class="h-8 w-8 text-[var(--poet-muted)]"
                                    />
                                </div>
                            </div>

                            <div class="p-3">
                                <div
                                    class="truncate text-sm font-semibold text-[var(--poet-text)]"
                                >
                                    {{ group.name }}
                                </div>

                                <div
                                    class="mt-1 flex items-center justify-between gap-2 text-[11px] text-[var(--poet-muted)]"
                                >
                                    <span>
                                        {{ group.member_count }}
                                        member{{ group.member_count === 1 ? '' : 's' }}
                                    </span>

                                    <span
                                        class="rounded-full bg-[var(--poet-accent-soft)] px-2 py-0.5 text-[var(--poet-accent)]"
                                    >
                                        {{
                                            membershipLabel(
                                                group
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-[var(--poet-border)] bg-[var(--poet-surface)] px-5 py-8 text-center"
                    >
                        <UserGroupIcon
                            class="mx-auto h-8 w-8 text-[var(--poet-muted)]"
                        />

                        <div
                            class="mt-2 font-medium text-[var(--poet-text)]"
                        >
                            You haven't joined a group yet.
                        </div>

                        <p
                            class="mt-1 text-sm text-[var(--poet-muted)]"
                        >
                            Discover a writing space below or start your own.
                        </p>
                    </div>
                </section>

                <section class="mt-8">
                    <div
                        class="flex flex-col gap-3 border-b border-[var(--poet-border)] pb-4 md:flex-row md:items-end md:justify-between"
                    >
                        <div>
                            <h2
                                class="font-serif text-xl font-semibold text-[var(--poet-text)]"
                            >
                                Discover
                            </h2>

                            <p
                                class="text-xs text-[var(--poet-muted)]"
                            >
                                Find groups by name or what they write about.
                            </p>
                        </div>

                        <form
                            class="flex w-full items-center gap-2 md:w-auto"
                            @submit.prevent="searchGroups"
                        >
                            <div
                                class="relative min-w-0 flex-1 md:w-72"
                            >
                                <MagnifyingGlassIcon
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--poet-muted)]"
                                />

                                <input
                                    v-model="searchInput"
                                    type="search"
                                    placeholder="Search groups..."
                                    class="w-full rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-9 pr-4 text-sm text-[var(--poet-text)] placeholder:text-[var(--poet-muted)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                                />
                            </div>

                            <button
                                type="submit"
                                class="rounded-full bg-[var(--poet-accent)] px-4 py-2 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:shadow"
                            >
                                Search
                            </button>

                            <button
                                v-if="hasSearch"
                                type="button"
                                @click="clearSearch"
                                class="rounded-full border border-[var(--poet-border)] px-3 py-2 text-sm text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                            >
                                Clear
                            </button>
                        </form>
                    </div>

                    <div
                        v-if="
                            discoverGroups
                                .data
                                ?.length
                        "
                        class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <article
                            v-for="group in discoverGroups.data"
                            :key="group.id"
                            class="overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)]"
                        >
                            <Link
                                :href="
                                    route(
                                        'group.profile',
                                        group.slug
                                    )
                                "
                                class="block"
                            >
                                <div
                                    class="h-28 overflow-hidden bg-[var(--poet-surface-soft)]"
                                >
                                    <img
                                        v-if="group.cover_url"
                                        :src="group.cover_url"
                                        :alt="group.name"
                                        class="h-full w-full object-cover"
                                    />

                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center"
                                    >
                                        <UserGroupIcon
                                            class="h-9 w-9 text-[var(--poet-muted)]"
                                        />
                                    </div>
                                </div>
                            </Link>

                            <div class="p-4">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0">
                                        <Link
                                            :href="
                                                route(
                                                    'group.profile',
                                                    group.slug
                                                )
                                            "
                                            class="block truncate font-serif text-lg font-semibold text-[var(--poet-text)] hover:text-[var(--poet-accent)]"
                                        >
                                            {{ group.name }}
                                        </Link>

                                        <div
                                            class="mt-1 text-xs text-[var(--poet-muted)]"
                                        >
                                            {{ group.member_count }}
                                            member{{ group.member_count === 1 ? '' : 's' }}
                                        </div>
                                    </div>

                                    <span
                                        v-if="group.status === 'pending'"
                                        class="shrink-0 rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-medium text-amber-500"
                                    >
                                        Pending
                                    </span>
                                </div>

                                <p
                                    class="group-description mt-3 min-h-10 text-sm leading-relaxed text-[var(--poet-muted)]"
                                >
                                    {{
                                        group.description ||
                                        'No description yet.'
                                    }}
                                </p>

                                <div
                                    class="mt-4 flex items-center justify-between gap-3"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'group.profile',
                                                group.slug
                                            )
                                        "
                                        class="text-sm font-medium text-[var(--poet-text)] transition hover:text-[var(--poet-accent)]"
                                    >
                                        View group
                                    </Link>

                                    <button
                                        type="button"
                                        :disabled="
                                            group.status === 'pending' ||
                                            joiningGroupId === group.id
                                        "
                                        @click="joinGroup(group)"
                                        class="rounded-full bg-[var(--poet-accent-soft)] px-3 py-2 text-xs font-semibold text-[var(--poet-accent)] transition hover:bg-[var(--poet-accent)] hover:text-white disabled:pointer-events-none disabled:opacity-55"
                                    >
                                        {{
                                            groupActionLabel(
                                                group
                                            )
                                        }}
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div
                        v-else
                        class="mt-4 rounded-2xl border border-dashed border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center"
                    >
                        <div
                            class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                        >
                            No groups found.
                        </div>

                        <p
                            class="mt-1 text-sm text-[var(--poet-muted)]"
                        >
                            Try another search or create a new writing space.
                        </p>
                    </div>

                    <div class="mt-6">
                        <CompactPaginator
                            :meta="
                                discoverGroups.meta
                            "
                            label="Group pages"
                            @page="goToPage"
                        />
                    </div>
                </section>
            </div>
        </div>

        <GroupModal
            v-model="showGroupModal"
            @created="onGroupCreated"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.group-description {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>
