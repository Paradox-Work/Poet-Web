<script setup>
import {
    nextTick,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch
} from 'vue';

import axios from 'axios';

import PostItem
    from '@/Components/app/PostItem.vue';

import PostModal
    from '@/Components/app/PostModal.vue';

import AttachmentPreviewModal
    from '@/Components/app/AttachmentPreviewModal.vue';

const props = defineProps({
    posts: {
        type: Object,
        required: true
    },

    mode: {
        type: String,
        default: 'list'
    }
});

const feedState = reactive({
    posts: [
        ...(props.posts.data ?? [])
    ],

    nextPageUrl:
        props.posts.links?.next
        ?? null,

    loadedBeyondFirstPage: false
});

const loadingMore = ref(false);
const loadMoreIntersect = ref(null);
const postListContainer = ref(null);
const deckTrack = ref(null);
const activeIndex = ref(0);

let observer = null;
let wheelLocked = false;
let pointerStartX = null;
let pointerDeltaX = 0;

const showEditModal = ref(false);
const editPost = ref({});

const showAttachmentsModal = ref(false);

const previewAttachmentsPost = ref({
    post: null,
    index: 0
});

function openEditModal(post) {
    editPost.value = post;
    showEditModal.value = true;
}

function openAttachmentPreviewModal(
    post,
    index
) {
    previewAttachmentsPost.value = {
        post,
        index
    };

    showAttachmentsModal.value = true;
}

async function loadMore() {
    if (
        !feedState.nextPageUrl ||
        loadingMore.value
    ) {
        return;
    }

    loadingMore.value = true;

    try {
        const { data } =
            await axios.get(
                feedState.nextPageUrl,
                {
                    headers: {
                        Accept:
                            'application/json'
                    }
                }
            );

        const existingIds =
            new Set(
                feedState.posts.map(
                    post => post.id
                )
            );

        const newPosts =
            (data.data ?? [])
                .filter(
                    post =>
                        !existingIds.has(
                            post.id
                        )
                );

        feedState.posts.push(
            ...newPosts
        );

        feedState.nextPageUrl =
            data.links?.next
            ?? null;

        feedState.loadedBeyondFirstPage =
            true;

    } catch (error) {
        console.error(
            'Failed to load more posts:',
            error
        );

    } finally {
        loadingMore.value = false;
    }
}

watch(
    () => props.posts,

    posts => {
        const incomingPosts =
            posts?.data ?? [];

        const incomingById =
            new Map(
                incomingPosts.map(
                    post => [
                        post.id,
                        post
                    ]
                )
            );

        feedState.posts =
            feedState.posts.map(
                post =>
                    incomingById.get(
                        post.id
                    )
                    ?? post
            );

        const existingIds =
            new Set(
                feedState.posts.map(
                    post => post.id
                )
            );

        const newPosts =
            incomingPosts.filter(
                post =>
                    !existingIds.has(
                        post.id
                    )
            );

        if (newPosts.length) {
            feedState.posts.unshift(
                ...newPosts
            );

            if (
                props.mode === 'deck'
            ) {
                activeIndex.value +=
                    newPosts.length;
            }
        }

        if (
            !feedState.loadedBeyondFirstPage
        ) {
            feedState.nextPageUrl =
                posts?.links?.next
                ?? null;
        }
    }
);

function deckCards() {
    return Array.from(
        deckTrack.value
            ?.querySelectorAll(
                '[data-deck-card]'
            )
        ?? []
    );
}

function scrollToDeckIndex(
    index,
    behavior = 'smooth'
) {
    const cards =
        deckCards();

    if (!cards.length) {
        return;
    }

    const nextIndex =
        Math.max(
            0,
            Math.min(
                index,
                cards.length - 1
            )
        );

    activeIndex.value =
        nextIndex;

    cards[nextIndex]
        ?.scrollIntoView({
            behavior,
            block: 'nearest',
            inline: 'center'
        });

    if (
        nextIndex >=
            feedState.posts.length - 2
    ) {
        loadMore();
    }
}

function nextPost() {
    scrollToDeckIndex(
        activeIndex.value + 1
    );
}

function previousPost() {
    scrollToDeckIndex(
        activeIndex.value - 1
    );
}

