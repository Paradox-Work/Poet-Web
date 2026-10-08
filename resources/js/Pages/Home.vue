<script setup>
import {
    computed
} from 'vue';

import {
    Head,
    router
} from '@inertiajs/vue3';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import PostList
    from '@/Components/app/PostList.vue';

const props = defineProps({
    posts: Object,

    feedFilters: {
        type: Object,
        default: () => ({
            feed: 'poems',
            source: 'for_you',
            genre: null
        })
    },

    genreCounts: {
        type: Array,
        default: () => []
    }
});

const sourceOptions = [
    {
        value: 'for_you',
        label: 'For You'
    },
    {
        value: 'following',
        label: 'Following'
    },
    {
        value: 'groups',
        label: 'Groups'
    },
    {
        value: 'mine',
        label: 'My Art'
    }
];

const publicationWord =
    computed(() => {
        if (
            props.feedFilters.feed ===
            'posts'
        ) {
            return 'post';
        }

        if (
            props.feedFilters.feed ===
            'all'
        ) {
            return 'publication';
        }

        return 'poem';
    });

const feedLabel =
    computed(() => {
        const typeLabel = {
            poems: 'Poems',
            posts: 'Posts',
            all: 'Everything'
        }[
            props.feedFilters.feed
                ?? 'poems'
        ];

        return props.feedFilters.genre
            ? `${typeLabel} · ${props.feedFilters.genre}`
            : typeLabel;
    });

function navigateFilters({
    feed =
        props.feedFilters.feed
            ?? 'poems',

    source =
        props.feedFilters.source
            ?? 'for_you',

    genre =
        props.feedFilters.genre
            ?? null
} = {}) {
    const query = {
        feed,
        source
    };

    if (
        genre &&
        feed !== 'posts'
    ) {
        query.genre = genre;
    }

    router.get(
        route('dashboard'),
        query,
        {
            preserveState: false,
            preserveScroll: false,
            replace: true
        }
    );
}

function changeFeed(event) {
    const feed =
        event.target.value;

    navigateFilters({
        feed,
        genre:
            feed === 'posts'
                ? null
                : props.feedFilters.genre
    });
}

function changeGenre(event) {
    navigateFilters({
        genre:
            event.target.value ||
            null
    });
}

function changeSource(source) {
    if (
        source ===
        props.feedFilters.source
    ) {
        return;
    }

    navigateFilters({
        source
    });
}
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
                    class="flex shrink-0 items-end justify-between gap-6 px-[2%] lg:px-[7%]"
                >
                    <div class="min-w-0">
                        <div
                            class="truncate text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                        >
                            Feed · {{ feedLabel }}
                        </div>

                        <div
                            class="mt-1 font-serif text-2xl font-semibold text-[var(--poet-text)]"
                        >
                            Read one
                            {{ publicationWord }}
                            at a time.
                        </div>
                    </div>

                    <div
                        class="flex shrink-0 items-center gap-2"
                    >
                        <label
                            class="sr-only"
                            for="feed-type"
                        >
                            Feed type
                        </label>

                        <select
                            id="feed-type"
                            :value="feedFilters.feed"
                            @change="changeFeed"
                            class="rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-3 pr-9 text-sm font-medium text-[var(--poet-text)] shadow-sm transition hover:border-[var(--poet-border-strong)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                        >
                            <option value="poems">
                                Poems
                            </option>

                            <option value="posts">
                                Posts
                            </option>

                            <option value="all">
                                Everything
                            </option>
                        </select>

                        <label
                            class="sr-only"
                            for="feed-genre"
                        >
                            Poem genre
                        </label>

                        <select
                            id="feed-genre"
                            :value="
                                feedFilters.genre
                                    ?? ''
                            "
                            :disabled="
                                feedFilters.feed ===
                                'posts'
                            "
                            @change="changeGenre"
                            class="max-w-[220px] rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-3 pr-9 text-sm text-[var(--poet-text)] shadow-sm transition hover:border-[var(--poet-border-strong)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)] disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <option value="">
                                All genres
                            </option>

                            <option
                                v-for="genre in genreCounts"
                                :key="genre.name"
                                :value="genre.name"
                            >
                                {{
                                    `${genre.name} (${genre.count})`
                                }}
                            </option>
                        </select>
                    </div>
                </div>

                <nav
                    class="flex shrink-0 justify-center pt-5"
                    aria-label="Feed source"
                >
                    <div
                        class="flex items-center gap-7 sm:gap-10"
                    >
                        <button
                            v-for="source in sourceOptions"
                            :key="source.value"
                            type="button"
                            @click="
                                changeSource(
                                    source.value
                                )
                            "
                            :class="[
                                'relative pb-2 text-sm font-medium transition',
                                feedFilters.source === source.value
                                    ? 'text-[var(--poet-text)]'
                                    : 'text-[var(--poet-muted)] hover:text-[var(--poet-text)]'
                            ]"
                        >
                            {{ source.label }}

                            <span
                                v-if="
                                    feedFilters.source ===
                                    source.value
                                "
                                class="absolute inset-x-1 -bottom-px h-0.5 rounded-full bg-[var(--poet-accent)]"
                            />
                        </button>
                    </div>
                </nav>

                <div class="min-h-0 flex-1">
                    <PostList
                        :key="
                            `${feedFilters.source}-${feedFilters.feed}-${feedFilters.genre ?? 'all'}`
                        "
                        :posts="posts"
                        mode="deck"
                    />
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
