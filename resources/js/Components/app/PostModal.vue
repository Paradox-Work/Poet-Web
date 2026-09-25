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
                                        v-if="showExtensionsText"
                                        class="border-l-4 border-amber-500 py-2 px-3 bg-amber-100 mt-3 text-gray-800"
                                    >
                                        Files must use one of the following extensions:

                                        <br>

                                        <small>
                                            {{ attachmentExtensions.join(', ') }}
                                        </small>
                                    </div>

                                    <div
                                        v-if="form.errors.attachments"
                                        class="mt-2 text-sm text-red-500"
                                    >
                                        {{ form.errors.attachments }}
                                    </div>

                                    <div
                                        v-if="computedAttachments.length"
                                        class="grid grid-cols-2 lg:grid-cols-3 gap-3 mt-4"
                                    >

                                        <div
                                            v-for="(myFile, index) in computedAttachments"
                                            :key="
                                                myFile.file
                                                    ? `${myFile.file.name}-${index}`
                                                    : `existing-${myFile.id}`
                                            "
                                        >
                                            <div
                                                class="group aspect-square bg-gray-100 rounded-md flex flex-col items-center justify-center text-gray-500 relative overflow-hidden border-2"
                                                :class="
                                                    getAttachmentError(myFile)
                                                        ? 'border-red-500'
                                                        : 'border-transparent'
                                                "
                                            >
    
                                                <div
                                                    v-if="
                                                        !myFile.file &&
                                                        form.deleted_file_ids.includes(myFile.id)
                                                    "
                                                    class="absolute z-30 left-0 bottom-0 right-0 py-2 px-3 text-sm bg-black/80 text-white flex justify-between items-center"
                                                >
                                                    To be deleted

                                                    <ArrowUturnLeftIcon
                                                        @click.stop="undoDelete(myFile)"
                                                        class="w-5 h-5 cursor-pointer"
                                                    />
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="removeFile(myFile)"
                                                    class="absolute z-20 right-2 top-2 w-7 h-7 flex items-center justify-center bg-black/40 text-white rounded-full hover:bg-black/60"
                                                >
                                                    <XMarkIcon class="h-5 w-5" />
                                                </button>


                                                <img
                                                    v-if="isImage(myFile.file ?? myFile)"
                                                    :src="myFile.url"
                                                    :alt="(myFile.file ?? myFile).name"
                                                    class="w-full h-full object-cover"
                                                    :class="
                                                        !myFile.file &&
                                                        form.deleted_file_ids.includes(myFile.id)
                                                            ? 'opacity-50'
                                                            : ''
                                                    "
                                                />


                                                <template v-else>

                                                    <PaperClipIcon class="w-10 h-10 mb-3" />

                                                    <small class="text-center px-2 break-all">
                                                        {{ (myFile.file ?? myFile).name }}
                                                    </small>

                                                </template>
                                                
                                            </div>

                                            <small
                                                v-if="getAttachmentError(myFile)"
                                                class="text-red-500"
                                            >
                                                {{ getAttachmentError(myFile) }}
                                            </small>

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
    PaperClipIcon,
    ArrowUturnLeftIcon
        } from '@heroicons/vue/24/solid';

import { 
    useForm,
    usePage
        } from '@inertiajs/vue3';

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

const attachmentExtensions =
    usePage().props.attachmentExtensions ?? [];

const emit = defineEmits([
    'update:modelValue'
]);

const attachmentFiles = ref([]);
const attachmentErrors = ref([]);

const showExtensionsText = computed(() => {

    for (const myFile of attachmentFiles.value) {

        const file = myFile.file;

        const parts = file.name.split('.');

        const extension =
            parts.length > 1
                ? parts.pop().toLowerCase()
                : '';

        if (
            !attachmentExtensions.includes(extension)
        ) {
            return true;
        }
    }

    return false;
});

const computedAttachments = computed(() => {

    return [
        ...(props.post.attachments ?? []),
        ...attachmentFiles.value
    ];

});

const form = useForm({
    id: null,
    body: '',
    attachments: [],
    deleted_file_ids: [],
    _method: 'POST'
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

        form.deleted_file_ids = [];
        form.attachments = [];
        form._method = post.id ? 'PUT' : 'POST';

        attachmentFiles.value = [];
        attachmentErrors.value = [];
    },
    {
        immediate: true
    }
);


function closeModal() {
    show.value = false;

    form.reset();
    attachmentFiles.value = [];
    attachmentErrors.value = [];
}

async function onAttachmentChoose(event) {

    attachmentErrors.value = [];

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


function removeFile(myFile) {

    if (myFile.file) {

        attachmentFiles.value =
            attachmentFiles.value.filter(
                file => file !== myFile
            );

        return;
    }


    if (
        !form.deleted_file_ids.includes(myFile.id)
    ) {
        form.deleted_file_ids.push(myFile.id);
    }

}

function undoDelete(myFile) {

    form.deleted_file_ids =
        form.deleted_file_ids.filter(
            id => id !== myFile.id
        );

}

function getAttachmentError(myFile) {

    if (!myFile.file) {
        return null;
    }

    const index =
        attachmentFiles.value.indexOf(myFile);

    return attachmentErrors.value[index] ?? null;
}

function processErrors(errors) {

    attachmentErrors.value = [];

    for (const key in errors) {

        if (!key.startsWith('attachments.')) {
            continue;
        }

        const parts = key.split('.');
        const index = Number(parts[1]);

        if (!Number.isNaN(index)) {
            attachmentErrors.value[index] =
                errors[key];
        }
    }
}

function submit() {

    attachmentErrors.value = [];

    form.attachments =
        attachmentFiles.value.map(
            myFile => myFile.file
        );

    const options = {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            closeModal();
        },
        
        onError: (errors) => {
        processErrors(errors);
    }
    };


    if (form.id) {

        form._method = 'PUT';

        form.post(
            route('post.update', form.id),
            options
        );

    } else {

        form._method = 'POST';

        form.post(
            route('post.create'),
            options
        );
    }

}

</script>