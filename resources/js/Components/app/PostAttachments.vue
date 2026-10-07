<script setup>
import {
    ArrowDownTrayIcon,
    PaperClipIcon
} from '@heroicons/vue/24/solid';

import {
    isImage,
    isVideo
} from '@/helpers.js';

defineProps({
    attachments: {
        type: Array,
        default: () => []
    }
});

defineEmits([
    'attachmentClick'
]);
</script>

<template>

    <template
        v-for="(attachment, index) in attachments.slice(0, 4)"
        :key="attachment.id"
    >

        <div
            @click="$emit(
                'attachmentClick',
                index
            )"
            class="group bg-blue-100 flex flex-col items-center justify-center text-gray-500 rounded h-48 relative cursor-pointer"
        >

            <div
                v-if="
                    index === 3 &&
                    attachments.length > 4
                "
                class="absolute inset-0 z-10 bg-black/60 text-white flex items-center justify-center text-2xl rounded"
            >
                +{{ attachments.length - 4 }} more
            </div>

            <!-- Download -->
            <a
                :href="
                    route(
                        'post.download',
                        attachment.id
                    )
                "
                @click.stop
                class="z-20 opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center text-gray-100 bg-gray-600 rounded absolute right-2 top-2 hover:bg-gray-800"
            >

                <ArrowDownTrayIcon
                    class="w-5 h-5"
                />

            </a>
            <!-- /Download -->


            <img
                v-if="isImage(attachment)"
                :src="attachment.url"
                alt="Attachment"
                class="w-full h-48 object-cover rounded"
            />


            <div
                v-else-if="isVideo(attachment)"
                class="relative flex h-full w-full items-center justify-center overflow-hidden rounded bg-black"
            >

                <video
                    :src="attachment.url"
                    preload="metadata"
                    muted
                    playsinline
                    class="h-full w-full object-cover"
                />

                <div
                    class="absolute inset-0 bg-black/30"
                />

                <div
                    class="
                        absolute
                        z-10
                        flex
                        h-14
                        w-14
                        items-center
                        justify-center
                        rounded-full
                        bg-black/60
                        text-white
                    "
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="h-8 w-8"
                    >
                        <path
                            d="M8.25 5.25v13.5L18.75 12 8.25 5.25Z"
                        />
                    </svg>
                </div>

            </div>


            <template v-else>

                <PaperClipIcon
                    class="w-2/5 h-2/5 text-gray-500"
                />

                <small>
                    {{ attachment.name }}
                </small>

            </template>

        </div>

    </template>

</template>