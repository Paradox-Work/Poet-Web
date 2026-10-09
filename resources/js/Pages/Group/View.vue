<script setup>
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
    Tab,
    TabGroup,
    TabList,
    TabPanel,
    TabPanels
} from '@headlessui/vue';

import {
    CameraIcon,
    CheckCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

import {
    ChevronDownIcon,
    DocumentTextIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    UserPlusIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import Dropdown
    from '@/Components/Dropdown.vue';

import InviteUserModal
    from '@/Pages/Group/InviteUserModal.vue';

import PostList
    from '@/Components/app/PostList.vue';

import PostModal
    from '@/Components/app/PostModal.vue';

import TabPhotos
    from '@/Pages/Profile/TabPhotos.vue';

import UserListItem
    from '@/Components/app/UserListItem.vue';

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

    photos: {
        type: Array,
        default: () => []
    }
});

const page = usePage();

const authUser = computed(
    () => page.props.auth.user
);

const isAdmin = computed(
    () => props.group.role === 'admin'
);

const isApprovedMember = computed(
    () =>
        props.group.status ===
        'approved'
);

const memberCount = computed(
    () => props.users.length
);

const memberSearch = ref('');

const filteredUsers = computed(() => {
    const value =
        memberSearch.value
            .trim()
            .toLocaleLowerCase();

    if (!value) {
        return props.users;
    }

    return props.users.filter(
        user =>
            user.name
                ?.toLocaleLowerCase()
                .includes(value) ||
            user.username
                ?.toLocaleLowerCase()
                .includes(
                    value.replace(
                        /^@/,
                        ''
                    )
                )
    );
});

const showInviteUserModal = ref(false);
const showCreatePostModal = ref(false);
const editingGroupSettings = ref(false);

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

const requestForm = useForm({
    user_id: null,
    action: null
});

const joinForm = useForm({});

const groupSettingsForm = useForm({
    name: props.group.name,

    auto_approval:
        Boolean(props.group.auto_approval),

    about:
        props.group.about ?? ''
});

const newGroupPost = computed(() => ({
    id: null,
    type: 'post',
    body: '',
    user: authUser.value,
    group: props.group,
    updated_at: null
}));

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

function updateGroup() {
    groupSettingsForm.put(
        route(
            'group.update',
            props.group.slug
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                editingGroupSettings.value =
                    false;
            }
        }
    );
}

