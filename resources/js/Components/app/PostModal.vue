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
                                class="w-full max-w-2xl transform overflow-hidden rounded-xl bg-white text-left align-middle shadow-xl transition-all dark:bg-gray-800 dark:text-gray-100"
                            >

                                <DialogTitle
                                    as="h3"
                                    class="flex items-center justify-between py-3 px-4 font-medium bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-100"
                                >

                                    {{
                                        form.id
                                            ? 'Update Post'
                                            : form.type === 'poem'
                                                ? 'Write Poem'
                                                : 'Create Post'
                                    }}

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

                                    <div
                                        v-if="form.errors.group_id"
                                        class="mb-3 rounded-md bg-red-100 px-3 py-2 text-sm text-red-700"
                                    >
                                        {{ form.errors.group_id }}
                                    </div>

                                    <div class="mb-4 flex rounded-lg bg-gray-100 p-1 dark:bg-gray-700">
                                        <button
                                            type="button"
                                            @click="chooseType('post')"
                                            :class="[
                                                'flex-1 rounded-md px-3 py-2 text-sm font-medium transition',
                                                form.type === 'post'
                                                    ? 'bg-white text-gray-900 shadow dark:bg-gray-900 dark:text-gray-100'
                                                    : 'text-gray-500 dark:text-gray-300'
                                            ]"
                                        >
                                            Post
                                        </button>

                                        <button
                                            type="button"
                                            @click="chooseType('poem')"
                                            :class="[
                                                'flex-1 rounded-md px-3 py-2 text-sm font-medium transition',
                                                form.type === 'poem'
                                                    ? 'bg-white text-gray-900 shadow dark:bg-gray-900 dark:text-gray-100'
                                                    : 'text-gray-500 dark:text-gray-300'
                                            ]"
                                        >
                                            Poem
                                        </button>
                                    </div>

                                    <div
                                        v-if="
                                            form.type === 'poem' &&
                                            !form.id
                                        "
                                        class="mb-3 flex items-center justify-between text-xs text-gray-400"
                                    >
                                        <span>
                                            {{
                                                draftState === 'saving'
                                                    ? 'Saving draft...'
                                                    : draftState === 'saved'
                                                        ? 'Draft saved'
                                                        : draftState === 'restored'
                                                            ? 'Draft restored'
                                                            : draftState === 'error'
                                                                ? 'Draft save failed'
                                                                : 'Draft autosave on'
                                            }}
                                        </span>

                                        <span
                                            v-if="draftSavedAt"
                                        >
                                            {{
                                                new Date(
                                                    draftSavedAt
                                                ).toLocaleTimeString(
                                                    [],
                                                    {
                                                        hour: '2-digit',
                                                        minute: '2-digit'
                                                    }
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        v-if="form.type === 'poem'"
                                        class="mb-4 space-y-3"
                                    >
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                Poem title
                                            </label>

                                            <input
                                                v-model="form.title"
                                                type="text"
                                                maxlength="160"
                                                placeholder="Give your poem a title..."
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                            />

                                            <p
                                                v-if="form.errors.title"
                                                class="mt-1 text-sm text-red-500"
                                            >
                                                {{ form.errors.title }}
                                            </p>
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                                Caption
                                                <span class="font-normal text-gray-400">
                                                    optional
                                                </span>
                                            </label>

                                            <textarea
                                                v-model="form.caption"
                                                rows="2"
                                                maxlength="500"
                                                placeholder="A short note, context, dedication, or thought about the piece..."
                                                class="block w-full resize-none rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                            />

                                            <div class="mt-1 flex justify-between text-xs text-gray-400">
                                                <span>Shown separately from the poem.</span>
                                                <span>{{ (form.caption ?? '').length }}/500</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="form.type === 'poem'"
                                        class="mb-2 text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Poetry mode keeps each Enter close to the previous line. Use an empty line between stanzas.
                                    </div>

                                    <TiptapEditor
                                        v-model="form.body"
                                        :poem-mode="form.type === 'poem'"
                                    />

                                    <div class="mt-4">
                                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Hashtags
                                            <span class="font-normal text-gray-400">
                                                optional
                                            </span>
                                        </label>

                                        <input
                                            v-model="hashtagsInput"
                                            type="text"
                                            placeholder="poetry, night, memories"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                        />

                                        <p class="mt-1 text-xs text-gray-400">
                                            Separate tags with spaces or commas. You can type them with or without #.
                                        </p>

                                        <p
                                            v-if="form.errors.hashtags"
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{ form.errors.hashtags }}
                                        </p>
                                    </div>

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
                                                class="group aspect-square bg-gray-100 rounded-md flex flex-col items-center justify-center text-gray-500 relative overflow-hidden border-2 dark:bg-gray-700 dark:text-gray-300"
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
                                        class="cursor-pointer flex items-center justify-center rounded-md bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200 flex-1 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600"
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
    onBeforeUnmount,
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
    router,
    useForm,
    usePage
} from '@inertiajs/vue3';

import TiptapEditor from '@/Components/app/TiptapEditor.vue';
import PostUserHeader from '@/Components/app/PostUserHeader.vue';
import { isImage } from '@/helpers.js';
import axios from 'axios';

const props = defineProps({

    post: {
        type: Object,
        required: true
    },

    group: {
        type: Object,
        default: null
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
const hashtagsInput = ref('');

const draftId = ref(null);
const draftState = ref('idle');
const draftSavedAt = ref(null);
const draftLoadAttempted = ref(false);

let draftSaveTimer = null;

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
    type: 'post',
    title: '',
    caption: '',
    hashtags: [],
    body: '',
    group_id: null,
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

        const isDraft =
            post.status === 'draft';

        form.id =
            isDraft
                ? null
                : (post.id ?? null);

        form.type = post.type ?? 'post';

        draftId.value =
            isDraft
                ? post.id
                : null;

        draftState.value =
            isDraft
                ? 'restored'
                : 'idle';

        draftSavedAt.value =
            isDraft
                ? (post.draft_saved_at ?? null)
                : null;

        draftLoadAttempted.value =
            isDraft;
        form.title = post.title ?? '';
        form.caption = post.caption ?? '';
        form.hashtags = post.hashtags ?? [];
        form.body = post.body ?? '';

        hashtagsInput.value =
            (post.hashtags ?? [])
                .map(tag => `#${tag}`)
                .join(' ');

        form.group_id =
            props.group?.id
            ?? post.group?.id
            ?? null;

        form.deleted_file_ids = [];
        form.attachments = [];
        form._method =
            isDraft || post.id
                ? 'PUT'
                : 'POST';

        attachmentFiles.value = [];
        attachmentErrors.value = [];
    },
    {
        immediate: true
    }
);


function closeModal() {
    show.value = false;

    if (draftSaveTimer) {
        clearTimeout(
            draftSaveTimer
        );

        draftSaveTimer = null;
    }

    form.reset();
    form.type = 'post';
    draftId.value = null;
    draftState.value = 'idle';
    draftSavedAt.value = null;
    draftLoadAttempted.value = false;
    hashtagsInput.value = '';
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

function hasDraftContent() {
    const bodyText =
        (form.body ?? '')
            .replace(/<[^>]*>/g, '')
            .replace(/&nbsp;/g, ' ')
            .trim();

    return Boolean(
        form.title?.trim() ||
        form.caption?.trim() ||
        bodyText ||
        hashtagsInput.value.trim()
    );
}

function draftPayload() {
    return {
        type: form.type,
        title:
            form.type === 'poem'
                ? form.title
                : null,
        caption:
            form.type === 'poem'
                ? form.caption
                : null,
        hashtags:
            normalizeHashtags(
                hashtagsInput.value
            ),
        body: form.body,
        group_id:
            props.group?.id
            ?? form.group_id
            ?? null
    };
}

async function loadLatestDraft() {
    if (
        form.id ||
        draftLoadAttempted.value ||
        form.type !== 'poem' ||
        hasDraftContent()
    ) {
        return;
    }

    draftLoadAttempted.value = true;

    try {
        const { data } =
            await axios.get(
                route(
                    'draft.latest'
                ),
                {
                    params: {
                        type: 'poem',
                        group_id:
                            props.group?.id
                            ?? null
                    }
                }
            );

        if (!data.draft) {
            return;
        }

        draftId.value =
            data.draft.id;

        form.title =
            data.draft.title ?? '';

        form.caption =
            data.draft.caption ?? '';

        form.body =
            data.draft.body ?? '';

        form.group_id =
            data.draft.group_id ?? null;

        form.hashtags =
            data.draft.hashtags ?? [];

        hashtagsInput.value =
            form.hashtags
                .map(tag => `#${tag}`)
                .join(' ');

        draftSavedAt.value =
            data.draft.draft_saved_at;

        draftState.value =
            'restored';

    } catch (error) {
        console.error(
            'Failed to restore draft:',
            error
        );
    }
}

async function saveDraft() {
    if (
        form.id ||
        form.type !== 'poem' ||
        !show.value ||
        !hasDraftContent()
    ) {
        return;
    }

    draftState.value =
        'saving';

    const payload =
        draftPayload();

    try {
        const request =
            draftId.value
                ? axios.put(
                    route(
                        'draft.update',
                        draftId.value
                    ),
                    payload
                )
                : axios.post(
                    route(
                        'draft.store'
                    ),
                    payload
                );

        const { data } =
            await request;

        draftId.value =
            data.draft.id;

        draftSavedAt.value =
            data.draft
                .draft_saved_at;

        draftState.value =
            'saved';

    } catch (error) {
        draftState.value =
            'error';

        console.error(
            'Failed to autosave draft:',
            error
        );
    }
}

function scheduleDraftSave() {
    if (
        form.id ||
        form.type !== 'poem' ||
        !show.value
    ) {
        return;
    }

    if (draftSaveTimer) {
        clearTimeout(
            draftSaveTimer
        );
    }

    draftSaveTimer =
        setTimeout(
            saveDraft,
            1200
        );
}

async function chooseType(type) {
    form.type = type;

    if (
        type === 'poem'
    ) {
        await loadLatestDraft();
    }
}

watch(
    [
        () => form.type,
        () => form.title,
        () => form.caption,
        () => form.body,
        hashtagsInput
    ],
    () => {
        scheduleDraftSave();
    }
);

onBeforeUnmount(() => {
    if (draftSaveTimer) {
        clearTimeout(
            draftSaveTimer
        );
    }
});

function normalizeHashtags(value) {
    const seen = new Set();

    return value
        .split(/[\\s,]+/)
        .map(tag =>
            tag
                .trim()
                .replace(/^#+/, '')
        )
        .filter(Boolean)
        .filter(tag => {
            const key = tag.toLocaleLowerCase();

            if (seen.has(key)) {
                return false;
            }

            seen.add(key);
            return true;
        })
        .slice(0, 10);
}

function submit() {

    attachmentErrors.value = [];

    form.hashtags =
        normalizeHashtags(
            hashtagsInput.value
        );

    if (form.type !== 'poem') {
        form.title = '';
        form.caption = '';
    }

    if (!form.id) {
        form.group_id =
            props.group?.id ?? null;
    }

    form.attachments =
        attachmentFiles.value.map(
            myFile => myFile.file
        );

    const options = {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            draftId.value = null;
            closeModal();
        },

        onError: (errors) => {
            processErrors(errors);
        }
    };


    if (
        form.id ||
        draftId.value
    ) {

        form._method = 'PUT';

        form.post(
            route(
                'post.update',
                form.id ??
                    draftId.value
            ),
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