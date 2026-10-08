<script setup>

import {
    ref
} from 'vue';

import {
    ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

import AttachmentPreviewModal
    from '@/Components/app/AttachmentPreviewModal.vue';


const props = defineProps({
    photos: {
        type: Array,
        default: () => []
    }
});


const currentPhotoIndex =
    ref(0);

const showModal =
    ref(false);


function openPhoto(index) {

    currentPhotoIndex.value =
        index;

    showModal.value =
        true;
}

</script>


<template>

    <div
        v-if="photos.length"
        class="grid grid-cols-2 gap-2 sm:grid-cols-3"
    >

        <div
            v-for="(attachment, index) in photos"
            :key="attachment.id"
            @click="openPhoto(index)"
            class="group relative aspect-square cursor-pointer overflow-hidden rounded-md bg-gray-100"
        >

            <img
                :src="attachment.url"
                :alt="attachment.name"
                class="h-full w-full object-cover"
            />


            <a
                @click.stop
                :href="
                    route(
                        'post.download',
                        attachment.id
                    )
                "
                class="
                    absolute
                    right-2
                    top-2
                    z-20
                    flex
                    h-8
                    w-8
                    items-center
                    justify-center
                    rounded
                    bg-gray-800/80
                    text-white
                    opacity-0
                    transition
                    group-hover:opacity-100
                "
            >
                <ArrowDownTrayIcon
                    class="h-4 w-4"
                />
            </a>

        </div>

    </div>


    <div
        v-else
        class="py-8 text-center text-gray-500 dark:text-gray-300"
    >
        No photos yet.
    </div>


    <AttachmentPreviewModal
        :attachments="photos"
        v-model:index="
            currentPhotoIndex
        "
        v-model="
            showModal
        "
    />

</template>