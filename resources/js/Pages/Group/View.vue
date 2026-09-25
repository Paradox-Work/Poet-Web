<script setup>
import {
    computed,
    ref
} from 'vue';

import {
    Head,
    useForm
} from '@inertiajs/vue3';

import {
    CameraIcon,
    CheckCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

import {
    Tab,
    TabGroup,
    TabList,
    TabPanel,
    TabPanels
} from '@headlessui/vue';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';


const props = defineProps({
    group: Object,

    success: {
        type: String,
        default: null
    },

    errors: {
        type: Object,
        default: () => ({})
    }
});


const isAdmin = computed(
    () => props.group.role === 'admin'
);


const coverPreview = ref(null);

const thumbnailPreview = ref(null);


const coverForm = useForm({
    cover: null
});

const thumbnailForm = useForm({
    thumbnail: null
});


function onCoverChange(event) {

    const file =
        event.target.files?.[0];

    if (!file) {
        return;
    }

    coverForm.cover = file;

    coverPreview.value =
        URL.createObjectURL(file);
}


function onThumbnailChange(event) {

    const file =
        event.target.files?.[0];

    if (!file) {
        return;
    }

    thumbnailForm.thumbnail = file;

    thumbnailPreview.value =
        URL.createObjectURL(file);
}


function cancelCover() {

    coverForm.reset();

    coverPreview.value = null;
}


function cancelThumbnail() {

    thumbnailForm.reset();

    thumbnailPreview.value = null;
}


function submitCover() {

    coverForm.post(
        route(
            'group.updateImages',
            props.group.slug
        ),
        {
            forceFormData: true,

            preserveScroll: true,

            onSuccess: () => {
                coverPreview.value = null;

                coverForm.reset();
            }
        }
    );
}


function submitThumbnail() {

    thumbnailForm.post(
        route(
            'group.updateImages',
            props.group.slug
        ),
        {
            forceFormData: true,

            preserveScroll: true,

            onSuccess: () => {
                thumbnailPreview.value = null;

                thumbnailForm.reset();
            }
        }
    );
}
</script>


<template>

    <Head :title="group.name" />

    <AuthenticatedLayout>

        <div
            class="max-w-4xl mx-auto p-4"
        >

            <!-- Success -->
            <div
                v-if="success"
                class="mb-4 rounded-md bg-emerald-100 px-4 py-3 text-emerald-800"
            >
                {{ success }}
            </div>


            <!-- Group card -->
            <div
                class="overflow-hidden rounded-xl bg-white shadow"
            >

                <!-- COVER -->
                <div
                    class="group relative h-56 bg-gradient-to-br from-indigo-200 via-slate-200 to-purple-200"
                >

                    <img
                        v-if="
                            coverPreview ||
                            group.cover_url
                        "
                        :src="
                            coverPreview ||
                            group.cover_url
                        "
                        class="w-full h-full object-cover"
                        alt="Group cover"
                    />


                    <!-- Admin cover control -->
                    <div
                        v-if="isAdmin"
                        class="absolute right-3 top-3"
                    >

                        <label
                            v-if="!coverPreview"
                            class="relative cursor-pointer flex items-center gap-2 rounded-md bg-white/90 px-3 py-2 text-sm shadow hover:bg-white"
                        >
                            <CameraIcon
                                class="w-4 h-4"
                            />

                            Change cover

                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                @change="onCoverChange"
                            />
                        </label>


                        <div
                            v-else
                            class="flex gap-2"
                        >

                            <button
                                type="button"
                                @click="cancelCover"
                                class="flex items-center gap-1 rounded-md bg-white px-3 py-2 text-sm"
                            >
                                <XMarkIcon
                                    class="w-4 h-4"
                                />

                                Cancel
                            </button>


                            <button
                                type="button"
                                @click="submitCover"
                                :disabled="
                                    coverForm.processing
                                "
                                class="flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 text-sm text-white disabled:opacity-50"
                            >
                                <CheckCircleIcon
                                    class="w-4 h-4"
                                />

                                Save
                            </button>

                        </div>

                    </div>

                </div>


                <!-- HEADER -->
                <div
                    class="relative px-6 pb-6"
                >

                    <!-- THUMBNAIL -->
                    <div
                        class="relative -mt-16 w-32 h-32"
                    >

                        <img
                            v-if="
                                thumbnailPreview ||
                                group.thumbnail_url
                            "
                            :src="
                                thumbnailPreview ||
                                group.thumbnail_url
                            "
                            class="w-full h-full rounded-full border-4 border-white object-cover bg-white"
                            alt="Group thumbnail"
                        />


                        <div
                            v-else
                            class="w-full h-full rounded-full border-4 border-white bg-indigo-100 text-indigo-700 flex items-center justify-center text-5xl font-bold"
                        >
                            {{
                                group.name
                                    ?.charAt(0)
                                    .toUpperCase()
                            }}
                        </div>


                        <label
                            v-if="
                                isAdmin &&
                                !thumbnailPreview
                            "
                            class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/0 text-white opacity-0 hover:bg-black/40 hover:opacity-100 transition"
                        >
                            <CameraIcon
                                class="w-8 h-8"
                            />

                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                @change="onThumbnailChange"
                            />
                        </label>


                        <div
                            v-if="
                                isAdmin &&
                                thumbnailPreview
                            "
                            class="absolute -right-2 top-1 flex flex-col gap-2"
                        >

                            <button
                                type="button"
                                @click="cancelThumbnail"
                                class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center"
                            >
                                <XMarkIcon
                                    class="w-5 h-5"
                                />
                            </button>

                            <button
                                type="button"
                                @click="submitThumbnail"
                                :disabled="
                                    thumbnailForm.processing
                                "
                                class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center disabled:opacity-50"
                            >
                                <CheckCircleIcon
                                    class="w-5 h-5"
                                />
                            </button>

                        </div>

                    </div>


                    <!-- Name -->
                    <div
                        class="mt-4 flex items-start justify-between gap-4"
                    >

                        <div>

                            <div
                                class="flex items-center gap-3"
                            >

                                <h1
                                    class="text-2xl font-bold text-gray-900"
                                >
                                    {{ group.name }}
                                </h1>


                                <span
                                    v-if="
                                        group.role === 'admin'
                                    "
                                    class="rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700"
                                >
                                    Admin
                                </span>

                            </div>


                            <p
                                v-if="group.about"
                                class="mt-2 text-gray-600"
                            >
                                {{ group.about }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- VALIDATION -->
            <div
                v-if="
                    coverForm.errors.cover ||
                    thumbnailForm.errors.thumbnail
                "
                class="mt-3 rounded-md bg-red-100 px-4 py-3 text-red-700"
            >
                {{
                    coverForm.errors.cover ||
                    thumbnailForm.errors.thumbnail
                }}
            </div>


            <!-- TABS -->
            <div
                class="mt-4 rounded-xl bg-white shadow"
            >

                <TabGroup>

                    <TabList
                        class="flex border-b"
                    >

                        <Tab
                            v-slot="{ selected }"
                            as="template"
                        >
                            <button
                                :class="[
                                    'px-5 py-3 text-sm font-medium',

                                    selected
                                        ? 'border-b-2 border-indigo-600 text-indigo-600'
                                        : 'text-gray-500'
                                ]"
                            >
                                Posts
                            </button>
                        </Tab>


                        <Tab
                            v-slot="{ selected }"
                            as="template"
                        >
                            <button
                                :class="[
                                    'px-5 py-3 text-sm font-medium',

                                    selected
                                        ? 'border-b-2 border-indigo-600 text-indigo-600'
                                        : 'text-gray-500'
                                ]"
                            >
                                Members
                            </button>
                        </Tab>


                        <Tab
                            v-slot="{ selected }"
                            as="template"
                        >
                            <button
                                :class="[
                                    'px-5 py-3 text-sm font-medium',

                                    selected
                                        ? 'border-b-2 border-indigo-600 text-indigo-600'
                                        : 'text-gray-500'
                                ]"
                            >
                                About
                            </button>
                        </Tab>

                    </TabList>


                    <TabPanels>

                        <TabPanel
                            class="p-6 text-gray-500"
                        >
                            Group posts will appear here.
                        </TabPanel>


                        <TabPanel
                            class="p-6 text-gray-500"
                        >
                            Group members will appear here.
                        </TabPanel>


                        <TabPanel
                            class="p-6"
                        >

                            <h3
                                class="font-semibold text-lg"
                            >
                                About this group
                            </h3>

                            <p
                                class="mt-2 whitespace-pre-wrap text-gray-600"
                            >
                                {{
                                    group.about ||
                                    'No description has been added yet.'
                                }}
                            </p>


                            <div
                                class="mt-4 text-sm text-gray-500"
                            >
                                Auto approval:
                                <strong>
                                    {{
                                        group.auto_approval
                                            ? 'Enabled'
                                            : 'Disabled'
                                    }}
                                </strong>
                            </div>

                        </TabPanel>

                    </TabPanels>

                </TabGroup>

            </div>

        </div>

    </AuthenticatedLayout>

</template>