function syncDeckIndex() {
    const track =
        deckTrack.value;

    const cards =
        deckCards();

    if (
        !track ||
        !cards.length
    ) {
        return;
    }

    const center =
        track.scrollLeft +
        track.clientWidth / 2;

    let nearest = 0;
    let nearestDistance =
        Number.POSITIVE_INFINITY;

    cards.forEach(
        (card, index) => {
            const cardCenter =
                card.offsetLeft +
                card.offsetWidth / 2;

            const distance =
                Math.abs(
                    cardCenter -
                    center
                );

            if (
                distance <
                nearestDistance
            ) {
                nearestDistance =
                    distance;

                nearest =
                    index;
            }
        }
    );

    activeIndex.value =
        nearest;

    if (
        nearest >=
            feedState.posts.length - 2
    ) {
        loadMore();
    }
}

function onDeckWheel(event) {
    const target =
        event.target;

    if (
        target?.closest(
            'textarea, input, select, [contenteditable="true"], .comment-list'
        )
    ) {
        return;
    }

    if (
        Math.abs(event.deltaY) <
            8 &&
        Math.abs(event.deltaX) <
            8
    ) {
        return;
    }

    event.preventDefault();

    if (wheelLocked) {
        return;
    }

    wheelLocked = true;

    const direction =
        Math.abs(event.deltaX) >
        Math.abs(event.deltaY)
            ? Math.sign(
                event.deltaX
            )
            : Math.sign(
                event.deltaY
            );

    if (direction > 0) {
        nextPost();
    } else {
        previousPost();
    }

    window.setTimeout(
        () => {
            wheelLocked = false;
        },
        420
    );
}

function onDeckKeydown(event) {
    if (
        props.mode !== 'deck'
    ) {
        return;
    }

    if (
        event.key ===
        'ArrowRight'
    ) {
        nextPost();
    }

    if (
        event.key ===
        'ArrowLeft'
    ) {
        previousPost();
    }
}

function onPointerDown(event) {
    if (
        event.pointerType ===
            'mouse' &&
        event.button !== 0
    ) {
        return;
    }

    pointerStartX =
        event.clientX;

    pointerDeltaX = 0;
}

function onPointerMove(event) {
    if (
        pointerStartX === null
    ) {
        return;
    }

    pointerDeltaX =
        event.clientX -
        pointerStartX;
}

function onPointerUp() {
    if (
        pointerStartX === null
    ) {
        return;
    }

    if (
        Math.abs(pointerDeltaX) >
        70
    ) {
        if (
            pointerDeltaX < 0
        ) {
            nextPost();
        } else {
            previousPost();
        }
    }

    pointerStartX = null;
    pointerDeltaX = 0;
}

onMounted(() => {
    window.addEventListener(
        'keydown',
        onDeckKeydown
    );

    if (
        props.mode === 'list'
    ) {
        observer =
            new IntersectionObserver(
                entries => {
                    if (
                        entries.some(
                            entry =>
                                entry.isIntersecting
                        )
                    ) {
                        loadMore();
                    }
                },
                {
                    root:
                        postListContainer.value,

                    rootMargin:
                        '0px 0px 300px 0px'
                }
            );

        if (
            loadMoreIntersect.value
        ) {
            observer.observe(
                loadMoreIntersect.value
            );
        }
    }

    if (
        props.mode === 'deck'
    ) {
        nextTick(
            () =>
                scrollToDeckIndex(
                    0,
                    'auto'
                )
        );
    }
});

onBeforeUnmount(() => {
    observer?.disconnect();

    window.removeEventListener(
        'keydown',
        onDeckKeydown
    );
});

function handlePinChanged({
    postId,
    pinned
}) {
    if (pinned) {
        const pinnedPost =
            feedState.posts.find(
                post =>
                    post.id === postId
            );

        if (!pinnedPost) {
            return;
        }

        feedState.posts = [
            pinnedPost,
            ...feedState.posts.filter(
                post =>
                    post.id !== postId
            )
        ];

        return;
    }

    feedState.posts = [
        ...feedState.posts
    ].sort(
        (a, b) =>
            String(b.created_at)
                .localeCompare(
                    String(a.created_at)
                )
    );
}

