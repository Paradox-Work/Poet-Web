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
    ChatBubbleOvalLeftIcon,
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
    'pinChanged'
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

const reactionPending = ref(false);

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
    <div class="bg-white border border-gray-200 rounded p-4 mb-3 shadow dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
        <div class="flex items-center justify-between mb-3">

            <PostUserHeader :post="post" />

            <div
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
        <div class="mb-3">

            <div
                v-if="post.type === 'poem'"
                class="mb-4"
            >
                <div class="mb-2 flex flex-wrap items-center gap-1.5">
                    <span
                        class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-300"
                    >
                        Poem
                    </span>

                    <span
                        v-for="genre in post.poem_genres ?? []"
                        :key="genre"
                        class="rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-medium text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300"
                    >
                        {{ genre }}
                    </span>
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
                    class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 dark:bg-gray-700 dark:text-indigo-300 dark:hover:bg-gray-600"
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
        <Disclosure v-slot="{ open }">

    <div class="mt-4 flex items-end justify-center gap-8">
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

        <Link
            :href="route('post.view', post.id)"
            class="group flex flex-col items-center gap-1 text-xs font-medium text-indigo-600 dark:text-indigo-300"
            aria-label="View post"
        >
            <span
                class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-md transition group-hover:-translate-y-0.5 group-hover:bg-indigo-500 group-hover:shadow-lg"
            >
                <EyeIcon
                    class="h-6 w-6"
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


    <!-- Everything below appears when Comment is clicked -->
    <DisclosurePanel class="comment-list mt-4 max-h-[400px] overflow-y-auto pr-2">

        <CommentList
            :post="post"
            :comments="post.comments ?? []"
        />

    </DisclosurePanel>

</Disclosure>
    </div>
</template>

<style scoped>
</style>