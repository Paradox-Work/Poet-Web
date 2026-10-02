<script setup>
import UserListItem
    from '@/Components/app/UserListItem.vue';
    
import InviteUserModal
    from '@/Pages/Group/InviteUserModal.vue';

import CreatePost
    from '@/Components/app/CreatePost.vue';

import PostList
    from '@/Components/app/PostList.vue';

import {
    computed,
    ref
} from 'vue';

import {
    Head,
    Link,
    useForm,
    usePage
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
    },
    
    users: {
        type: Array,
        default: () => []
    },

    requests: {
        type: Array,
        default: () => []
    },

    posts: {
        type: Object,
        default: null
    },

});

const requestForm = useForm({
    user_id: null,
    action: null
});

function resolveRequest(
    user,
    action
) {
    requestForm.user_id = user.id;
    requestForm.action = action;

    requestForm.post(
        route(
            'group.resolveJoinRequest',
            props.group.slug
        ),
        {
            preserveScroll: true,

            onFinish: () => {
                requestForm.reset();
            }
        }
    );
}

const isAdmin = computed(
    () => props.group.role === 'admin'
);

const isApprovedMember = computed(
    () =>
        props.group.status ===
        'approved'
);

const showInviteUserModal = ref(false);

const coverPreview = ref(null);

const thumbnailPreview = ref(null);


const coverForm = useForm({
    cover: null
});

const thumbnailForm = useForm({
    thumbnail: null
});

const roleForm = useForm({
    user_id: null,
    role: null
});

const removeMemberForm =
    useForm({
        user_id: null
    });

const groupSettingsForm = useForm({
    name: props.group.name,

    auto_approval:
        Boolean(props.group.auto_approval),

    about:
        props.group.about ?? ''
});

function updateGroup() {
    groupSettingsForm.put(
        route(
            'group.update',
            props.group.slug
        ),
        {
            preserveScroll: true
        }
    );
}

function changeMemberRole(
    user,
    role
) {
    if (user.role === role) {
        return;
    }

    roleForm.user_id = user.id;
    roleForm.role = role;

    roleForm.post(
        route(
            'group.changeRole',
            props.group.slug
        ),
        {
            preserveScroll: true,

            onFinish: () => {
                roleForm.reset();
            }
        }
    );
}

function removeMember(
    user
) {
    if (
        !window.confirm(
            `Are you sure you want to remove "${user.name}" from this group?`
        )
    ) {
        return;
    }

    removeMemberForm.user_id =
        user.id;

    removeMemberForm.delete(
        route(
            'group.removeUser',
            props.group.slug
        ),
        {
            preserveScroll: true,

            onFinish: () => {
                removeMemberForm.reset();
            }
        }
    );
}

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

const page = usePage();

const authUser = computed(
    () => page.props.auth.user
);

const joinForm = useForm({});

