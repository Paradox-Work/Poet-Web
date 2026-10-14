<script setup>
import {
    computed
} from 'vue';

import {
    Head,
    router,
    usePage
} from '@inertiajs/vue3';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import PostList
    from '@/Components/app/PostList.vue';

import { useI18n } from '@/i18n';

const props = defineProps({
    posts: Object,

    feedFilters: {
        type: Object,
        default: () => ({
            feed: 'poems',
            source: 'for_you',
            genre: null,
            sort: 'latest'
        })
    },

    genreCounts: {
        type: Array,
        default: () => []
    }
});

const authUser = usePage().props.auth.user;
const { locale, t } = useI18n();

const allSourceOptions = [
    {
        value: 'for_you',
        label: 'home.for_you'
    },
    {
        value: 'following',
        label: 'home.following'
    },
    {
        value: 'groups',
        label: 'home.groups'
    },
    {
        value: 'mine',
        label: 'home.mine'
    }
];

const sourceOptions = computed(() =>
    authUser
        ? allSourceOptions
        : [
            {
                value: 'for_you',
                label: 'home.explore'
            }
        ]
);

const activeSourceIndex =
    computed(() =>
        Math.max(
            0,
            sourceOptions.value.findIndex(
                source =>
                    source.value ===
                    props.feedFilters.source
            )
        )
    );

const activeSourceLabel = computed(
    () =>
        sourceOptions.value[
            activeSourceIndex.value
        ]?.label
        ?? 'home.explore'
);

const sourceIndicatorStyle =
    computed(() => ({
        left:
            `calc(${activeSourceIndex.value * (100 / sourceOptions.value.length)}% + ${50 / sourceOptions.value.length}% - 1.25rem)`
    }));

const publicationWord =
    computed(() => {
        if (
            props.feedFilters.feed ===
            'posts'
        ) {
            return t('home.post');
        }

        if (
            props.feedFilters.feed ===
            'all'
        ) {
            return t('home.publication');
        }

        return t('home.poem');
    });

const feedLabel =
    computed(() => {
        const typeLabel = {
            poems: t('home.poems'),
            posts: t('home.posts'),
            all: t('home.everything')
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
            ?? null,

    sort =
        props.feedFilters.sort
            ?? 'latest'
} = {}) {
    const query = {
        feed,
        source,
        sort
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

function changeSort(event) {
    navigateFilters({
        sort: event.target.value
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
    <Head :title="t('nav.home')" />

    <AuthenticatedLayout>
        <div
            class="h-full overflow-hidden bg-[var(--poet-bg)]"
        >
            <section
                class="mx-auto flex h-full min-h-0 max-w-[1560px] flex-col px-4 pb-3 pt-5 sm:px-6 sm:pt-6 lg:px-8"
            >
                <div
                    class="flex shrink-0 flex-col gap-4 px-[2%] lg:flex-row lg:items-end lg:justify-between lg:gap-6 min-[1400px]:px-[7%]"
                >
                    <div class="min-w-0">
                        <div
                            class="truncate text-xs font-semibold uppercase tracking-[0.22em] text-[var(--poet-muted)]"
                        >
                            {{ t('home.feed') }} · {{ feedLabel }}
                            <span class="compact-height-source hidden">
                                · {{ t(activeSourceLabel) }}
                            </span>
                        </div>

                        <div
                            class="mt-1 font-serif text-2xl font-semibold text-[var(--poet-text)]"
                        >
                            {{ t('home.title', { item: publicationWord }) }}
                        </div>
                    </div>

                    <div
                        class="grid w-full shrink-0 grid-cols-1 gap-2 sm:grid-cols-3 lg:flex lg:w-auto lg:items-center"
                    >
                        <label
                            class="sr-only"
                            for="feed-type"
                        >
                            {{ t('home.feed_type') }}
                        </label>

                        <select
                            id="feed-type"
                            :value="feedFilters.feed"
                            @change="changeFeed"
                            class="w-full rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-3 pr-9 text-sm font-medium text-[var(--poet-text)] shadow-sm transition hover:border-[var(--poet-border-strong)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)] lg:w-32"
                        >
                            <option value="poems">
                                {{ t('home.poems') }}
                            </option>

                            <option value="posts">
                                {{ t('home.posts') }}
                            </option>

                            <option value="all">
                                {{ t('home.everything') }}
                            </option>
                        </select>

                        <label
                            class="sr-only"
                            for="feed-sort"
                        >
                            {{ locale === 'lv' ? 'Kārtošana' : 'Sort' }}
                        </label>

                        <select
                            id="feed-sort"
                            :value="feedFilters.sort ?? 'latest'"
                            @change="changeSort"
                            class="w-full rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-3 pr-9 text-sm text-[var(--poet-text)] shadow-sm transition hover:border-[var(--poet-border-strong)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)] lg:w-40"
                        >
                            <option value="latest">
                                {{ locale === 'lv' ? 'Jaunākie' : 'Newest' }}
                            </option>
                            <option value="popular">
                                {{ locale === 'lv' ? 'Populārākie' : 'Most liked' }}
                            </option>
                            <option value="discussed">
                                {{ locale === 'lv' ? 'Apspriestākie' : 'Most discussed' }}
                            </option>
                        </select>

                        <label
                            class="sr-only"
                            for="feed-genre"
                        >
                            {{ t('home.genre') }}
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
                            class="w-full rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-3 pr-9 text-sm text-[var(--poet-text)] shadow-sm transition hover:border-[var(--poet-border-strong)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)] disabled:cursor-not-allowed disabled:opacity-40 lg:w-44"
                        >
                            <option value="">
                                {{ locale === 'lv' ? 'Visi žanri' : 'All genres' }}
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
                    class="home-source-tabs flex shrink-0 justify-center pb-3 pt-4 sm:pb-2 sm:pt-5"
                    aria-label="Feed source"
                >
                    <div
                        :class="[
                            'relative grid w-full max-w-sm',
                            authUser ? 'grid-cols-4' : 'grid-cols-1'
                        ]"
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
                                'min-w-0 px-1 pb-2 text-xs font-medium transition-colors duration-200 sm:text-sm',
                                feedFilters.source === source.value
                                    ? 'text-[var(--poet-text)]'
                                    : 'text-[var(--poet-muted)] hover:text-[var(--poet-text)]'
                            ]"
                        >
                            {{ t(source.label) }}
                        </button>

                        <span
                            class="pointer-events-none absolute bottom-0 h-0.5 w-10 rounded-full bg-[var(--poet-accent)] transition-[left] duration-300 ease-out"
                            :style="sourceIndicatorStyle"
                            aria-hidden="true"
                        />
                    </div>
                </nav>

                <div class="min-h-0 flex-1">
                    <PostList
                        :key="
                            `${feedFilters.source}-${feedFilters.feed}-${feedFilters.genre ?? 'all'}-${feedFilters.sort ?? 'latest'}`
                        "
                        :posts="posts"
                        mode="deck"
                    />
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
