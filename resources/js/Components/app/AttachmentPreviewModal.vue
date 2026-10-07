<script setup>
import { computed } from 'vue';

import {
    XMarkIcon,
    PaperClipIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/solid';

import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel
} from '@headlessui/vue';

import {
    isImage,
    isVideo
} from '@/helpers.js';


const props = defineProps({
    attachments: {
        type: Array,
        required: true
    },

    index: {
        type: Number,
        default: 0
    },

    modelValue: Boolean
});


const emit = defineEmits([
    'update:modelValue',
    'update:index'
]);


const show = computed({
    get: () => props.modelValue,

    set: (value) => {
        emit('update:modelValue', value);
    }
});


const currentIndex = computed({
    get: () => props.index,

    set: (value) => {
        emit('update:index', value);
    }
});


const attachment = computed(() => {
    return props.attachments[currentIndex.value] ?? null;
});


function closeModal() {
    show.value = false;
}


function prev() {
    if (currentIndex.value <= 0) {
        return;
    }

    currentIndex.value--;
}


function next() {
    if (
        currentIndex.value >=
        props.attachments.length - 1
    ) {
        return;
    }

    currentIndex.value++;
}
</script>


<template>
    <teleport
        v-if="show"
        to="body"
    >

        <TransitionRoot
            appear
            :show="show"
            as="template"
        >

            <Dialog
                as="div"
                @close="closeModal"
                class="relative z-50"
            >

                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >

                    <div class="fixed inset-0 bg-black/70" />

                </TransitionChild>


                <div class="fixed inset-0">

                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >

                        <DialogPanel
                            class="relative flex w-full h-screen items-center justify-center bg-slate-900/95 p-6"
                            @click.self="closeModal"
                        >

                            <div
                                v-if="attachment"
                                class="
                                    relative
                                    inline-flex
                                    max-h-full
                                    max-w-full
                                    items-center
                                    justify-center
                                    px-16
                                    py-14
                                "
                            >

                                <img
                                    v-if="isImage(attachment)"
                                    :src="attachment.url"
                                    :alt="attachment.name"
                                    class="block max-w-[calc(100vw-6rem)] max-h-[calc(100vh-6rem)] object-contain"
                                />


                                <video
                                    v-else-if="isVideo(attachment)"
                                    :src="attachment.url"
                                    controls
                                    autoplay
                                    playsinline
                                    class="block max-w-[calc(100vw-6rem)] max-h-[calc(100vh-6rem)] object-contain"
                                />


                                <div
                                    v-else
                                    class="flex flex-col items-center text-gray-100"
                                >
                                    <PaperClipIcon class="w-12 h-12 mb-3" />

                                    <span>
                                        {{ attachment.name }}
                                    </span>
                                </div>
                            
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="
                                        absolute
                                        right-2
                                        top-2
                                        z-30
                                        flex
                                        h-11
                                        w-11
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-black/75
                                        text-white
                                        shadow-lg
                                        ring-1
                                        ring-white/30
                                        backdrop-blur-sm
                                        transition
                                        hover:bg-black/90
                                    "
                                    aria-label="Close attachment preview"
                                >
                                    <XMarkIcon class="w-7 h-7" />
                                </button>
                                
                                <button
                                    v-if="currentIndex > 0"
                                    type="button"
                                    @click="prev"
                                    class="
                                        absolute
                                        left-2
                                        top-1/2
                                        z-20
                                        flex
                                        h-12
                                        w-12
                                        -translate-y-1/2
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-black/55
                                        text-white
                                        shadow-md
                                        ring-1
                                        ring-white/20
                                        backdrop-blur-sm
                                        transition
                                        hover:bg-black/75
                                    "
                                    aria-label="Previous attachment"
                                >
                                    <ChevronLeftIcon class="w-8 h-8" />
                                </button>


                                <button
                                    v-if="
                                        currentIndex <
                                        attachments.length - 1
                                    "
                                    type="button"
                                    @click="next"
                                    class="
                                        absolute
                                        right-2
                                        top-1/2
                                        z-20
                                        flex
                                        h-12
                                        w-12
                                        -translate-y-1/2
                                        items-center
                                        justify-center
                                        rounded-full
                                        bg-black/55
                                        text-white
                                        shadow-md
                                        ring-1
                                        ring-white/20
                                        backdrop-blur-sm
                                        transition
                                        hover:bg-black/75
                                    "
                                    aria-label="Next attachment"
                                >
                                    <ChevronRightIcon class="w-8 h-8" />
                                </button>

                            </div>

                        </DialogPanel>

                    </TransitionChild>

                </div>

            </Dialog>

        </TransitionRoot>

    </teleport>
</template>