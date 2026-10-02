<script setup>
import {
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

});


const feedState =
    reactive({
        posts: [
            ...(props.posts.data ?? [])
        ],

        nextPageUrl:
            props.posts.links?.next
            ?? null,

        loadedBeyondFirstPage: false
    });

const loadingMore =
    ref(false);

const loadMoreIntersect =
    ref(null);

const postListContainer =
    ref(null);


let observer = null;


const showEditModal =
    ref(false);

const editPost =
    ref({});

const showAttachmentsModal =
    ref(false);

const previewAttachmentsPost =
    ref({
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
    () =>
        (props.posts?.data ?? [])
            .map(
                post =>
                    `${post.id}:${post.updated_at}`
            )
            .join('|'),

    () => {

        const posts =
            props.posts;

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


        /*
         * Refresh posts already present
         * in remembered state.
         */
        feedState.posts =
            feedState.posts.map(
                post =>
                    incomingById.get(
                        post.id
                    )
                    ?? post
            );


        /*
         * Add newly-created posts.
         */
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


onMounted(() => {

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


    if (loadMoreIntersect.value) {

        observer.observe(
            loadMoreIntersect.value
        );
    }
});


onBeforeUnmount(() => {

    observer?.disconnect();
});
</script>

<template>

    <div
        ref="postListContainer"
        class="scrollbar-hidden overflow-auto flex-1"
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
        />


        <div
            ref="loadMoreIntersect"
            class="h-px"
            aria-hidden="true"
        />


        <div
            v-if="loadingMore"
            class="text-center text-sm text-gray-400 py-3"
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

</style>