function removePost(postId) {
    const removedIndex =
        feedState.posts.findIndex(
            post =>
                post.id === postId
        );

    feedState.posts =
        feedState.posts.filter(
            post =>
                post.id !== postId
        );

    if (
        props.mode === 'deck'
    ) {
        activeIndex.value =
            Math.max(
                0,
                Math.min(
                    activeIndex.value,
                    feedState.posts.length - 1
                )
            );

        nextTick(
            () =>
                scrollToDeckIndex(
                    activeIndex.value,
                    'auto'
                )
        );
    }
}
</script>

<template>
    <div
        v-if="mode === 'deck'"
        class="relative"
    >
        <div
            v-if="feedState.posts.length"
            ref="deckTrack"
            class="scrollbar-hidden flex snap-x snap-mandatory gap-5 overflow-x-auto overscroll-x-contain px-[7%] py-3"
            tabindex="0"
            @scroll.passive="syncDeckIndex"
            @wheel="onDeckWheel"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerUp"
        >
            <div
                v-for="(post, index) in feedState.posts"
                :key="post.id"
                data-deck-card
                class="w-[86%] shrink-0 snap-center sm:w-[78%] lg:w-[72%]"
                :class="
                    index === activeIndex
                        ? 'opacity-100'
                        : 'opacity-60'
                "
            >
                <div
                    class="transition duration-300"
                    :class="[
                        index === activeIndex
                            ? 'scale-100'
                            : 'scale-[0.965]',
                        index === activeIndex
                            ? ''
                            : 'pointer-events-none'
                    ]"
                >
                    <PostItem
                        :post="post"
                        :deck-navigation="
                            index === activeIndex
                        "
                        :deck-preview="
                            index !== activeIndex
                        "
                        :can-go-previous="
                            activeIndex > 0
                        "
                        :can-go-next="
                            activeIndex <
                                feedState.posts.length - 1 ||
                            Boolean(
                                feedState.nextPageUrl
                            )
                        "
                        @previous="
                            previousPost
                        "
                        @next="
                            nextPost
                        "
                        @editClick="
                            openEditModal
                        "
                        @attachmentClick="
                            openAttachmentPreviewModal
                        "
                        @deleted="
                            removePost
                        "
                        @pinChanged="
                            handlePinChanged
                        "
                    />
                </div>
            </div>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-[var(--poet-border)] bg-[var(--poet-surface)] px-6 py-16 text-center text-sm text-[var(--poet-muted)]"
        >
            Nothing in your feed yet.
        </div>

        <template
            v-if="feedState.posts.length"
        >
            <div
                class="mt-2 flex items-center justify-center gap-3 text-xs text-[var(--poet-muted)]"
            >
                <span>
                    {{ activeIndex + 1 }}
                    /
                    {{ feedState.posts.length }}
                </span>

                <span
                    class="hidden sm:inline"
                >
                    swipe · wheel · arrow keys
                </span>

                <span
                    v-if="loadingMore"
                >
                    loading more…
                </span>
            </div>
        </template>

        <PostModal
            :post="editPost"
            v-model="showEditModal"
        />

        <AttachmentPreviewModal
            :attachments="
                previewAttachmentsPost
                    .post
                    ?.attachments
                ?? []
            "
            v-model:index="
                previewAttachmentsPost.index
            "
            v-model="
                showAttachmentsModal
            "
        />
    </div>

    <div
        v-else
        ref="postListContainer"
        class="scrollbar-hidden flex-1 overflow-auto"
        scroll-region
    >
        <PostItem
            v-for="post of feedState.posts"
            :key="post.id"
            :post="post"
            @editClick="
                openEditModal
            "
            @attachmentClick="
                openAttachmentPreviewModal
            "
            @deleted="
                removePost
            "
            @pinChanged="
                handlePinChanged
            "
        />

        <div
            ref="loadMoreIntersect"
            class="h-px"
            aria-hidden="true"
        />

        <div
            v-if="loadingMore"
            class="py-3 text-center text-sm text-gray-400"
        >
            Loading more posts...
        </div>

        <PostModal
            :post="editPost"
            v-model="showEditModal"
        />

        <AttachmentPreviewModal
            :attachments="
                previewAttachmentsPost
                    .post
                    ?.attachments
                ?? []
            "
            v-model:index="
                previewAttachmentsPost.index
            "
            v-model="
                showAttachmentsModal
            "
        />
    </div>
</template>
