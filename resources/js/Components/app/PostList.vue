<script setup>
import {
    computed,
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

import PostCommentsPanel
    from '@/Components/app/PostCommentsPanel.vue';

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
const activeIndex = ref(0);
const deckTransitionName = ref('deck-next');
const commentsOpen = ref(false);

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

const activePost = computed(() =>
    feedState.posts[
        activeIndex.value
    ] ?? null
);

const previousPreview = computed(() =>
    activeIndex.value > 0
        ? feedState.posts[
            activeIndex.value - 1
        ]
        : null
);

const nextPreview = computed(() =>
    activeIndex.value <
        feedState.posts.length - 1
        ? feedState.posts[
            activeIndex.value + 1
        ]
        : null
);

const canGoPrevious = computed(
    () => activeIndex.value > 0
);

const canGoNext = computed(
    () =>
        activeIndex.value <
            feedState.posts.length - 1 ||
        Boolean(
            feedState.nextPageUrl
        )
);

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
        return false;
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

        return newPosts.length > 0;

    } catch (error) {
        console.error(
            'Failed to load more posts:',
            error
        );

        return false;

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

function toggleComments() {
    commentsOpen.value =
        !commentsOpen.value;
}

function closeComments() {
    commentsOpen.value = false;
}

function goToIndex(
    index,
    direction
) {
    if (
        index < 0 ||
        index >= feedState.posts.length
    ) {
        return;
    }

    deckTransitionName.value =
        direction === 'previous'
            ? 'deck-previous'
            : 'deck-next';

    commentsOpen.value = false;

    activeIndex.value =
        index;

    if (
        index >=
            feedState.posts.length - 2
    ) {
        loadMore();
    }
}

async function nextPost() {
    const nextIndex =
        activeIndex.value + 1;

    if (
        nextIndex <
        feedState.posts.length
    ) {
        goToIndex(
            nextIndex,
            'next'
        );

        return;
    }

    if (
        feedState.nextPageUrl
    ) {
        const loaded =
            await loadMore();

        if (
            loaded &&
            activeIndex.value + 1 <
                feedState.posts.length
        ) {
            goToIndex(
                activeIndex.value + 1,
                'next'
            );
        }
    }
}

function previousPost() {
    goToIndex(
        activeIndex.value - 1,
        'previous'
    );
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
        Math.abs(event.deltaY) < 8 &&
        Math.abs(event.deltaX) < 8
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
        380
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

        commentsOpen.value = false;
        activeIndex.value = 0;

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

    activeIndex.value =
        Math.min(
            activeIndex.value,
            Math.max(
                0,
                feedState.posts.length - 1
            )
        );
}

function removePost(postId) {
    feedState.posts =
        feedState.posts.filter(
            post =>
                post.id !== postId
        );

    commentsOpen.value = false;

    activeIndex.value =
        Math.max(
            0,
            Math.min(
                activeIndex.value,
                feedState.posts.length - 1
            )
        );
}
</script>

<template>
    <div
        v-if="mode === 'deck'"
        class="flex h-full min-h-0 flex-col"
    >
        <div
            v-if="feedState.posts.length"
            class="flex min-h-0 flex-1 items-center"
            tabindex="0"
            @wheel="onDeckWheel"
            @pointerdown="onPointerDown"
            @pointermove="onPointerMove"
            @pointerup="onPointerUp"
            @pointercancel="onPointerUp"
        >
            <div
                class="mx-auto grid w-full max-w-[1480px] items-center gap-5 px-2 lg:grid-cols-[minmax(190px,0.72fr)_minmax(480px,1.45fr)_minmax(190px,0.72fr)] xl:gap-8"
            >
                <div
                    class="hidden min-w-0 lg:block"
                >
                    <Transition
                        name="preview-fade"
                        mode="out-in"
                    >
                        <div
                            v-if="previousPreview"
                            :key="previousPreview.id"
                            class="pointer-events-none opacity-55"
                        >
                            <PostItem
                                :post="previousPreview"
                                deck-preview
                            />
                        </div>

                        <div
                            v-else
                            key="empty-previous"
                            class="h-[min(46vh,430px)] w-full"
                            aria-hidden="true"
                        />
                    </Transition>
                </div>

                <div
                    class="min-w-0"
                >
                    <Transition
                        :name="deckTransitionName"
                        mode="out-in"
                    >
                        <div
                            v-if="activePost"
                            :key="activePost.id"
                            class="mx-auto max-h-[calc(100vh-185px)] w-full overflow-y-auto rounded-2xl scrollbar-hidden"
                        >
                            <PostItem
                                :post="activePost"
                                deck-navigation
                                :can-go-previous="
                                    canGoPrevious
                                "
                                :can-go-next="
                                    canGoNext
                                "
                                :comments-open="
                                    commentsOpen
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
                                @comments="
                                    toggleComments
                                "
                            />
                        </div>
                    </Transition>
                </div>

                <div
                    class="min-w-0"
                >
                    <Transition
                        name="comments-panel"
                        mode="out-in"
                    >
                        <PostCommentsPanel
                            v-if="
                                commentsOpen &&
                                activePost
                            "
                            :key="`comments-${activePost.id}`"
                            :post="activePost"
                            @close="closeComments"
                        />

                        <div
                            v-else
                            key="next-preview"
                            class="hidden lg:block"
                        >
                            <Transition
                                name="preview-fade"
                                mode="out-in"
                            >
                                <div
                                    v-if="nextPreview"
                                    :key="nextPreview.id"
                                    class="pointer-events-none opacity-55"
                                >
                                    <PostItem
                                        :post="nextPreview"
                                        deck-preview
                                    />
                                </div>

                                <div
                                    v-else
                                    key="empty-next"
                                    class="h-[min(46vh,430px)] w-full"
                                    aria-hidden="true"
                                />
                            </Transition>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>

        <div
            v-else
            class="m-auto rounded-2xl border border-dashed border-[var(--poet-border)] bg-[var(--poet-surface)] px-6 py-16 text-center text-sm text-[var(--poet-muted)]"
        >
            Nothing in your feed yet.
        </div>

        <div
            v-if="feedState.posts.length"
            class="shrink-0 pb-1 pt-2 text-center text-xs text-[var(--poet-muted)]"
        >
            <span>
                {{ activeIndex + 1 }}
                /
                {{ feedState.posts.length }}
            </span>

            <span
                class="ml-3 hidden sm:inline"
            >
                swipe · wheel · arrow keys
            </span>

            <span
                v-if="loadingMore"
                class="ml-3"
            >
                loading more…
            </span>
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

<style scoped>
.deck-next-enter-active,
.deck-next-leave-active,
.deck-previous-enter-active,
.deck-previous-leave-active,
.preview-fade-enter-active,
.preview-fade-leave-active {
    transition:
        opacity 280ms ease,
        transform 320ms ease;
}

.deck-next-enter-from {
    opacity: 0;
    transform:
        translateX(70px)
        scale(0.975);
}

.deck-next-leave-to {
    opacity: 0;
    transform:
        translateX(-70px)
        scale(0.975);
}

.deck-previous-enter-from {
    opacity: 0;
    transform:
        translateX(-70px)
        scale(0.975);
}

.deck-previous-leave-to {
    opacity: 0;
    transform:
        translateX(70px)
        scale(0.975);
}

.preview-fade-enter-from,
.preview-fade-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

.comments-panel-enter-active,
.comments-panel-leave-active {
    transition:
        opacity 220ms ease,
        transform 260ms ease;
}

.comments-panel-enter-from,
.comments-panel-leave-to {
    opacity: 0;
    transform: translateX(24px) scale(0.985);
}
</style>
