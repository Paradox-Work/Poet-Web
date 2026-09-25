<script setup>
import { ref } from 'vue';

import PostItem from '@/Components/app/PostItem.vue';
import PostModal from '@/Components/app/PostModal.vue';
import AttachmentPreviewModal from '@/Components/app/AttachmentPreviewModal.vue';

defineProps({
    posts: Array
});

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

</script>

<template>

    <div class="overflow-auto flex-1">
        <PostItem 
            v-for="post of posts"
            :key="post.id"
            :post="post"
            @editClick="openEditModal"
            @attachmentClick="openAttachmentPreviewModal"
/>
        />
        <PostModal
            :post="editPost"
            v-model="showEditModal"
        />
        
        <AttachmentPreviewModal
            :attachments="
                previewAttachmentsPost.post?.attachments ?? []
            "
            v-model:index="previewAttachmentsPost.index"
            v-model="showAttachmentsModal"
        />
    </div>

</template>

<style scoped>

</style>