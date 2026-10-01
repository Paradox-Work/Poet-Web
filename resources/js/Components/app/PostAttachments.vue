<script setup>
import {
    ArrowDownTrayIcon,
    PaperClipIcon
} from '@heroicons/vue/24/solid';

import { isImage } from '@/helpers.js';

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
        v-for="(attachment, index) in attachments"
        :key="attachment.id"
    >

        <div
            @click="$emit(
                'attachmentClick',
                index
            )"
            class="group bg-blue-100 flex flex-col items-center justify-center text-gray-500 rounded h-48 relative cursor-pointer"
        >

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