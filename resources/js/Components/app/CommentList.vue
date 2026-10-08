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
    :class="
        panelMode
            ? 'flex h-full min-h-0 flex-col'
            : ''
    "
>

    <!-- New comment / reply -->
    <div
        :class="[
            'flex gap-2',
            panelMode
                ? 'order-2 mt-3 border-t border-[var(--poet-border)] pt-3'
                : 'mb-4'
        ]"
    >

        <img
            v-if="authUser.avatar_url"
            :src="authUser.avatar_url"
            class="w-10 h-10 rounded-full object-cover"
            alt="Your avatar"
        />


        <div class="flex flex-1 gap-2">

            <textarea
                v-model="newCommentText"
                :placeholder="
                    parentComment
                        ? 'Write a reply...'
                        : 'Write a comment...'
                "
                rows="2"
                maxlength="2000"
                class="flex-1 resize-none rounded-md border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
            />


            <button
                type="button"
                @click="createComment"
                :disabled="
                    commentPending ||
                    !newCommentText.trim()
                "
                class="flex h-10 w-10 shrink-0 items-center justify-center self-end rounded-full bg-[var(--poet-accent)] text-white transition hover:-translate-y-0.5 hover:shadow-md disabled:pointer-events-none disabled:opacity-40"
                :aria-label="
                    commentPending
                        ? 'Posting comment'
                        : 'Post comment'
                "
            >
                <PaperAirplaneIcon
                    class="h-5 w-5"
                />
            </button>

        </div>

    </div>


    <!-- Comments -->
    <div
        v-if="comments.length"
        :class="[
            'space-y-4',
            panelMode
                ? 'comment-list order-1 min-h-0 flex-1 overflow-y-auto pr-1'
                : ''
        ]"
    >

        <div
            v-for="comment in comments"
            :key="comment.id"
        >

            <div class="flex justify-between gap-2">

                <div class="flex gap-2 flex-1">

                    <img
                        v-if="comment.user.avatar_url"
                        :src="comment.user.avatar_url"
                        class="w-10 h-10 rounded-full object-cover"
                        alt="User avatar"
                    />


                    <div class="flex-1">

                        <div class="flex items-center gap-2">

                            <strong>
                                {{ comment.user.name }}
                            </strong>

                            <small class="text-gray-400">
                                {{ comment.updated_at }}
                            </small>

                        </div>

                    </div>

                </div>


                <EditDeleteDropdown
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


            <!-- Editing -->
            <div
                v-if="
                    editingComment &&
                    editingComment.id ===
                        comment.id
                "
                class="ml-12 mt-2"
            >

                <textarea
                    v-model="
                        editingComment.comment
                    "
                    rows="2"
                    maxlength="2000"
                    class="w-full resize-none rounded-md border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-gray-500"
                />


                <div
                    class="flex justify-end gap-3 mt-2"
                >

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
                        class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            commentUpdatePending
                                ? 'Updating...'
                                : 'Update'
                        }}
                    </button>

                </div>

            </div>


            <!-- Normal text -->
            <p
                v-else
                class="text-sm whitespace-pre-wrap ml-12"
            >
                {{ comment.comment }}
            </p>


            <div class="ml-12 mt-1">

                <Disclosure>

                    <div class="flex gap-2">

                        <!-- Reaction -->
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
                                    ? 'opacity-50 cursor-wait'
                                    : ''
                            ]"
                        >

                            <HandThumbUpIcon
                                class="w-3 h-3"
                            />

                            <span>
                                {{
                                    comment.num_of_reactions
                                    ?? 0
                                }}
                            </span>

                            {{
                                comment.current_user_has_reaction
                                    ? 'Unlike'
                                    : 'Like'
                            }}

                        </button>


                        <!-- Replies -->
                        <DisclosureButton
                            class="flex items-center gap-1 rounded px-2 py-1 text-xs text-indigo-600 hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-950/40"
                        >

                            <ChatBubbleLeftEllipsisIcon
                                class="w-3 h-3"
                            />

                            <span>
                                {{
                                    comment.num_of_comments
                                    ?? 0
                                }}
                            </span>

                            Replies

                        </DisclosureButton>

                    </div>


                    <DisclosurePanel
                        class="mt-3 ml-6"
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

        </div>

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