function joinToGroup() {
    joinForm.post(
        route(
            'group.join',
            props.group.slug
        ),
        {
            preserveScroll: true
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

                        <button
                            v-if="isAdmin"
                            type="button"
                            @click="showInviteUserModal = true"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                        >
                            Invite user
                        </button>


                        <Link
                            v-else-if="!authUser"
                            :href="route('login')"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                        >
                            Login to join
                        </Link>


                        <button
                            v-else-if="
                                !group.role &&
                                group.auto_approval
                            "
                            type="button"
                            :disabled="joinForm.processing"
                            @click="joinToGroup"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            {{
                                joinForm.processing
                                    ? 'Joining...'
                                    : 'Join group'
                            }}
                        </button>


                        <button
                            v-else-if="
                                !group.role &&
                                !group.auto_approval
                            "
                            type="button"
                            :disabled="joinForm.processing"
                            @click="joinToGroup"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            {{
                                joinForm.processing
                                    ? 'Sending...'
                                    : 'Request to join'
                            }}
                        </button>


                        <span
                            v-else-if="
                                group.status === 'pending'
                            "
                            class="rounded-md bg-amber-100 px-4 py-2 text-sm font-medium text-amber-700"
                        >
                            Membership pending
                        </span>

                                

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

                        <TabPanel class="p-3">

                            <template v-if="posts">

                                <CreatePost
                                    v-if="isApprovedMember"
                                    :group="group"
                                />

                                <PostList
                                    :posts="posts"
                                />

                            </template>


                            <div
                                v-else
                                class="py-8 text-center text-gray-500"
                            >
                                Only approved group members can view group posts.
                            </div>

                        </TabPanel>


                        <TabPanel class="p-6">

                            <!-- Pending requests -->
                            <div
                                v-if="
                                    isAdmin &&
                                    requests.length
                                "
                                class="mb-6"
                            >
                                <h3
                                    class="mb-3 font-semibold text-gray-900"
                                >
                                    Pending requests
                                </h3>

                                <div
                                    class="overflow-hidden rounded-lg border"
                                >
                                    <UserListItem
                                        v-for="user in requests"
                                        :key="user.id"
                                        :user="user"
                                        show-actions
                                        :processing="
                                            requestForm.processing
                                        "
                                        @approve="
                                            resolveRequest(
                                                $event,
                                                'approve'
                                            )
                                        "
                                        @reject="
                                            resolveRequest(
                                                $event,
                                                'reject'
                                            )
                                        "
                                    />
                                </div>
                            </div>


                            <!-- Approved members -->
                            <h3
                                class="mb-3 font-semibold text-gray-900"
                            >
                                Members
                            </h3>

                            <div
                                v-if="users.length"
                                class="overflow-hidden rounded-lg border"
                            >
                                <UserListItem
                                    v-for="user in users"
                                    :key="user.id"
                                    :user="user"
                                    :show-role-control="isAdmin"
                                    :is-owner="
                                        user.id === group.user_id
                                    "
                                    :role-processing="
                                        roleForm.processing ||
                                        removeMemberForm.processing
                                    "
                                    @role-change="
                                        changeMemberRole
                                    "
                                    @remove="
                                        removeMember
                                    "
                                />
                            </div>

                            <p
                                v-else
                                class="text-gray-500"
                            >
                                No members yet.
                            </p>

                        </TabPanel>


                        <TabPanel class="p-6">

                            <!-- Admin settings -->
                            <form
                                v-if="isAdmin"
                                @submit.prevent="updateGroup"
                            >
                                <h3
                                    class="mb-5 text-lg font-semibold text-gray-900"
                                >
                                    Group settings
                                </h3>


                                <!-- Name -->
                                <div class="mb-5">
                                    <label
                                        for="group-name"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Group name
                                    </label>

                                    <input
                                        id="group-name"
                                        v-model="groupSettingsForm.name"
                                        type="text"
                                        maxlength="255"
                                        class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />

                                    <p
                                        v-if="groupSettingsForm.errors.name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ groupSettingsForm.errors.name }}
                                    </p>
                                </div>


                                <!-- Auto approval -->
                                <div class="mb-5">
                                    <label
                                        class="flex items-center gap-2"
                                    >
                                        <input
                                            v-model="
                                                groupSettingsForm.auto_approval
                                            "
                                            type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />

                                        <span
                                            class="text-sm text-gray-700"
                                        >
                                            Automatically approve new members
                                        </span>
                                    </label>

                                    <p
                                        class="mt-1 text-xs text-gray-500"
                                    >
                                        When disabled, new members must be approved by a group administrator.
                                    </p>

                                    <p
                                        v-if="
                                            groupSettingsForm.errors.auto_approval
                                        "
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{
                                            groupSettingsForm.errors
                                                .auto_approval
                                        }}
                                    </p>
                                </div>


                                <!-- About -->
                                <div class="mb-5">
                                    <label
                                        for="group-about"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        About group
                                    </label>

                                    <textarea
                                        id="group-about"
                                        v-model="groupSettingsForm.about"
                                        rows="6"
                                        maxlength="5000"
                                        class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />

                                    <p
                                        v-if="groupSettingsForm.errors.about"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ groupSettingsForm.errors.about }}
                                    </p>
                                </div>


                                <button
                                    type="submit"
                                    :disabled="
                                        groupSettingsForm.processing
                                    "
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{
                                        groupSettingsForm.processing
                                            ? 'Saving...'
                                            : 'Save changes'
                                    }}
                                </button>

                            </form>


                            <!-- Normal visitor view -->
                            <div v-else>

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

                            </div>

                        </TabPanel>

                    </TabPanels>

                </TabGroup>

            </div>

        </div>

    </AuthenticatedLayout>

    <InviteUserModal
        v-model="showInviteUserModal"
        :group="group"
    />

</template>