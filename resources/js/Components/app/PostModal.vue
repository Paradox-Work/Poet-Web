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

                    <div class="fixed inset-0 bg-black/25" />

                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">

                    <div
                        class="flex min-h-full items-center justify-center p-4 text-center"
                    >

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
                                class="w-full max-w-md transform overflow-hidden rounded bg-white text-left align-middle shadow-xl transition-all"
                            >

                                <DialogTitle
                                    as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium bg-gray-100 text-gray-900"
                                >

                                    {{ form.id ? 'Update Post' : 'Create Post' }}

                                    <button
                                        @click="closeModal"
                                        class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center"
                                    >

                                        <XMarkIcon class="w-4 h-4" />

                                    </button>

                                </DialogTitle>

                                <div class="p-4">

                                    <PostUserHeader
                                        v-if="post.user"
                                        :post="post"
                                        :show-time="false"
                                        class="mb-4"
                                    />

                                    <TiptapEditor
                                        v-model="form.body"
                                    />

                                    <div
                                        v-if="attachmentFiles.length"
                                        class="grid grid-cols-2 lg:grid-cols-3 gap-3 mt-4"
                                    >

                                        <div
                                            v-for="(myFile, index) in attachmentFiles"
                                            :key="`${myFile.file.name}-${index}`"
                                            class="group aspect-square bg-gray-100 rounded-md flex flex-col items-center justify-center text-gray-500 relative overflow-hidden"
                                        >

                                            <button
                                                type="button"
                                                @click="removeFile(myFile)"
                                                class="absolute z-20 right-2 top-2 w-7 h-7 flex items-center justify-center bg-black/40 text-white rounded-full hover:bg-black/60"
                                            >
                                                <XMarkIcon class="h-5 w-5" />
                                            </button>


                                            <img
                                                v-if="isImage(myFile.file)"
                                                :src="myFile.url"
                                                :alt="myFile.file.name"
                                                class="w-full h-full object-cover"
                                            />


                                            <template v-else>

                                                <PaperClipIcon class="w-10 h-10 mb-3" />

                                                <small class="text-center px-2 break-all">
                                                    {{ myFile.file.name }}
                                                </small>

                                            </template>

                                        </div>

                                    </div>

                                </div>

                                <div class="flex gap-2 py-3 px-4">

                                    <label
                                        class="cursor-pointer flex items-center justify-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 flex-1"
                                    >

                                        <PaperClipIcon class="w-4 h-4 mr-2" />

                                        Attach Files

                                        <input
                                            type="file"
                                            multiple
                                            class="hidden"
                                            @change="onAttachmentChoose"
                                        />

                                    </label>


                                    <button
                                        type="button"
                                        class="flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 flex-1"
                                        @click="submit"
                                    >

                                        {{ form.id ? 'Save Changes' : 'Publish Post' }}

                                    </button>

                                </div>

                            </DialogPanel>

                        </TransitionChild>

                    </div>

                </div>

            </Dialog>

        </TransitionRoot>

    </teleport>

</template>


<script setup>

import {
    computed,
    ref,
    watch
} from 'vue';

import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle
} from '@headlessui/vue';

import { 
    XMarkIcon,
    PaperClipIcon
        } from '@heroicons/vue/24/solid';

import { useForm } from '@inertiajs/vue3';

import TiptapEditor from '@/Components/app/TiptapEditor.vue';
import PostUserHeader from '@/Components/app/PostUserHeader.vue';
import { isImage } from '@/helpers.js';

const props = defineProps({

    post: {
        type: Object,
        required: true
    },

    modelValue: Boolean

});


const emit = defineEmits([
    'update:modelValue'
]);

const attachmentFiles = ref([]);

const form = useForm({
    id: null,
    body: ''
});


const show = computed({

    get: () => props.modelValue,

    set: (value) => {
        emit('update:modelValue', value);
    }

});


watch(
    [
        () => props.post,
        () => props.modelValue
    ],
    ([post, isOpen]) => {

        if (!isOpen || !post) {
            return;
        }

        form.id = post.id ?? null;
        form.body = post.body ?? '';
    },
    {
        immediate: true
    }
);


function closeModal() {
    show.value = false;

    form.reset();
    attachmentFiles.value = [];
}

async function onAttachmentChoose(event) {

    for (const file of event.target.files) {

        attachmentFiles.value.push({
            file,
            url: await readFile(file)
        });

    }

    event.target.value = '';
}


function readFile(file) {

    return new Promise((resolve, reject) => {

        if (!isImage(file)) {
            resolve(null);
            return;
        }

        const reader = new FileReader();

        reader.onload = () => {
            resolve(reader.result);
        };

        reader.onerror = reject;

        reader.readAsDataURL(file);
    });
}


function removeFile(fileToRemove) {

    attachmentFiles.value =
        attachmentFiles.value.filter(
            file => file !== fileToRemove
        );

}

function submit() {

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            show.value = false;
            form.reset();
            attachmentFiles.value = [];
        }
    };


    if (form.id) {

        form.put(
            route('post.update', form.id),
            options
        );

    } else {

        form.post(
            route('post.create'),
            options
        );

    }

}

</script>