<script setup>
import {
    Link
} from '@inertiajs/vue3';

import {
    ChatBubbleOvalLeftIcon,
    HeartIcon,
    MapPinIcon,
    PlayIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    posts: {
        type: Array,
        default: () => []
    },

    pinnedPostId: {
        type: [
            Number,
            String
        ],
        default: null
    }
});

function plainText(value) {
    return String(value ?? '')
        .replace(
            /<\s*br\s*\/?>/gi,
            ' '
        )
        .replace(
            /<\/p>/gi,
            ' '
        )
        .replace(
            /<[^>]*>/g,
            ' '
        )
        .replace(
            /&nbsp;/g,
            ' '
        )
        .replace(
            /&amp;/g,
            '&'
        )
        .replace(
            /&lt;/g,
            '<'
        )
        .replace(
            /&gt;/g,
            '>'
        )
        .replace(
            /\s+/g,
            ' '
        )
        .trim();
}

function firstVisual(post) {
    return (
        post.attachments ?? []
    ).find(
        attachment =>
            attachment.mime?.startsWith(
                'image/'
            ) ||
            attachment.mime?.startsWith(
                'video/'
            )
    ) ?? null;
}

function isImage(attachment) {
    return attachment?.mime
        ?.startsWith('image/');
}

function isVideo(attachment) {
    return attachment?.mime
        ?.startsWith('video/');
}

function formLabel(value) {
    if (!value) {
        return null;
    }

    return value
        .replaceAll('_', ' ')
        .replace(
            /\b\w/g,
            letter =>
                letter.toUpperCase()
        );
}

function publicationTitle(post) {
    if (post.title?.trim()) {
        return post.title.trim();
    }

    const body =
        plainText(post.body);

    if (body) {
        return body;
    }

    return post.type === 'poem'
        ? 'Untitled poem'
        : 'Untitled post';
}
</script>

<template>
    <div
        class="grid grid-cols-2 gap-1 sm:grid-cols-3 sm:gap-2 lg:grid-cols-4"
    >
        <Link
            v-for="post in posts"
            :key="post.id"
            :href="
                route(
                    'post.view',
                    post.id
                )
            "
            class="group relative h-28 overflow-hidden rounded-lg border border-[var(--poet-border)] bg-[var(--poet-surface)] transition duration-200 hover:-translate-y-0.5 hover:border-[var(--poet-border-strong)] hover:shadow-lg sm:h-40 sm:rounded-xl"
        >
            <template
                v-if="firstVisual(post)"
            >
                <img
                    v-if="
                        isImage(
                            firstVisual(post)
                        )
                    "
                    :src="
                        firstVisual(post).url
                    "
                    :alt="
                        publicationTitle(post)
                    "
                    class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                />

                <video
                    v-else-if="
                        isVideo(
                            firstVisual(post)
                        )
                    "
                    :src="
                        firstVisual(post).url
                    "
                    muted
                    playsinline
                    preload="metadata"
                    class="absolute inset-0 h-full w-full object-cover"
                />

                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/20"
                />

                <div
                    v-if="
                        isVideo(
                            firstVisual(post)
                        )
                    "
                    class="absolute inset-0 flex items-center justify-center"
                >
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-black/45 text-white backdrop-blur-sm"
                    >
                        <PlayIcon
                            class="h-5 w-5"
                        />
                    </span>
                </div>
            </template>

            <div
                v-else
                class="absolute inset-0 bg-[var(--poet-surface-soft)]"
            />

            <div
                class="absolute inset-0 flex flex-col p-2 sm:p-3"
            >
                <div
                    class="flex items-start justify-between gap-2"
                >
                    <div
                        class="flex min-w-0 flex-wrap gap-1"
                    >
                        <span
                            :class="[
                                'rounded-full px-1.5 py-0.5 text-[9px] font-medium sm:px-2 sm:text-[10px]',
                                post.type === 'poem'
                                    ? 'bg-[var(--poet-accent-soft)] text-[var(--poet-accent)]'
                                    : 'bg-sky-950/50 text-sky-200'
                            ]"
                        >
                            {{
                                post.type ===
                                    'poem'
                                    ? 'Poem'
                                    : 'Post'
                            }}
                        </span>

                        <span
                            v-if="
                                post.content_rating ===
                                'mature'
                            "
                            class="rounded-full border border-[var(--poet-gold)]/50 bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-medium text-[var(--poet-gold)] sm:px-2 sm:text-[10px]"
                        >
                            Mature
                        </span>

                        <span
                            v-if="
                                post.type ===
                                    'poem' &&
                                post.poem_genres?.length
                            "
                            class="max-w-20 truncate rounded-full bg-black/30 px-1.5 py-0.5 text-[9px] text-white/85 sm:max-w-24 sm:px-2 sm:text-[10px]"
                        >
                            {{
                                post.poem_genres[0]
                            }}
                        </span>
                    </div>

                    <MapPinIcon
                        v-if="
                            String(
                                pinnedPostId
                            ) ===
                            String(
                                post.id
                            )
                        "
                        class="h-4 w-4 shrink-0 text-[var(--poet-accent)]"
                        aria-label="Pinned publication"
                    />
                </div>

                <div
                    class="mt-auto min-w-0"
                >
                    <div
                        :class="[
                            'publication-excerpt font-semibold',
                            firstVisual(post)
                                ? 'text-white'
                                : 'text-[var(--poet-text)]',
                            post.type === 'poem'
                                ? 'font-serif text-[13px] sm:text-base'
                                : 'text-[11px] sm:text-sm'
                        ]"
                    >
                        {{
                            publicationTitle(
                                post
                            )
                        }}
                    </div>

                    <div
                        v-if="
                            post.type ===
                                'poem' &&
                            post.poem_form
                        "
                        :class="[
                            'mt-0.5 truncate text-[10px] italic sm:mt-1 sm:text-[11px]',
                            firstVisual(post)
                                ? 'text-white/70'
                                : 'text-[var(--poet-muted)]'
                        ]"
                    >
                        {{
                            formLabel(
                                post.poem_form
                            )
                        }}
                    </div>

                    <div
                        :class="[
                            'mt-1.5 flex items-center gap-2 text-[9px] sm:mt-2 sm:gap-3 sm:text-[10px]',
                            firstVisual(post)
                                ? 'text-white/75'
                                : 'text-[var(--poet-muted)]'
                        ]"
                    >
                        <span
                            class="flex items-center gap-1"
                        >
                            <HeartIcon
                                class="h-3 w-3 sm:h-3.5 sm:w-3.5"
                            />
                            {{
                                post.num_of_reactions
                                ?? 0
                            }}
                        </span>

                        <span
                            class="flex items-center gap-1"
                        >
                            <ChatBubbleOvalLeftIcon
                                class="h-3 w-3 sm:h-3.5 sm:w-3.5"
                            />
                            {{
                                post.num_of_comments
                                ?? 0
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </Link>
    </div>
</template>

<style scoped>
.publication-excerpt {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}
</style>
