<script setup>
import {
    computed,
    ref
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
    BookmarkIcon
} from '@heroicons/vue/24/solid';

import axios from 'axios';

import { useI18n } from '@/i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: Boolean
});

const emit = defineEmits([
    'update:modelValue',
    'created'
]);

const name = ref('');
const about = ref('');
const autoApproval = ref(true);
const visibility = ref('private');

const submitting = ref(false);

const errors = ref({});

const show = computed({
    get: () => props.modelValue,

    set: value =>
        emit(
            'update:modelValue',
            value
        )
});

function resetModal() {
    name.value = '';
    about.value = '';
    autoApproval.value = true;
    visibility.value = 'private';
    errors.value = {};
}

function closeModal() {
    show.value = false;

    resetModal();
}

async function submit() {

    if (submitting.value) {
        return;
    }

    submitting.value = true;

    errors.value = {};

    try {

        const { data } =
            await axios.post(
                route('group.create'),
                {
                    name: name.value,
                    about: about.value,
                    auto_approval:
                        autoApproval.value,
                    visibility:
                        visibility.value,
                }
            );

        emit(
            'created',
            data.data ?? data
        );

        closeModal();

    } catch (error) {

        if (
            error.response?.status === 422
        ) {
            errors.value =
                error.response.data.errors;
        } else {
            console.error(
                'Failed to create group:',
                error
            );
        }

    } finally {

        submitting.value = false;
    }
}
</script>

<template>
    <Teleport to="body">

        <TransitionRoot
            appear
            :show="show"
            as="template"
        >
            <Dialog
                as="div"
                class="relative z-50"
                @close="closeModal"
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
                    <div
                        class="fixed inset-0 bg-black/25"
                    />
                </TransitionChild>

                <div
                    class="fixed inset-0 overflow-y-auto"
                >
                    <div
                        class="flex min-h-full items-center justify-center p-4"
                    >

                        <DialogPanel
                            class="w-full max-w-md rounded bg-white shadow-xl dark:bg-gray-800 dark:text-gray-100"
                        >

                            <DialogTitle
                                class="flex items-center justify-between px-4 py-3 bg-gray-100 font-medium dark:bg-gray-700"
                            >
                                {{ t('group.create_title') }}

                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="w-8 h-8 rounded-full hover:bg-black/5 flex items-center justify-center"
                                >
                                    <XMarkIcon
                                        class="w-4 h-4"
                                    />
                                </button>
                            </DialogTitle>


                            <div class="p-4">

                                <div class="mb-4">

                                    <label
                                        class="block mb-1"
                                    >
                                        {{ t('group.name') }}
                                    </label>

                                    <input
                                        v-model="name"
                                        type="text"
                                        maxlength="255"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    />

                                    <div
                                        v-if="errors.name"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ errors.name[0] }}
                                    </div>

                                </div>


                                <div class="mb-4">

                                    <label
                                        class="flex items-center gap-2"
                                    >
                                        <input
                                            v-model="autoApproval"
                                            type="checkbox"
                                        />

                                        {{ t('group.auto_approve') }}
                                    </label>

                                </div>


                                <div class="mb-4">
                                    <label class="mb-1 block">
                                        {{ t('groups.visibility') }}
                                    </label>

                                    <select
                                        v-model="visibility"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    >
                                        <option value="private">
                                            {{ t('groups.private') }}
                                        </option>
                                        <option value="public">
                                            {{ t('groups.public') }}
                                        </option>
                                    </select>

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{
                                            visibility === 'public'
                                                ? t('groups.public_help')
                                                : t('groups.private_help')
                                        }}
                                    </p>

                                    <div
                                        v-if="errors.visibility"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.visibility[0] }}
                                    </div>
                                </div>

                                <div>

                                    <label
                                        class="block mb-1"
                                    >
                                        {{ t('group.about') }}
                                    </label>

                                    <textarea
                                        v-model="about"
                                        rows="4"
                                        maxlength="5000"
                                        class="w-full rounded-md border-gray-300 resize-none dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                                    />

                                    <div
                                        v-if="errors.about"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ errors.about[0] }}
                                    </div>

                                </div>

                            </div>


                            <div
                                class="flex justify-end gap-2 px-4 py-3"
                            >

                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="bg-gray-100 hover:bg-gray-200 rounded-md py-2 px-4 dark:bg-gray-700 dark:hover:bg-gray-600"
                                >
                                    {{ t('group.cancel') }}
                                </button>

                                <button
                                    type="button"
                                    @click="submit"
                                    :disabled="
                                        submitting ||
                                        !name.trim()
                                    "
                                    class="flex items-center rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    <BookmarkIcon
                                        class="w-4 h-4 mr-2"
                                    />

                                    {{
                                        submitting
                                            ? t('group.creating')
                                            : t('group.create')
                                    }}
                                </button>

                            </div>

                        </DialogPanel>

                    </div>
                </div>

            </Dialog>
        </TransitionRoot>

    </Teleport>
</template>