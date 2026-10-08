<script setup>

import {
    ref
} from 'vue';

import {
    Head,
    usePage
} from '@inertiajs/vue3';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import PostItem
    from '@/Components/app/PostItem.vue';

import PostModal
    from '@/Components/app/PostModal.vue';

import AttachmentPreviewModal
    from '@/Components/app/AttachmentPreviewModal.vue';


const props = defineProps({
    post: {
        type: Object,
        required: true
    }
});


const authUser =
    usePage().props.auth.user;


const showEditModal =
    ref(false);

const showAttachmentsModal =
    ref(false);

const editPost =
    ref({});

const previewAttachmentsPost =
    ref({
        post: null,
        index: 0
    });


function openEditModal(post) {

    editPost.value =
        post;

    showEditModal.value =
        true;
}


function openAttachmentPreviewModal(
    post,
    index
) {

    previewAttachmentsPost.value = {
        post,
        index
    };

    showAttachmentsModal.value =
        true;
}


function onModalHide() {

    editPost.value = {
        id: null,
        body: '',
        user: authUser
    };
}

</script>


<template>

    <Head title="Post" />

    <AuthenticatedLayout>

        <div
            class="mx-auto max-w-2xl p-4"
        >

            <PostItem
                :post="post"
                show-hashtags
                dedicated-page
                @edit-click="
                    openEditModal
                "
                @attachment-click="
                    openAttachmentPreviewModal
                "
            />

        </div>


        <PostModal
            v-model="showEditModal"
            :post="editPost"
            @hide="
                onModalHide
            "
        />


        <AttachmentPreviewModal
            v-model="
                showAttachmentsModal
            "
            v-model:index="
                previewAttachmentsPost.index
            "
            :attachments="
                previewAttachmentsPost
                    .post
                    ?.attachments
                    ?? []
            "
        />

    </AuthenticatedLayout>

</template>