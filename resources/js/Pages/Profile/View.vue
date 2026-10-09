<template>
    <AuthenticatedLayout>
        <div
            class="h-full overflow-y-auto bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto w-full max-w-5xl px-0 pb-10 sm:px-4 lg:px-6"
            >
                <div
                    v-show="showNotification && success"
                    class="mx-4 my-3 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-medium text-white sm:mx-0"
                >
                    {{ success }}
                </div>

                <div
                    v-if="errors.cover"
                    class="mx-4 my-3 rounded-xl bg-red-500 px-4 py-3 text-sm font-medium text-white sm:mx-0"
                >
                    {{ errors.cover }}
                </div>

                <section
                    class="overflow-hidden border-b border-[var(--poet-border)] bg-[var(--poet-surface)] sm:mt-5 sm:rounded-2xl sm:border"
                >
                    <div
                        class="group relative h-44 bg-[var(--poet-surface-soft)] sm:h-56"
                    >
                        <img
                            :src="
                                coverImageSrc ||
                                user.cover_url ||
                                '/img/default_cover.jpg'
                            "
                            class="h-full w-full object-cover"
                            alt="Profile cover"
                        />

                        <div
                            v-if="isMyProfile"
                            class="absolute right-3 top-3"
                        >
                            <label
                                v-if="!coverImageSrc"
                                class="relative flex cursor-pointer items-center gap-2 rounded-full bg-black/55 px-3 py-2 text-xs font-medium text-white opacity-0 backdrop-blur transition group-hover:opacity-100"
                            >
                                <CameraIcon class="h-4 w-4" />
                                Change cover

                                <input
                                    type="file"
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
                                    @click="resetCoverImage"
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-white transition hover:bg-white/15"
                                    aria-label="Cancel cover change"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                </button>

                                <button
                                    type="button"
                                    @click="submitCoverImage"
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-white transition hover:bg-white/15"
                                    aria-label="Save cover image"
                                >
                                    <CheckCircleIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="relative flex flex-col gap-4 px-5 pb-5 sm:flex-row sm:items-end sm:px-7"
                    >
                        <div
                            class="group/avatar relative -mt-12 h-24 w-24 shrink-0 rounded-full ring-4 ring-[var(--poet-surface)] sm:-mt-14 sm:h-28 sm:w-28"
                        >
                            <img
                                :src="
                                    avatarImageSrc ||
                                    user.avatar_url ||
                                    '/img/default_avatar.svg'
                                "
                                :alt="user.name"
                                class="h-full w-full rounded-full object-cover"
                            />

                            <label
                                v-if="
                                    isMyProfile &&
                                    !avatarImageSrc
                                "
                                class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition group-hover/avatar:opacity-100"
                            >
                                <CameraIcon class="h-7 w-7" />

                                <input
                                    type="file"
                                    class="absolute inset-0 cursor-pointer opacity-0"
                                    @change="onAvatarChange"
                                />
                            </label>

                            <div
                                v-if="
                                    isMyProfile &&
                                    avatarImageSrc
                                "
                                class="absolute -right-1 top-1 flex flex-col gap-1"
                            >
                                <button
                                    type="button"
                                    @click="resetAvatarImage"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500 text-white shadow"
                                    aria-label="Cancel avatar change"
                                >
                                    <XMarkIcon class="h-4 w-4" />
                                </button>

                                <button
                                    type="button"
                                    @click="submitAvatarImage"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow"
                                    aria-label="Save avatar"
                                >
                                    <CheckCircleIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="min-w-0 flex-1 sm:pb-1"
                        >
                            <h1
                                class="truncate font-serif text-2xl font-semibold text-[var(--poet-text)]"
                            >
                                {{ user.name }}
                            </h1>

                            <div
                                class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-[var(--poet-muted)]"
                            >
                                <span>
                                    @{{ user.username }}
                                </span>

                                <span>
                                    {{ followerCount }}
                                    follower{{ followerCount === 1 ? '' : 's' }}
                                </span>

                                <span>
                                    {{ followings.length }}
                                    following
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="!isMyProfile"
                            class="sm:pb-1"
                        >
                            <PrimaryButton
                                v-if="!isCurrentUserFollower"
                                @click="followUser"
                            >
                                Follow
                            </PrimaryButton>

                            <DangerButton
                                v-else
                                @click="followUser"
                            >
                                Unfollow
                            </DangerButton>
                        </div>
                    </div>
                </section>

                <div
                    class="border-b border-[var(--poet-border)] bg-[var(--poet-bg)] sm:mt-4"
                >
                    <TabGroup>
                        <TabList
                            class="scrollbar-hidden flex overflow-x-auto px-3 sm:justify-center sm:px-0"
                        >
                            <Tab
                                as="template"
                                v-slot="{ selected }"
                            >
                                <TabItem
                                    text="Posts"
                                    :selected="selected"
                                />
                            </Tab>

                            <Tab
                                as="template"
                                v-slot="{ selected }"
                            >
                                <TabItem
                                    text="Followers"
                                    :selected="selected"
                                />
                            </Tab>

                            <Tab
                                as="template"
                                v-slot="{ selected }"
                            >
                                <TabItem
                                    text="Following"
                                    :selected="selected"
                                />
                            </Tab>

                            <Tab
                                as="template"
                                v-slot="{ selected }"
                            >
                                <TabItem
                                    text="Photos"
                                    :selected="selected"
                                />
                            </Tab>

                            <Tab
                                v-if="isMyProfile"
                                as="template"
                                v-slot="{ selected }"
                            >
                                <TabItem
                                    text="My Profile"
                                    :selected="selected"
                                />
                            </Tab>
                        </TabList>

                        <TabPanels class="pt-4">
                            <TabPanel class="px-3 sm:px-0">
                                <template v-if="posts">
                                    <PostList
                                        v-if="posts.data?.length"
                                        :posts="posts"
                                    />

                                    <div
                                        v-else
                                        class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                                    >
                                        No posts yet.
                                    </div>
                                </template>

                                <div
                                    v-else
                                    class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                                >
                                    Log in to view posts.
                                </div>
                            </TabPanel>

                            <TabPanel class="px-3 sm:px-0">
                                <div
                                    v-if="followers.length"
                                    class="grid gap-2 sm:grid-cols-2"
                                >
                                    <div
                                        v-for="follower in followers"
                                        :key="follower.id"
                                        class="overflow-hidden rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface)]"
                                    >
                                        <UserListItem
                                            :user="follower"
                                        />
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                                >
                                    No followers yet.
                                </div>
                            </TabPanel>

                            <TabPanel class="px-3 sm:px-0">
                                <div
                                    v-if="followings.length"
                                    class="grid gap-2 sm:grid-cols-2"
                                >
                                    <div
                                        v-for="following in followings"
                                        :key="following.id"
                                        class="overflow-hidden rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface)]"
                                    >
                                        <UserListItem
                                            :user="following"
                                        />
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                                >
                                    Not following anyone yet.
                                </div>
                            </TabPanel>

                            <TabPanel class="px-3 sm:px-0">
                                <div
                                    class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-3"
                                >
                                    <TabPhotos
                                        v-if="photos"
                                        :photos="photos"
                                    />

                                    <div
                                        v-else
                                        class="py-10 text-center text-sm text-[var(--poet-muted)]"
                                    >
                                        Log in to view photos.
                                    </div>
                                </div>
                            </TabPanel>

                            <TabPanel
                                v-if="isMyProfile"
                                class="px-3 sm:px-0"
                            >
                                <div
                                    class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-4 sm:p-6"
                                >
                                    <Edit
                                        :must-verify-email="mustVerifyEmail"
                                        :status="status"
                                    />
                                </div>
                            </TabPanel>
                        </TabPanels>
                    </TabGroup>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import TabPhotos
    from '@/Pages/Profile/TabPhotos.vue';

