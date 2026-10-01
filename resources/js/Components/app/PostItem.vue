<script setup>
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

import EditDeleteDropdown
    from '@/Components/app/EditDeleteDropdown.vue';

import {
    computed,
    ref
} from 'vue';

import {
    router,
    usePage
} from '@inertiajs/vue3';

import PostUserHeader from '@/Components/app/PostUserHeader.vue';
import axios from 'axios';

const props = defineProps({
    post: Object,
});

const authUser =
    usePage().props.auth.user;

const newCommentText = ref('');

const commentPending = ref(false);

const editingComment = ref(null);

const commentUpdatePending = ref(false);

const deletingCommentId = ref(null);

const reactingCommentId = ref(null);

const plainBody = computed(() => {

    const body = props.post.body ?? '';

    return body
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
});

const emit = defineEmits([
    'editClick',
    'attachmentClick'
]);

function openAttachment(index) {
    emit(
        'attachmentClick',
        props.post,
        index
    );
}

function openEditModal() {
    emit('editClick', props.post);
}

function deletePost() {
    if (window.confirm('Are you sure you want to delete this post?')) {
        router.delete(
            route('post.destroy', props.post.id),
            {
                preserveScroll: true
            }
        );
    }
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

async function createComment() {

        const comment =
            newCommentText.value.trim();

        if (!comment || commentPending.value) {
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
                        comment
                    }
                );

            props.post.comments.unshift(data);

            props.post.num_of_comments++;

            newCommentText.value = '';

        } catch (error) {

            console.error(
                'Failed to create comment:',
                error
            );

        } finally {

            commentPending.value = false;
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

        if (deletingCommentId.value === comment.id) {
            return;
        }

        deletingCommentId.value = comment.id;

        try {

            await axios.delete(
                route(
                    'post.comment.delete',
                    comment.id
                )
            );

            props.post.comments =
                props.post.comments.filter(
                    item => item.id !== comment.id
                );

            props.post.num_of_comments =
                Math.max(
                    0,
                    props.post.num_of_comments - 1
                );

        } catch (error) {

            console.error(
                'Failed to delete comment:',
                error
            );

        } finally {

            deletingCommentId.value = null;
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

            props.post.comments =
                props.post.comments.map(
                    currentComment =>
                        currentComment.id === data.id
                            ? data
                            : currentComment
                );

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

    async function sendCommentReaction(comment) {

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
    
</script>

<template>
    <div class="bg-white border rounded p-4 mb-3 shadow">
        <div class="flex items-center justify-between mb-3">

            <PostUserHeader :post="post" />

            <EditDeleteDropdown
                :user="post.user"
                @edit="openEditModal"
                @delete="deletePost"
            />

        </div>
        <div class="mb-3">

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
                        v-html="post.body"
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
                v-html="post.body"
            />

        </div>
        <div
            v-if="post.attachments?.length"
            class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-3"
        >

            <PostAttachments
                :attachments="post.attachments"
                @attachmentClick="openAttachment"
            />

        </div>
        <Disclosure v-slot="{ open }">

    <!-- Like + Comment buttons -->
    <div class="flex gap-2 mt-3">

        <!-- Like -->
        <button
            type="button"
            @click="sendReaction"
            :disabled="reactionPending"
            class="text-gray-800 flex gap-1 items-center justify-center rounded-lg py-2 px-4 flex-1 transition"
            :class="[
                post.current_user_has_reaction
                    ? 'bg-sky-100 hover:bg-sky-200'
                    : 'bg-gray-100 hover:bg-gray-200',

                reactionPending
                    ? 'opacity-60 cursor-wait'
                    : ''
            ]"
        >
            <HandThumbUpIcon
                class="w-5 h-5"
            />

            <span class="mr-1">
                {{ post.num_of_reactions ?? 0 }}
            </span>

            {{
                post.current_user_has_reaction
                    ? 'Unlike'
                    : 'Like'
            }}
        </button>


        <!-- Comment -->
        <DisclosureButton
            class="text-gray-800 flex gap-1 items-center justify-center bg-gray-100 rounded-lg hover:bg-gray-200 py-2 px-4 flex-1"
        >
            <span>
                {{ post.num_of_comments ?? 0 }}
            </span>

            Comment
        </DisclosureButton>

    </div>


    <!-- Everything below appears when Comment is clicked -->
    <DisclosurePanel class="mt-4">

            <!-- New comment -->
            <div class="flex gap-2 mb-4">

                <img
                    v-if="authUser.avatar_url"
                    :src="authUser.avatar_url"
                    class="w-10 h-10 rounded-full object-cover"
                    alt="Your avatar"
                />

                <div class="flex flex-1 gap-2">

                    <textarea
                        v-model="newCommentText"
                        placeholder="Write a comment..."
                        rows="2"
                        maxlength="2000"
                        class="flex-1 rounded-md border-gray-300 resize-none"
                    />

                    <button
                        type="button"
                        @click="createComment"
                        :disabled="
                            commentPending ||
                            !newCommentText.trim()
                        "
                        class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{
                            commentPending
                                ? 'Posting...'
                                : 'Submit'
                        }}
                    </button>

                </div>

            </div>


            <!-- Existing comments -->
    <div
        v-if="post.comments?.length"
        class="space-y-4"
    >

        <!-- PUT THE NEW BLOCK HERE -->
        <div
            v-for="comment in post.comments"
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
                    @edit="startCommentEdit(comment)"
                    @delete="deleteComment(comment)"
                />

            </div>


            <!-- Editing mode -->
            <div
                v-if="
                    editingComment &&
                    editingComment.id === comment.id
                "
                class="ml-12 mt-2"
            >
                <textarea
                    v-model="editingComment.comment"
                    rows="2"
                    maxlength="2000"
                    class="w-full rounded-md border-gray-300 resize-none"
                />

                <div class="flex justify-end gap-3 mt-2">

                    <button
                        type="button"
                        @click="editingComment = null"
                        class="text-gray-600 hover:underline"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="updateComment"
                        :disabled="
                            commentUpdatePending ||
                            !editingComment.comment.trim()
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


            <!-- Normal comment -->
            <p
                v-else
                class="text-sm whitespace-pre-wrap ml-12"
            >
                {{ comment.comment }}
            </p>

            <div class="ml-12 mt-1">

                <button
                    type="button"
                    @click="sendCommentReaction(comment)"
                    :disabled="
                        reactingCommentId === comment.id
                    "
                    class="flex items-center gap-1 rounded px-2 py-1 text-xs transition"
                    :class="[
                        comment.current_user_has_reaction
                            ? 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100'
                            : 'text-gray-500 hover:bg-gray-100',

                        reactingCommentId === comment.id
                            ? 'opacity-50 cursor-wait'
                            : ''
                    ]"
                >

                    <HandThumbUpIcon
                        class="w-3 h-3"
                    />

                    <span>
                        {{ comment.num_of_reactions ?? 0 }}
                    </span>

                    {{
                        comment.current_user_has_reaction
                            ? 'Unlike'
                            : 'Like'
                    }}

                </button>

            </div>

        </div>
        <!-- NEW BLOCK ENDS HERE -->

    </div>


    <div
        v-else
        class="text-sm text-gray-500"
    >
        No comments yet.
    </div>

    </DisclosurePanel>

</Disclosure>
    </div>
</template>

<style scoped>
</style>