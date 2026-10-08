<script setup>
import {
    ChatBubbleLeftEllipsisIcon,
    PaperAirplaneIcon
} from '@heroicons/vue/24/outline';

import {
    HandThumbUpIcon
} from '@heroicons/vue/20/solid';

import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel
} from '@headlessui/vue';

import {
    ref,
} from 'vue';

import {
    usePage
} from '@inertiajs/vue3';

import axios from 'axios';

import EditDeleteDropdown
    from '@/Components/app/EditDeleteDropdown.vue';


const props = defineProps({

    post: {
        type: Object,
        required: true
    },

    comments: {
        type: Array,
        default: () => []
    },

    parentComment: {
        type: Object,
        default: null
    },

    panelMode: {
        type: Boolean,
        default: false
    }

});

const emit = defineEmits([
    'commentCreate',
    'commentDelete'
]);

const authUser =
    usePage().props.auth.user;


const newCommentText =
    ref('');

const commentPending =
    ref(false);

const editingComment =
    ref(null);

const commentUpdatePending =
    ref(false);

const deletingCommentId =
    ref(null);

const reactingCommentId =
    ref(null);


async function createComment() {

    const comment =
        newCommentText.value.trim();

    if (
        !comment ||
        commentPending.value
    ) {
        return;
    }


    commentPending.value = true;


    try {

        const { data } =
            await axios.post(
                route(
                    'post.comment.create',
                    props.post.id
                ),
                {
                    comment,

                    parent_id:
                        props.parentComment?.id
                        ?? null
                }
            );


        props.comments.unshift(data);


        if (props.parentComment) {

            props.parentComment.num_of_comments =
                (
                    props.parentComment
                        .num_of_comments ?? 0
                ) + 1;
        }


        props.post.num_of_comments =
            (
                props.post.num_of_comments
                ?? 0
            ) + 1;


        newCommentText.value = '';

        emit(
            'commentCreate',
            data
        );

    } catch (error) {

        console.error(
            'Failed to create comment:',
            error
        );

    } finally {

        commentPending.value = false;
    }
}


function startCommentEdit(comment) {

    editingComment.value = {
        id: comment.id,
        comment: comment.comment
    };
}


async function updateComment() {

    if (
        !editingComment.value ||
        commentUpdatePending.value
    ) {
        return;
    }


    const comment =
        editingComment.value.comment.trim();


    if (!comment) {
        return;
    }


    commentUpdatePending.value = true;


    try {

        const { data } =
            await axios.put(
                route(
                    'post.comment.update',
                    editingComment.value.id
                ),
                {
                    comment
                }
            );


        const index =
            props.comments.findIndex(
                currentComment =>
                    currentComment.id ===
                    data.id
            );


        if (index !== -1) {

            const previous =
                props.comments[index];

            props.comments.splice(
                index,
                1,
                {
                    ...previous,
                    ...data,

                    num_of_comments:
                        previous.num_of_comments
                        ?? 0,


                    comments:
                        previous.comments
                        ?? []
                }
            );
        }


        editingComment.value = null;

    } catch (error) {

        console.error(
            'Failed to update comment:',
            error
        );

    } finally {

        commentUpdatePending.value = false;
    }
    
}


    
async function deleteComment(comment) {

    if (
        !window.confirm(
            'Are you sure you want to delete this comment?'
        )
    ) {
        return;
    }


    if (
        deletingCommentId.value ===
        comment.id
    ) {
        return;
    }

    const removedCount =
        1 +
        (
            comment.num_of_comments
            ?? 0
        );

    deletingCommentId.value =
        comment.id;


    try {

        const { data } =
            await axios.delete(
                route(
                    'post.comment.delete',
                    comment.id
                )
            );


        const index =
            props.comments.findIndex(
                currentComment =>
                    currentComment.id ===
                    comment.id
            );


        if (index !== -1) {

            props.comments.splice(
                index,
                1
            );
        }


        if (props.parentComment) {

            props.parentComment.num_of_comments =
                Math.max(
                    0,

                    (
                        props.parentComment
                            .num_of_comments ?? 0
                    ) - removedCount
                );
        }


        props.post.num_of_comments =
            data.num_of_comments;

        emit(
            'commentDelete',
            removedCount
        );

    } catch (error) {

        console.error(
            'Failed to delete comment:',
            error
        );

    } finally {

        deletingCommentId.value =
            null;
    }
}


