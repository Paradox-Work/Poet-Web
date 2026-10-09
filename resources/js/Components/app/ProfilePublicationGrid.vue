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
        class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4"
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
            class="group relative h-36 overflow-hidden rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface)] transition duration-200 hover:-translate-y-0.5 hover:border-[var(--poet-border-strong)] hover:shadow-lg sm:h-40"
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
                class="absolute inset-0 flex flex-col p-3"
            >
                <div
                    class="flex items-start justify-between gap-2"
                >
                    <div
                        class="flex min-w-0 flex-wrap gap-1"
                    >
                        <span
                            :class="[
                                'rounded-full px-2 py-0.5 text-[10px] font-medium',
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
                                post.type ===
                                    'poem' &&
                                post.poem_genres?.length
                            "
                            class="max-w-24 truncate rounded-full bg-black/30 px-2 py-0.5 text-[10px] text-white/85"
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
                                ? 'font-serif text-base'
                                : 'text-sm'
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
                            'mt-1 truncate text-[11px] italic',
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
                            'mt-2 flex items-center gap-3 text-[10px]',
                            firstVisual(post)
                                ? 'text-white/75'
                                : 'text-[var(--poet-muted)]'
                        ]"
                    >
                        <span
                            class="flex items-center gap-1"
                        >
                            <HeartIcon
                                class="h-3.5 w-3.5"
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
                                class="h-3.5 w-3.5"
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
