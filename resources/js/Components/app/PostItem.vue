<script setup>
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