async function sendCommentReaction(
    comment
) {

    if (
        reactingCommentId.value ===
        comment.id
    ) {
        return;
    }


    reactingCommentId.value =
        comment.id;


    try {

        const { data } =
            await axios.post(
                route(
                    'post.comment.reaction',
                    comment.id
                ),
                {
                    reaction: 'like'
                }
            );


        comment.current_user_has_reaction =
            data.current_user_has_reaction;

        comment.num_of_reactions =
            data.num_of_reactions;

    } catch (error) {

        console.error(
            'Failed to update comment reaction:',
            error
        );

    } finally {

        reactingCommentId.value =
            null;
    }
}

function onCommentCreate(comment) {

    if (props.parentComment) {

        props.parentComment.num_of_comments =
            (
                props.parentComment
                    .num_of_comments ?? 0
            ) + 1;
    }


    emit(
        'commentCreate',
        comment
    );
}


function onCommentDelete(
    removedCount
) {

    if (props.parentComment) {

        props.parentComment.num_of_comments =
            Math.max(
                0,

                (
                    props.parentComment
                        .num_of_comments ?? 0
                ) - removedCount
            );
    }


    emit(
        'commentDelete',
        removedCount
    );
}
</script>


<template>
<div
    :class="[
        'min-w-0',
        panelMode || parentComment
            ? 'flex min-h-0 flex-col'
            : ''
    ]"
