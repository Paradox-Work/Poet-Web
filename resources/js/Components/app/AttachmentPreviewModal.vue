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

import { isImage } from '@/helpers.js';


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
    <teleport to="body">

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
                            class="relative flex w-full h-screen bg-slate-800"
                        >

                            <button
                                type="button"
                                @click="closeModal"
                                class="absolute right-4 top-4 z-30 w-10 h-10 rounded-full hover:bg-black/30 flex items-center justify-center text-white"
                            >
                                <XMarkIcon class="w-6 h-6" />
                            </button>


                            <button
                                v-if="currentIndex > 0"
                                type="button"
                                @click="prev"
                                class="absolute z-20 left-0 top-0 h-full w-14 flex items-center justify-center text-white hover:bg-black/20"
                            >
                                <ChevronLeftIcon class="w-10" />
                            </button>


                            <button
                                v-if="
                                    currentIndex <
                                    attachments.length - 1
                                "
                                type="button"
                                @click="next"
                                class="absolute z-20 right-0 top-0 h-full w-14 flex items-center justify-center text-white hover:bg-black/20"
                            >
                                <ChevronRightIcon class="w-10" />
                            </button>


                            <div
                                v-if="attachment"
                                class="flex items-center justify-center w-full h-full p-10"
                            >

                                <img
                                    v-if="isImage(attachment)"
                                    :src="attachment.url"
                                    :alt="attachment.name"
                                    class="max-w-full max-h-full object-contain"
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

                            </div>

                        </DialogPanel>

                    </TransitionChild>

                </div>

            </Dialog>

        </TransitionRoot>

    </teleport>
</template>