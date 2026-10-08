<script setup>
import { getPoemFormName } from '@/data/poemForms.js';
import CommentList
    from '@/Components/app/CommentList.vue';

import PostAttachments
    from '@/Components/app/PostAttachments.vue';

import { 
    Disclosure,
    DisclosureButton, 
    DisclosurePanel,
        } from '@headlessui/vue'
        
import {
    HandThumbUpIcon
} from '@heroicons/vue/20/solid';

import {
    ArrowUturnLeftIcon,
    ChatBubbleOvalLeftIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    EyeIcon,
    MapPinIcon
} from '@heroicons/vue/24/outline';

import EditDeleteDropdown
    from '@/Components/app/EditDeleteDropdown.vue';

import {
    computed,
    ref
} from 'vue';

import {
    Link,
    router,
    usePage
} from '@inertiajs/vue3';

import PostUserHeader from '@/Components/app/PostUserHeader.vue';
import axios from 'axios';

const props = defineProps({
    post: Object,

    showHashtags: {
        type: Boolean,
        default: false
    },

    dedicatedPage: {
        type: Boolean,
        default: false
    },

    deckNavigation: {
        type: Boolean,
        default: false
    },

    deckPreview: {
        type: Boolean,
        default: false
    },

    canGoPrevious: {
        type: Boolean,
        default: false
    },

    canGoNext: {
        type: Boolean,
        default: false
    }
});

const plainBody = computed(() => {

    const body = props.post.body ?? '';

    return body
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
});

const postBody = computed(() => {

    const body =
        props.post.body ?? '';

    return body.replace(
        /(^|>)([^<]*)(?=<|$)/g,
        (fullMatch, prefix, text) => {

            const linkedText =
                text.replace(
                    /(^|\s)(#[\p{L}\p{N}_]+)/gu,
                    (
                        match,
                        spacing,
                        hashtag
                    ) => {

                        const url =
                            `/search/${encodeURIComponent(
                                hashtag
                            )}`;

                        return `${spacing}<a href="${url}" class="hashtag">${hashtag}</a>`;
                    }
                );

            return prefix + linkedText;
        }
    );
});

const emit = defineEmits([
    'editClick',
    'attachmentClick',
    'deleted',
    'pinChanged',
    'previous',
    'next'
]);

const page = usePage();

const authUser = computed(
    () => page.props.auth.user
);

const pageGroup = computed(
    () => page.props.group ?? null
);

const profileUser = computed(
    () => page.props.user ?? null
);

const pinScope = computed(() => {

    if (
        pageGroup.value?.id &&
        props.post.group?.id ===
            pageGroup.value.id &&
        pageGroup.value.role === 'admin'
    ) {
        return 'group';
    }

    if (
        profileUser.value?.id &&
        authUser.value?.id ===
            profileUser.value.id &&
        props.post.user?.id ===
            authUser.value.id
    ) {
        return 'profile';
    }

    return null;
});

const isPinned = computed(() => {

    if (pinScope.value === 'group') {
        return (
            pageGroup.value
                ?.pinned_post_id ===
            props.post.id
        );
    }

    if (pinScope.value === 'profile') {
        return (
            profileUser.value
                ?.pinned_post_id ===
            props.post.id
        );
    }

    return false;
});

const pinPending = ref(false);

function openAttachment(index) {
    emit(
        'attachmentClick',
        props.post,
        index
    );
}

function openEditModal() {
    if (props.post.type === 'poem') {
        router.visit(
            route(
                'poem.write',
                { edit: props.post.id }
            )
        );

        return;
    }

    emit('editClick', props.post);
}

function pinUnpinPost() {

    if (
        !pinScope.value ||
        pinPending.value
    ) {
        return;
    }

    const wasPinned =
        isPinned.value;

    pinPending.value = true;

    router.post(
        route(
            'post.pin',
            props.post.id
        ),
        {
            scope: pinScope.value
        },
        {
            preserveScroll: true,

            onSuccess: () => {

                if (
                    pinScope.value ===
                    'group'
                ) {
                    page.props.group
                        .pinned_post_id =
                        wasPinned
                            ? null
                            : props.post.id;
                }

                if (
                    pinScope.value ===
                    'profile'
                ) {
                    page.props.user
                        .pinned_post_id =
                        wasPinned
                            ? null
                            : props.post.id;
                }

                emit(
                    'pinChanged',
                    {
                        postId:
                            props.post.id,

                        pinned:
                            !wasPinned
                    }
                );
            },

            onFinish: () => {
                pinPending.value = false;
            }
        }
    );
}


function deletePost() {

    if (
        !window.confirm(
            'Are you sure you want to delete this post?'
        )
    ) {
        return;
    }

    router.delete(
        route(
            'post.destroy',
            props.post.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                emit(
                    'deleted',
                    props.post.id
                );
            }
        }
    );
}

function returnFromPost() {
    if (
        window.history.length > 1
    ) {
        window.history.back();
        return;
    }

    router.visit(
        route('dashboard')
    );
}

