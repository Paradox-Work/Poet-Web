<script setup>
import {
    computed,
    watch
} from 'vue';

import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot
} from '@headlessui/vue';

import {
    EnvelopeIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

import {
    useForm
} from '@inertiajs/vue3';

import TextInput
    from '@/Components/TextInput.vue';


const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },

    group: {
        type: Object,
        required: true
    }
});


const emit = defineEmits([
    'update:modelValue'
]);


const show = computed({
    get: () => props.modelValue,

    set: value => {
        emit(
            'update:modelValue',
            value
        );
    }
});


const form = useForm({
    identifier: ''
});


function closeModal() {
    show.value = false;
}


function submit() {
    form.post(
        route(
            'group.inviteUsers',
            props.group.slug
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                form.reset();

                closeModal();
            }
        }
    );
}


watch(
    () => props.modelValue,
    value => {
        if (!value) {
            form.reset();

            form.clearErrors();
        }
    }
);
</script>


<template>
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
                enter="duration-200 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-150 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div
                    class="fixed inset-0 bg-black/40"
                />
            </TransitionChild>


            <div
                class="fixed inset-0 overflow-y-auto"
            >
                <div
                    class="flex min-h-full items-center justify-center p-4"
                >
                    <TransitionChild
                        as="template"
                        enter="duration-200 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-150 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-xl"
                        >
                            <DialogTitle
                                class="flex items-center justify-between border-b px-5 py-4"
                            >
                                <div>
                                    <h2
                                        class="font-semibold text-gray-900"
                                    >
                                        Invite user
                                    </h2>

                                    <p
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        Invite someone to {{ group.name }}
                                    </p>
                                </div>


                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="rounded-full p-2 text-gray-500 hover:bg-gray-100"
                                >
                                    <XMarkIcon
                                        class="w-5 h-5"
                                    />
                                </button>
                            </DialogTitle>


                            <form
                                @submit.prevent="submit"
                            >
                                <div
                                    class="p-5"
                                >
                                    <label
                                        for="group-invite-identifier"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Username or email
                                    </label>


                                    <TextInput
                                        id="group-invite-identifier"
                                        v-model="form.identifier"
                                        type="text"
                                        class="mt-2 block w-full"
                                        placeholder="username or email@example.com"
                                        autofocus
                                    />


                                    <p
                                        v-if="form.errors.identifier"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.identifier }}
                                    </p>


                                    <p
                                        class="mt-2 text-xs text-gray-500"
                                    >
                                        The user will receive an email containing an invitation link.
                                    </p>
                                </div>


                                <div
                                    class="flex justify-end gap-2 border-t bg-gray-50 px-5 py-4"
                                >
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="rounded-md px-4 py-2 text-sm text-gray-700 hover:bg-gray-200"
                                    >
                                        Cancel
                                    </button>


                                    <button
                                        type="submit"
                                        :disabled="
                                            form.processing ||
                                            !form.identifier.trim()
                                        "
                                        class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <EnvelopeIcon
                                            class="w-4 h-4"
                                        />

                                        {{
                                            form.processing
                                                ? 'Sending...'
                                                : 'Send invitation'
                                        }}
                                    </button>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>