import PostList
    from '@/Components/app/PostList.vue';

import UserListItem
    from '@/Components/app/UserListItem.vue';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import {
    computed,
    ref
} from 'vue';

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

import {
    useForm,
    usePage
} from '@inertiajs/vue3';

import TabItem
    from './Partials/TabItem.vue';

import Edit
    from './Edit.vue';

import PrimaryButton
    from '@/Components/PrimaryButton.vue';

import DangerButton
    from '@/Components/DangerButton.vue';

const props = defineProps({
    errors: Object,
    mustVerifyEmail: Boolean,
    status: String,
    success: String,

    isCurrentUserFollower: {
        type: Boolean,
        default: false,
    },

    followerCount: {
        type: Number,
        default: 0,
    },

    posts: {
        type: Object,
        default: null
    },

    followers: {
        type: Array,
        default: () => []
    },

    followings: {
        type: Array,
        default: () => []
    },

    photos: {
        type: Array,
        default: null
    },

    user: Object,
});

const imagesForm = useForm({
    avatar: null,
    cover: null,
});

const showNotification = ref(true);
const coverImageSrc = ref('');
const avatarImageSrc = ref('');

const authUser =
    usePage().props.auth.user;

const isMyProfile = computed(() =>
    authUser &&
    authUser.id === props.user.id
);

function onCoverChange(event) {
    imagesForm.cover =
        event.target.files[0];

    if (!imagesForm.cover) {
        return;
    }

    const reader =
        new FileReader();

    reader.onload = () => {
        coverImageSrc.value =
            reader.result;
    };

    reader.readAsDataURL(
        imagesForm.cover
    );
}

function onAvatarChange(event) {
    imagesForm.avatar =
        event.target.files[0];

    if (!imagesForm.avatar) {
        return;
    }

    const reader =
        new FileReader();

    reader.onload = () => {
        avatarImageSrc.value =
            reader.result;
    };

    reader.readAsDataURL(
        imagesForm.avatar
    );
}

function resetAvatarImage() {
    imagesForm.avatar = null;
    avatarImageSrc.value = '';
}

function resetCoverImage() {
    imagesForm.cover = null;
    coverImageSrc.value = '';
}

function submitCoverImage() {
    imagesForm.post(
        route('profile.updateImages'),
        {
            onSuccess: () => {
                resetCoverImage();

                setTimeout(
                    () => {
                        showNotification.value =
                            false;
                    },
                    3000
                );
            },
        }
    );
}

function submitAvatarImage() {
    imagesForm.post(
        route('profile.updateImages'),
        {
            onSuccess: () => {
                resetAvatarImage();

                setTimeout(
                    () => {
                        showNotification.value =
                            false;
                    },
                    3000
                );
            },
        }
    );
}

function followUser() {
    const form =
        useForm({
            follow:
                !props.isCurrentUserFollower
        });

    form.post(
        route(
            'user.follow',
            props.user.id
        ),
        {
            preserveScroll: true
        }
    );
}
</script>