const reactionPending = ref(false);
const showAllGenres = ref(false);

const visibleGenres = computed(() => {
    if (
        showAllGenres.value ||
        (props.post.poem_genres?.length ?? 0) <= 1
    ) {
        return props.post.poem_genres ?? [];
    }

    return [
        props.post.poem_genres[0]
    ];
});

const hiddenGenreCount = computed(() =>
    Math.max(
        0,
        (props.post.poem_genres?.length ?? 0) -
            visibleGenres.value.length
    )
);

async function sendReaction() {

    if (reactionPending.value) {
        return;
    }

    reactionPending.value = true;

    try {

        const { data } = await axios.post(
            route(
                'post.reaction',
                props.post.id
            ),
            {
                reaction: 'like'
            }
        );

        props.post.current_user_has_reaction =
            data.current_user_has_reaction;

        props.post.num_of_reactions =
            data.num_of_reactions;

    } catch (error) {

        console.error(
            'Failed to update reaction:',
            error
        );

    } finally {

        reactionPending.value = false;
    }
}
</script>

<template>
    <article
        :class="[
            'rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] text-[var(--poet-text)] shadow-[0_10px_35px_rgba(15,23,42,0.04)] transition hover:border-[var(--poet-border-strong)]',
            deckPreview
                ? 'h-[min(46vh,430px)] overflow-hidden px-4 py-4'
                : deckNavigation
                    ? 'mb-5 flex min-h-[clamp(380px,48vh,560px)] flex-col px-5 py-5'
                    : 'mb-5 px-5 py-5'
        ]"
    >
        <div class="flex items-center justify-between mb-3">

            <PostUserHeader :post="post" />

            <div
                v-if="!deckPreview"
                class="flex items-center gap-2"
            >
                <div
                    v-if="isPinned"
                    class="flex items-center gap-1 text-xs text-gray-500"
                >
                    <MapPinIcon
                        class="h-4 w-4"
                    />

                    Pinned
                </div>

                <EditDeleteDropdown
                    :user="post.user"
                    :post="post"
                    :pin-allowed="
                        Boolean(pinScope) &&
                        !pinPending
                    "
                    :pinned="isPinned"
                    @pin="pinUnpinPost"
                    @edit="openEditModal"
                    @delete="deletePost"
                />
            </div>

        </div>
        <div
            :class="[
                'mb-3',
                deckPreview
                    ? 'max-h-[300px] overflow-hidden'
                    : ''
            ]"
        >

            <div
                v-if="post.type === 'poem'"
                class="mb-4"
            >
                <div class="mb-2 flex flex-wrap items-center gap-1.5">
                    <span
                        class="rounded-full bg-[var(--poet-accent-soft)] px-2 py-1 text-xs font-medium text-[var(--poet-accent)] dark:bg-indigo-950/50 dark:text-[var(--poet-accent)]"
                    >
                        Poem
                    </span>

                    <span
                        v-for="genre in visibleGenres"
                        :key="genre"
                        class="rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                    >
                        {{ genre }}
                    </span>

                    <button
                        v-if="
                            hiddenGenreCount ||
                            (
                                showAllGenres &&
                                (post.poem_genres?.length ?? 0) > 1
                            )
                        "
                        type="button"
                        @click="showAllGenres = !showAllGenres"
                        class="rounded-full border border-gray-200 bg-gray-50 px-2 py-1 text-xs font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-gray-100"
                        :aria-label="
                            showAllGenres
                                ? 'Hide extra genres'
                                : 'Show extra genres'
                        "
                    >
                        {{
                            showAllGenres
                                ? '−'
                                : `+${hiddenGenreCount}`
                        }}
                    </button>
                </div>

                <h2
                    v-if="post.title"
                    class="font-serif text-2xl font-semibold leading-tight text-gray-900 dark:text-gray-100"
                >
                    {{ post.title }}
                </h2>

                <div
                    v-if="post.poem_form"
                    class="mt-1 font-serif text-sm italic text-gray-500 dark:text-gray-400"
                >
                    {{ getPoemFormName(post.poem_form) }}
                </div>
            </div>

            <Disclosure
                v-if="plainBody.length > 200"
                v-slot="{ open }"
            >

                <div v-if="!open">
                    {{ plainBody.substring(0, 200) }}...
                </div>

                <DisclosurePanel>
                    <div
                        class="rich-text-output"
                        :class="{ 'poem-output': post.type === 'poem' }"
                        v-html="postBody"
                    />
                </DisclosurePanel>

                <div class="flex justify-end">

                    <DisclosureButton
                        class="text-blue-500 hover:text-blue-700 hover:underline"
                    >
                        {{ open ? 'Display less' : 'Display more' }}
                    </DisclosureButton>

                </div>

            </Disclosure>


            <div
                v-else
                class="rich-text-output"
                :class="{ 'poem-output': post.type === 'poem' }"
                v-html="postBody"
            />

            <p
                v-if="post.caption"
                class="mt-4 border-l-2 border-gray-200 pl-3 text-sm italic text-gray-500 dark:border-gray-600 dark:text-gray-400"
            >
                {{ post.caption }}
            </p>

            <div
                v-if="showHashtags && post.hashtags?.length"
                class="mt-3 flex flex-wrap gap-2"
            >
                <a
                    v-for="tag in post.hashtags"
                    :key="tag"
                    :href="`/search/${encodeURIComponent('#' + tag)}`"
                    class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-[var(--poet-accent)] hover:bg-[var(--poet-accent-soft)] dark:bg-gray-700 dark:text-[var(--poet-accent)] dark:hover:bg-gray-600"
                >
                    #{{ tag }}
                </a>
            </div>

        </div>
        <div
            v-if="post.attachments?.length"
            class="grid gap-3 mb-3"
            :class="
                post.attachments.length === 1
                    ? 'grid-cols-1'
                    : 'grid-cols-2'
            "
        >

            <PostAttachments
                :attachments="post.attachments"
                @attachmentClick="openAttachment"
            />

        </div>
        <Disclosure
            v-if="!deckPreview"
            v-slot="{ open }"
        >

    <div
        :class="[
            'grid items-end',
            deckNavigation
                ? 'mt-auto grid-cols-[40px_1fr_40px] gap-2 pt-5'
                : 'mt-5 grid-cols-1'
        ]"
    >
        <button
            v-if="deckNavigation"
            type="button"
            @click="emit('previous')"
            :disabled="!canGoPrevious"
            class="group flex h-10 w-10 items-center justify-center self-end justify-self-start rounded-full border border-gray-200 bg-white text-gray-600 transition hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            aria-label="Previous post"
        >
            <ChevronLeftIcon
                class="h-5 w-5 transition-transform group-hover:-translate-x-0.5"
            />
        </button>

        <div class="flex items-end justify-center gap-8">
            <button
                type="button"
                @click="sendReaction"
                :disabled="reactionPending"
                class="group flex flex-col items-center gap-1 text-xs text-gray-500 transition dark:text-gray-400"
                :class="
                    reactionPending
                        ? 'cursor-wait opacity-60'
                        : ''
                "
                aria-label="Like post"
            >
                <span
                    :class="[
                        'flex h-11 w-11 items-center justify-center rounded-full border transition',
                        post.current_user_has_reaction
                            ? 'border-sky-300 bg-sky-100 text-sky-700 dark:border-sky-800 dark:bg-sky-900/60 dark:text-sky-300'
                            : 'border-gray-200 bg-white text-gray-700 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100'
                    ]"
                >
                    <HandThumbUpIcon
                        class="h-5 w-5"
                    />
                </span>

                <span class="font-medium">
                    {{ post.num_of_reactions ?? 0 }}
                </span>
            </button>

            <button
                v-if="dedicatedPage"
                type="button"
                @click="returnFromPost"
                class="group flex flex-col items-center gap-1 text-xs font-medium text-[var(--poet-accent)]"
                aria-label="Return"
            >
                <span
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-[var(--poet-accent)] text-white shadow-md transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-lg"
                >
                    <ArrowUturnLeftIcon
                        class="h-6 w-6 transition-transform duration-300 group-hover:-rotate-12"
                    />
                </span>

                <span>
                    Return
                </span>
            </button>

            <Link
                v-else
                :href="route('post.view', post.id)"
                class="group flex flex-col items-center gap-1 text-xs font-medium text-[var(--poet-accent)]"
                aria-label="View post"
            >
                <span
                    class="flex h-14 w-14 items-center justify-center rounded-full bg-[var(--poet-accent)] text-white shadow-md transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-lg"
                >
                    <EyeIcon
                        class="h-6 w-6 transition-transform duration-300 group-hover:scale-110"
                    />
                </span>

                <span>
                    View
                </span>
            </Link>

            <DisclosureButton
                class="group flex flex-col items-center gap-1 text-xs text-gray-500 transition dark:text-gray-400"
                aria-label="Show comments"
            >
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 transition group-hover:-translate-y-0.5 group-hover:shadow-md dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                >
                    <ChatBubbleOvalLeftIcon
                        class="h-5 w-5"
                    />
                </span>

                <span class="font-medium">
                    {{ post.num_of_comments ?? 0 }}
                </span>
            </DisclosureButton>
        </div>

        <button
            v-if="deckNavigation"
            type="button"
            @click="emit('next')"
            :disabled="!canGoNext"
            class="group flex h-10 w-10 items-center justify-center self-end justify-self-end rounded-full border border-gray-200 bg-white text-gray-600 transition hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
            aria-label="Next post"
        >
            <ChevronRightIcon
                class="h-5 w-5 transition-transform group-hover:translate-x-0.5"
            />
        </button>
    </div>


    <!-- Everything below appears when Comment is clicked -->
    <DisclosurePanel class="comment-list mt-4 max-h-[400px] overflow-y-auto pr-2">

        <CommentList
            :post="post"
            :comments="post.comments ?? []"
        />

    </DisclosurePanel>

</Disclosure>
    </article>
</template>

<style scoped>
</style>