function cancelGroupEdit() {
    groupSettingsForm.name =
        props.group.name;

    groupSettingsForm.auto_approval =
        Boolean(
            props.group.auto_approval
        );

    groupSettingsForm.about =
        props.group.about ?? '';

    groupSettingsForm.clearErrors();

    editingGroupSettings.value = false;
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
                thumbnailPreview.value =
                    null;

                thumbnailForm.reset();
            }
        }
    );
}

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
            class="h-full overflow-y-auto bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto w-full max-w-5xl px-3 pb-10 pt-4 sm:px-5 sm:pt-5 lg:px-6"
            >
                <div
                    v-if="success"
                    class="mb-4 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-medium text-white"
                >
                    {{ success }}
                </div>

                <section
                    class="overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)]"
                >
                    <div
                        class="group relative h-40 bg-gradient-to-br from-indigo-200 via-slate-200 to-purple-200 sm:h-48"
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
                            class="h-full w-full object-cover"
                            alt="Group cover"
                        />

                        <div
                            v-if="isAdmin"
                            class="absolute right-3 top-3"
                        >
                            <label
                                v-if="!coverPreview"
                                class="relative flex cursor-pointer items-center gap-2 rounded-full bg-black/55 px-3 py-2 text-xs font-medium text-white opacity-100 backdrop-blur transition sm:opacity-0 sm:group-hover:opacity-100"
                            >
                                <CameraIcon
                                    class="h-4 w-4"
                                />
                                Change cover

                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    @change="onCoverChange"
                                />
                            </label>

                            <div
                                v-else
                                class="flex gap-2 rounded-full bg-black/55 p-1.5 backdrop-blur"
                            >
                                <button
                                    type="button"
                                    @click="cancelCover"
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-white transition hover:bg-white/15"
                                    aria-label="Cancel cover change"
                                >
                                    <XMarkIcon
                                        class="h-4 w-4"
                                    />
                                </button>

                                <button
                                    type="button"
                                    @click="submitCover"
                                    :disabled="
                                        coverForm.processing
                                    "
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-white transition hover:bg-white/15 disabled:opacity-50"
                                    aria-label="Save cover"
                                >
                                    <CheckCircleIcon
                                        class="h-4 w-4"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="relative flex flex-col gap-4 px-4 pb-5 sm:flex-row sm:items-end sm:px-6"
                    >
                        <div
                            class="group/avatar relative -mt-10 h-20 w-20 shrink-0 rounded-full ring-4 ring-[var(--poet-surface)] sm:-mt-12 sm:h-24 sm:w-24"
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
                                class="h-full w-full rounded-full object-cover"
                                alt="Group thumbnail"
                            />

                            <div
                                v-else
                                class="flex h-full w-full items-center justify-center rounded-full bg-[var(--poet-accent-soft)] font-serif text-3xl font-semibold text-[var(--poet-accent)]"
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
                                class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/45 text-white opacity-100 transition sm:opacity-0 sm:group-hover/avatar:opacity-100"
                            >
                                <CameraIcon
                                    class="h-6 w-6"
                                />

                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    @change="onThumbnailChange"
                                />
                            </label>

                            <div
                                v-if="
                                    isAdmin &&
                                    thumbnailPreview
                                "
                                class="absolute -right-2 top-1 flex flex-col gap-1"
                            >
                                <button
                                    type="button"
                                    @click="cancelThumbnail"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow"
                                    aria-label="Cancel thumbnail change"
                                >
                                    <XMarkIcon
                                        class="h-4 w-4"
                                    />
                                </button>

                                <button
                                    type="button"
                                    @click="submitThumbnail"
                                    :disabled="
                                        thumbnailForm.processing
                                    "
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow disabled:opacity-50"
                                    aria-label="Save thumbnail"
                                >
                                    <CheckCircleIcon
                                        class="h-4 w-4"
                                    />
                                </button>
                            </div>
                        </div>

                        <div
                            class="min-w-0 flex-1 sm:pb-1"
                        >
                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <h1
                                    class="truncate font-serif text-2xl font-semibold text-[var(--poet-text)]"
                                >
                                    {{ group.name }}
                                </h1>

                                <span
                                    v-if="
                                        group.role ===
                                        'admin'
                                    "
                                    class="rounded-full bg-[var(--poet-accent-soft)] px-2.5 py-1 text-[11px] font-medium text-[var(--poet-accent)]"
                                >
                                    Admin
                                </span>
                            </div>

                            <div
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--poet-muted)] sm:text-sm"
                            >
                                <span>
                                    {{ memberCount }}
                                    member{{ memberCount === 1 ? '' : 's' }}
                                </span>

                                <span>
                                    {{
                                        group.auto_approval
                                            ? 'Open membership'
                                            : 'Approval required'
                                    }}
                                </span>
                            </div>

                            <p
                                v-if="group.about"
                                class="mt-2 line-clamp-2 max-w-2xl text-sm leading-relaxed text-[var(--poet-muted)]"
                            >
                                {{ group.about }}
                            </p>
                        </div>

                        <div
                            class="flex shrink-0 flex-wrap gap-2 sm:pb-1"
                        >
                            <button
                                v-if="isAdmin"
                                type="button"
                                @click="
                                    showInviteUserModal = true
                                "
                                class="inline-flex items-center gap-2 rounded-full border border-[var(--poet-border)] px-3 py-2 text-sm font-medium text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                            >
                                <UserPlusIcon
                                    class="h-4 w-4"
                                />
                                Invite
                            </button>

                            <Link
                                v-else-if="!authUser"
                                :href="route('login')"
                                class="rounded-full bg-[var(--poet-accent)] px-4 py-2 text-sm font-medium text-white"
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
                                class="rounded-full bg-[var(--poet-accent)] px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
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
                                class="rounded-full bg-[var(--poet-accent)] px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            >
                                {{
                                    joinForm.processing
                                        ? 'Sending...'
                                        : 'Request to join'
                                }}
                            </button>

                            <span
                                v-else-if="
                                    group.status ===
                                    'pending'
                                "
                                class="rounded-full bg-amber-500/10 px-3 py-2 text-sm font-medium text-amber-500"
                            >
                                Membership pending
                            </span>
                        </div>
                    </div>
                </section>

                <div
                    v-if="
                        coverForm.errors.cover ||
                        thumbnailForm.errors.thumbnail
                    "
                    class="mt-3 rounded-xl bg-red-500 px-4 py-3 text-sm text-white"
                >
                    {{
                        coverForm.errors.cover ||
                        thumbnailForm.errors.thumbnail
                    }}
                </div>

                <section
                    class="mt-4 overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)]"
                >
                    <TabGroup>
                        <TabList
                            class="scrollbar-hidden flex overflow-x-auto border-b border-[var(--poet-border)] px-2"
                        >
                            <Tab
                                v-for="tab in [
                                    'Posts',
                                    'Members',
                                    'Photos',
                                    'About'
                                ]"
                                :key="tab"
                                v-slot="{ selected }"
                                as="template"
                            >
                                <button
                                    :class="[
                                        'relative shrink-0 px-4 py-3 text-sm font-medium transition',
                                        selected
                                            ? 'text-[var(--poet-text)]'
                                            : 'text-[var(--poet-muted)] hover:text-[var(--poet-text)]'
                                    ]"
                                >
                                    {{ tab }}

                                    <span
                                        v-if="selected"
                                        class="absolute inset-x-4 bottom-0 h-0.5 rounded-full bg-[var(--poet-accent)]"
                                    />
                                </button>
                            </Tab>
                        </TabList>

                        <TabPanels>
                            <TabPanel class="p-3 sm:p-4">
                                <template v-if="posts">
                                    <div
                                        v-if="isApprovedMember"
                                        class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] px-4 py-3"
                                    >
                                        <div>
                                            <div
                                                class="text-sm font-semibold text-[var(--poet-text)]"
                                            >
                                                Write in {{ group.name }}
                                            </div>

                                            <div
                                                class="mt-0.5 text-xs text-[var(--poet-muted)]"
                                            >
                                                Share something with this group.
                                            </div>
                                        </div>

                                        <Dropdown
                                            align="right"
                                            width="48"
                                            content-classes="py-1 bg-[var(--poet-surface)]"
                                        >
                                            <template #trigger>
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-1.5 rounded-full bg-[var(--poet-accent)] px-3 py-2 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:shadow"
                                                >
                                                    Write
                                                    <ChevronDownIcon
                                                        class="h-4 w-4"
                                                    />
                                                </button>
                                            </template>

                                            <template #content>
                                                <button
                                                    type="button"
                                                    @click="
                                                        showCreatePostModal = true
                                                    "
                                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                                                >
                                                    <DocumentTextIcon
                                                        class="h-5 w-5 text-[var(--poet-muted)]"
                                                    />

                                                    <span>
                                                        Post
                                                    </span>
                                                </button>

                                                <Link
                                                    :href="
                                                        route(
                                                            'poem.write',
                                                            {
                                                                group:
                                                                    group.id
                                                            }
                                                        )
                                                    "
                                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                                                >
                                                    <PencilSquareIcon
                                                        class="h-5 w-5 text-[var(--poet-accent)]"
                                                    />

                                                    <span>
                                                        Poem
                                                    </span>
                                                </Link>
                                            </template>
                                        </Dropdown>
                                    </div>

                                    <PostList
                                        :posts="posts"
                                    />
                                </template>

                                <div
                                    v-else
                                    class="py-10 text-center text-sm text-[var(--poet-muted)]"
                                >
                                    Only approved group members can view group posts.
                                </div>
                            </TabPanel>

                            <TabPanel class="p-3 sm:p-4">
                                <div
                                    v-if="
                                        isAdmin &&
                                        requests.length
                                    "
                                    class="mb-6"
                                >
                                    <div
                                        class="mb-3 flex items-center justify-between gap-3"
                                    >
                                        <div>
                                            <h3
                                                class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                                            >
                                                Pending requests
                                            </h3>

                                            <p
                                                class="text-xs text-[var(--poet-muted)]"
                                            >
                                                {{
                                                    requests.length
                                                }}
                                                waiting for review
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="grid gap-2 sm:grid-cols-2"
                                    >
                                        <UserListItem
                                            v-for="user in requests"
                                            :key="user.id"
                                            :user="user"
                                            card-mode
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

                                <div
                                    class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                                >
                                    <div>
                                        <h3
                                            class="font-serif text-lg font-semibold text-[var(--poet-text)]"
                                        >
                                            Members
                                        </h3>

                                        <p
                                            class="text-xs text-[var(--poet-muted)]"
                                        >
                                            {{ memberCount }}
                                            member{{ memberCount === 1 ? '' : 's' }}
                                        </p>
                                    </div>

                                    <div
                                        class="relative w-full sm:w-72"
                                    >
                                        <MagnifyingGlassIcon
                                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--poet-muted)]"
                                        />

                                        <input
                                            v-model="memberSearch"
                                            type="search"
                                            placeholder="Search members..."
                                            class="w-full rounded-full border border-[var(--poet-border)] bg-[var(--poet-bg)] py-2 pl-9 pr-4 text-sm text-[var(--poet-text)] placeholder:text-[var(--poet-muted)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                                        />
                                    </div>
                                </div>

                                <div
                                    v-if="filteredUsers.length"
                                    class="grid gap-2 sm:grid-cols-2"
                                >
                                    <UserListItem
                                        v-for="user in filteredUsers"
                                        :key="user.id"
                                        :user="user"
                                        card-mode
                                        :show-role-control="
                                            isAdmin
                                        "
                                        :is-owner="
                                            user.id ===
                                            group.user_id
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

                                <div
                                    v-else
                                    class="rounded-xl border border-dashed border-[var(--poet-border)] py-10 text-center text-sm text-[var(--poet-muted)]"
                                >
                                    No members match that search.
                                </div>
                            </TabPanel>

                            <TabPanel class="p-3 sm:p-4">
                                <TabPhotos
                                    v-if="isApprovedMember"
                                    :photos="photos"
                                />

                                <div
                                    v-else
                                    class="py-10 text-center text-sm text-[var(--poet-muted)]"
                                >
                                    Join the group to view photos.
                                </div>
                            </TabPanel>

                            <TabPanel class="p-4 sm:p-5">
                                <div
                                    v-if="
                                        !editingGroupSettings
                                    "
                                >
                                    <div
                                        class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                                    >
                                        <div class="max-w-2xl">
                                            <div
                                                class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--poet-muted)]"
                                            >
                                                About
                                            </div>

                                            <h3
                                                class="mt-1 font-serif text-xl font-semibold text-[var(--poet-text)]"
                                            >
                                                About this group
                                            </h3>

                                            <p
                                                class="mt-3 whitespace-pre-wrap text-sm leading-6 text-[var(--poet-muted)]"
                                            >
                                                {{
                                                    group.about ||
                                                    'No description has been added yet.'
                                                }}
                                            </p>
                                        </div>

                                        <button
                                            v-if="isAdmin"
                                            type="button"
                                            @click="
                                                editingGroupSettings = true
                                            "
                                            class="shrink-0 rounded-full border border-[var(--poet-border)] px-4 py-2 text-sm font-medium text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                                        >
                                            Edit group
                                        </button>
                                    </div>

                                    <div
                                        class="mt-6 grid gap-3 sm:grid-cols-2"
                                    >
                                        <div
                                            class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] p-4"
                                        >
                                            <div
                                                class="text-xs uppercase tracking-wide text-[var(--poet-muted)]"
                                            >
                                                Membership
                                            </div>

                                            <div
                                                class="mt-1 font-medium text-[var(--poet-text)]"
                                            >
                                                {{
                                                    group.auto_approval
                                                        ? 'Automatically approved'
                                                        : 'Administrator approval required'
                                                }}
                                            </div>
                                        </div>

                                        <div
                                            class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] p-4"
                                        >
                                            <div
                                                class="text-xs uppercase tracking-wide text-[var(--poet-muted)]"
                                            >
                                                Members
                                            </div>

                                            <div
                                                class="mt-1 font-medium text-[var(--poet-text)]"
                                            >
                                                {{ memberCount }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form
                                    v-else-if="isAdmin"
                                    @submit.prevent="updateGroup"
                                    class="space-y-5"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <div>
                                            <div
                                                class="text-xs font-semibold uppercase tracking-[0.16em] text-[var(--poet-muted)]"
                                            >
                                                Settings
                                            </div>

                                            <h3
                                                class="mt-1 font-serif text-xl font-semibold text-[var(--poet-text)]"
                                            >
                                                Edit group
                                            </h3>
                                        </div>

                                        <button
                                            type="button"
                                            @click="cancelGroupEdit"
                                            class="rounded-full border border-[var(--poet-border)] px-3 py-2 text-sm text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                                        >
                                            Cancel
                                        </button>
                                    </div>

                                    <div>
                                        <label
                                            for="group-name"
                                            class="block text-sm font-medium text-[var(--poet-text)]"
                                        >
                                            Group name
                                        </label>

                                        <input
                                            id="group-name"
                                            v-model="groupSettingsForm.name"
                                            type="text"
                                            maxlength="255"
                                            class="mt-2 block w-full rounded-xl border border-[var(--poet-border)] bg-[var(--poet-bg)] text-[var(--poet-text)] placeholder:text-[var(--poet-muted)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                                        />

                                        <p
                                            v-if="
                                                groupSettingsForm.errors.name
                                            "
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{
                                                groupSettingsForm.errors.name
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] p-4"
                                    >
                                        <label
                                            class="flex items-center gap-2"
                                        >
                                            <input
                                                v-model="
                                                    groupSettingsForm.auto_approval
                                                "
                                                type="checkbox"
                                                class="rounded border-[var(--poet-border)] text-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                                            />

                                            <span
                                                class="text-sm font-medium text-[var(--poet-text)]"
                                            >
                                                Automatically approve new members
                                            </span>
                                        </label>

                                        <p
                                            class="mt-1 text-xs text-[var(--poet-muted)]"
                                        >
                                            When disabled, new members must be approved by a group administrator.
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            for="group-about"
                                            class="block text-sm font-medium text-[var(--poet-text)]"
                                        >
                                            About group
                                        </label>

                                        <textarea
                                            id="group-about"
                                            v-model="groupSettingsForm.about"
                                            rows="6"
                                            maxlength="5000"
                                            class="mt-2 block w-full resize-y rounded-xl border border-[var(--poet-border)] bg-[var(--poet-bg)] text-[var(--poet-text)] placeholder:text-[var(--poet-muted)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                                        />

                                        <p
                                            v-if="
                                                groupSettingsForm.errors.about
                                            "
                                            class="mt-1 text-sm text-red-500"
                                        >
                                            {{
                                                groupSettingsForm.errors.about
                                            }}
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="
                                            groupSettingsForm.processing
                                        "
                                        class="rounded-full bg-[var(--poet-accent)] px-4 py-2.5 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:shadow disabled:pointer-events-none disabled:opacity-50"
                                    >
                                        {{
                                            groupSettingsForm.processing
                                                ? 'Saving...'
                                                : 'Save changes'
                                        }}
                                    </button>
                                </form>
                            </TabPanel>
                        </TabPanels>
                    </TabGroup>
                </section>
            </div>
        </div>

        <PostModal
            v-if="authUser"
            :post="newGroupPost"
            :group="group"
            :allow-poem-mode="false"
            v-model="showCreatePostModal"
        />
    </AuthenticatedLayout>

    <InviteUserModal
        v-model="showInviteUserModal"
        :group="group"
    />
</template>