>
    <!-- New comment / reply -->
    <div
        :class="[
            'flex min-w-0 gap-2',
            parentComment
                ? 'order-2 mt-3'
                : panelMode
                    ? 'order-2 mt-3 border-t border-[var(--poet-border)] pt-3'
                    : 'mb-4'
        ]"
    >
        <img
            v-if="authUser.avatar_url"
            :src="authUser.avatar_url"
            :class="[
                'shrink-0 rounded-full object-cover',
                parentComment
                    ? 'h-8 w-8'
                    : 'h-10 w-10'
            ]"
            alt="Your avatar"
        />

        <div class="flex min-w-0 flex-1 items-end gap-2">
            <textarea
                v-model="newCommentText"
                :placeholder="
                    parentComment
                        ? 'Write a reply...'
                        : 'Write a comment...'
                "
                :rows="
                    parentComment
                        ? 1
                        : 2
                "
                maxlength="2000"
                :class="[
                    'min-w-0 flex-1 resize-none rounded-md border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)] dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500',
                    parentComment
                        ? 'min-h-9 py-2 text-sm'
                        : ''
                ]"
            />

            <button
                type="button"
                @click="createComment"
                :disabled="
                    commentPending ||
                    !newCommentText.trim()
                "
                :class="[
                    'flex shrink-0 items-center justify-center rounded-full bg-[var(--poet-accent)] text-white transition hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-40',
                    parentComment
                        ? 'h-9 w-9'
                        : 'h-10 w-10'
                ]"
                :aria-label="
                    commentPending
                        ? 'Posting comment'
                        : 'Post comment'
                "
            >
                <PaperAirplaneIcon
                    :class="
                        parentComment
                            ? 'h-4 w-4'
                            : 'h-5 w-5'
                    "
                />
            </button>
        </div>
    </div>

    <!-- Comments -->
    <div
        v-if="comments.length"
        :class="[
            'min-w-0 space-y-4',
            panelMode
                ? 'comment-list order-1 min-h-0 flex-1 overflow-x-hidden overflow-y-auto pr-1'
                : parentComment
                    ? 'order-1'
                    : ''
        ]"
    >
        <Disclosure
            v-for="comment in comments"
            :key="comment.id"
            as="div"
            class="min-w-0"
        >
            <div class="flex min-w-0 gap-2">
                <img
                    v-if="comment.user.avatar_url"
                    :src="comment.user.avatar_url"
                    :class="[
                        'shrink-0 rounded-full object-cover',
                        parentComment
                            ? 'h-8 w-8'
                            : 'h-10 w-10'
                    ]"
                    alt="User avatar"
                />

                <div class="min-w-0 flex-1">
                    <div class="flex min-w-0 items-start justify-between gap-2">
                        <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-0.5">
                            <strong class="break-words">
                                {{ comment.user.name }}
                            </strong>

                            <small class="text-gray-400">
                                {{ comment.updated_at }}
                            </small>
                        </div>

                        <EditDeleteDropdown
                            class="shrink-0"
                            :user="comment.user"
                            :post="post"
                            :comment="comment"
                            @edit="
                                startCommentEdit(comment)
                            "
                            @delete="
                                deleteComment(comment)
                            "
                        />
                    </div>

                    <div
                        v-if="parentComment"
                        class="mt-0.5 text-[11px] text-[var(--poet-muted)]"
                    >
                        Replying to
                        <span class="font-medium text-[var(--poet-accent)]">
                            @{{ parentComment.user?.username || parentComment.user?.name }}
                        </span>
                    </div>

                    <div
                        v-if="
                            editingComment &&
                            editingComment.id ===
                                comment.id
                        "
                        class="mt-2 min-w-0"
                    >
                        <textarea
                            v-model="
                                editingComment.comment
                            "
                            rows="2"
                            maxlength="2000"
                            class="w-full min-w-0 resize-none rounded-md border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)] dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                        />

                        <div class="mt-2 flex flex-wrap justify-end gap-3">
                            <button
                                type="button"
                                @click="
                                    editingComment = null
                                "
                                class="text-gray-600 hover:underline dark:text-gray-300"
                            >
                                Cancel
                            </button>

                            <button
                                type="button"
                                @click="updateComment"
                                :disabled="
                                    commentUpdatePending ||
                                    !editingComment
                                        .comment
                                        .trim()
                                "
                                class="rounded-md bg-[var(--poet-accent)] px-3 py-1.5 text-sm text-white disabled:opacity-50"
                            >
                                {{
                                    commentUpdatePending
                                        ? 'Updating...'
                                        : 'Update'
                                }}
                            </button>
                        </div>
                    </div>

                    <p
                        v-else
                        class="mt-1 whitespace-pre-wrap break-words text-sm"
                    >
                        {{ comment.comment }}
                    </p>

                    <div class="mt-1 flex flex-wrap gap-1.5">
                        <button
                            type="button"
                            @click="
                                sendCommentReaction(
                                    comment
                                )
                            "
                            :disabled="
                                reactingCommentId ===
                                comment.id
                            "
                            class="flex items-center gap-1 rounded px-2 py-1 text-xs transition"
                            :class="[
                                comment.current_user_has_reaction
                                    ? 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:hover:bg-indigo-950/60'
                                    : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700',

                                reactingCommentId ===
                                    comment.id
                                    ? 'cursor-wait opacity-50'
                                    : ''
                            ]"
                        >
                            <HandThumbUpIcon class="h-3 w-3" />

                            <span>
                                {{ comment.num_of_reactions ?? 0 }}
                            </span>

                            {{
                                comment.current_user_has_reaction
                                    ? 'Unlike'
                                    : 'Like'
                            }}
                        </button>

                        <DisclosureButton
                            class="flex items-center gap-1 rounded px-2 py-1 text-xs text-indigo-600 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-950/40"
                        >
                            <ChatBubbleLeftEllipsisIcon class="h-3 w-3" />

                            <span>
                                {{ comment.num_of_comments ?? 0 }}
                            </span>

                            Replies
                        </DisclosureButton>
                    </div>
                </div>
            </div>

            <DisclosurePanel
                class="mt-2 min-w-0"
            >
                <CommentList
                    :post="post"
                    :comments="
                        comment.comments ?? []
                    "
                    :parent-comment="
                        comment
                    "
                    @comment-create="
                        onCommentCreate
                    "
                    @comment-delete="
                        onCommentDelete
                    "
                />
            </DisclosurePanel>
        </Disclosure>
    </div>

    <div
        v-else-if="!parentComment"
        :class="[
            'text-sm text-gray-500',
            panelMode
                ? 'order-1 flex min-h-0 flex-1 items-center justify-center'
                : ''
        ]"
    >
        No comments yet.
    </div>
</div>
</template>
