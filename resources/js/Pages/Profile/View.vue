<template>
    <AuthenticatedLayout>
        <div
            class="h-full overflow-y-auto bg-[var(--poet-bg)]"
        >
            <div
                class="mx-auto w-full max-w-5xl px-3 pb-6 sm:px-4 sm:pb-10 lg:px-6"
            >
                <div
                    v-show="showNotification && success"
                    class="my-3 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-medium text-white"
                >
                    {{ success }}
                </div>

                <div
                    v-if="errors.cover"
                    class="my-3 rounded-xl bg-red-500 px-4 py-3 text-sm font-medium text-white"
                >
                    {{ errors.cover }}
                </div>

                <section
                    class="mt-3 overflow-hidden rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface)] sm:mt-5 sm:rounded-2xl"
                >
                    <div
                        class="group relative h-36 bg-[var(--poet-surface-soft)] sm:h-56"
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
                                class="relative flex cursor-pointer items-center gap-2 rounded-full bg-black/55 px-3 py-2 text-xs font-medium text-white opacity-100 backdrop-blur transition sm:opacity-0 sm:group-hover:opacity-100"
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
                        class="relative flex flex-col gap-3 px-4 pb-4 sm:flex-row sm:items-end sm:gap-4 sm:px-7 sm:pb-5"
                    >
                        <div
                            class="group/avatar relative -mt-10 h-20 w-20 shrink-0 rounded-full ring-4 ring-[var(--poet-surface)] sm:-mt-14 sm:h-28 sm:w-28"
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
                                class="absolute inset-0 flex cursor-pointer items-center justify-center rounded-full bg-black/50 text-white opacity-100 transition sm:opacity-0 sm:group-hover/avatar:opacity-100"
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
                                class="truncate font-serif text-xl font-semibold text-[var(--poet-text)] sm:text-2xl"
                            >
                                {{ user.name }}
                            </h1>

                            <div
                                class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-[var(--poet-muted)] sm:gap-x-3 sm:text-sm"
                            >
                                <span>
                                    @{{ user.username }}
                                </span>

                                <span>
                                    {{ followerCount }}
                                    follower{{ followerCount === 1 ? '' : 's' }}
                                </span>

                                <span>
                                    {{ followingCount }}
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

                <section class="mt-3 sm:mt-4">
                    <div
                        class="flex flex-col gap-3 border-b border-[var(--poet-border)] pb-3 md:flex-row md:items-end md:justify-between"
                    >
                        <nav
                            class="scrollbar-hidden -mx-1 flex min-w-0 snap-x overflow-x-auto px-1"
                            aria-label="Profile sections"
                        >
                            <button
                                v-for="tab in visibleTabs"
                                :key="tab.value"
                                type="button"
                                @click="selectTab(tab.value)"
                                :class="[
                                    'relative shrink-0 snap-start px-3 py-2.5 text-sm font-medium transition',
                                    selectedTab === tab.value
                                        ? 'text-[var(--poet-text)]'
                                        : 'text-[var(--poet-muted)] hover:text-[var(--poet-text)]'
                                ]"
                            >
                                {{ tab.label }}

                                <span
                                    v-if="
                                        selectedTab ===
                                        tab.value
                                    "
                                    class="absolute inset-x-3 bottom-0 h-0.5 rounded-full bg-[var(--poet-accent)]"
                                />
                            </button>
                        </nav>

                        <form
                            v-if="isPeopleTab"
                            class="grid w-full grid-cols-[minmax(0,1fr)_auto] items-center gap-2 md:flex md:w-auto"
                            @submit.prevent="searchPeople"
                        >
                            <div class="relative min-w-0 md:w-72">
                                <MagnifyingGlassIcon
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--poet-muted)]"
                                />

                                <input
                                    v-model="peopleSearchInput"
                                    type="search"
                                    placeholder="Search name or @username"
                                    class="w-full rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] py-2 pl-9 pr-4 text-sm text-[var(--poet-text)] placeholder:text-[var(--poet-muted)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
                                />
                            </div>

                            <button
                                type="submit"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--poet-accent)] text-white transition hover:-translate-y-0.5 hover:shadow md:h-auto md:w-auto md:px-4 md:py-2 md:text-sm md:font-medium"
                                aria-label="Search people"
                            >
                                <MagnifyingGlassIcon class="h-4 w-4 md:hidden" />
                                <span class="hidden md:inline">Search</span>
                            </button>

                            <button
                                v-if="peopleSearchInput"
                                type="button"
                                @click="clearPeopleSearch"
                                class="col-span-2 justify-self-start rounded-full border border-[var(--poet-border)] px-3 py-1.5 text-xs text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)] md:col-span-1 md:px-3 md:py-2 md:text-sm"
                            >
                                Clear
                            </button>
                        </form>
                    </div>

                    <div
                        v-if="isPeopleTab"
                        class="flex flex-wrap items-center justify-between gap-2 py-3 text-xs text-[var(--poet-muted)]"
                    >
                        <span>
                            {{
                                activePeopleMeta?.total
                                ?? 0
                            }}
                            {{
                                selectedTab === 'followers'
                                    ? 'followers'
                                    : 'following'
                            }}
                            <template v-if="peopleSearch">
                                matching “{{ peopleSearch }}”
                            </template>
                        </span>

                        <span
                            v-if="
                                activePeopleMeta?.total
                            "
                        >
                            Showing
                            {{
                                activePeopleMeta.from
                            }}–{{
                                activePeopleMeta.to
                            }}
                            of
                            {{
                                activePeopleMeta.total
                            }}
                        </span>
                    </div>

                    <div v-if="selectedTab === 'posts'">
                        <template v-if="posts">
                            <div
                                v-if="posts.meta?.total"
                                class="flex items-center justify-between gap-2 py-2.5 text-[11px] text-[var(--poet-muted)] sm:py-3 sm:text-xs"
                            >
                                <span>
                                    {{ posts.meta.total }}
                                    publication{{ posts.meta.total === 1 ? '' : 's' }}
                                </span>

                                <span>
                                    Showing
                                    {{ posts.meta.from }}–{{ posts.meta.to }}
                                    of {{ posts.meta.total }}
                                </span>
                            </div>

                            <ProfilePublicationGrid
                                v-if="posts.data?.length"
                                :posts="posts.data"
                                :pinned-post-id="
                                    user.pinned_post_id
                                "
                            />

                            <div
                                v-else
                                class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                            >
                                No publications yet.
                            </div>

                            <div class="mt-5">
                                <CompactPaginator
                                    :meta="posts.meta"
                                    label="Publication pages"
                                    @page="goToPostPage"
                                />
                            </div>
                        </template>

                        <div
                            v-else
                            class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                        >
                            Log in to view publications.
                        </div>
                    </div>

                    <div v-else-if="selectedTab === 'followers'">
                        <div
                            v-if="followers.data?.length"
                            class="grid grid-cols-2 gap-2 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <UserListItem
                                v-for="follower in followers.data"
                                :key="follower.id"
                                :user="follower"
                                card-mode
                            />
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                        >
                            {{
                                peopleSearch
                                    ? 'No followers match that search.'
                                    : 'No followers yet.'
                            }}
                        </div>

                        <div class="mt-5">
                            <CompactPaginator
                                :meta="followers.meta"
                                label="Follower pages"
                                @page="goToPeoplePage"
                            />
                        </div>
                    </div>

                    <div v-else-if="selectedTab === 'following'">
                        <div
                            v-if="followings.data?.length"
                            class="grid grid-cols-2 gap-2 sm:grid-cols-2 lg:grid-cols-4"
                        >
                            <UserListItem
                                v-for="following in followings.data"
                                :key="following.id"
                                :user="following"
                                card-mode
                            />
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] py-12 text-center text-sm text-[var(--poet-muted)]"
                        >
                            {{
                                peopleSearch
                                    ? 'No followed writers match that search.'
                                    : 'Not following anyone yet.'
                            }}
                        </div>

                        <div class="mt-5">
                            <CompactPaginator
                                :meta="followings.meta"
                                label="Following pages"
                                @page="goToPeoplePage"
                            />
                        </div>
                    </div>

                    <div
                        v-else-if="selectedTab === 'photos'"
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

                    <div
                        v-else-if="
                            selectedTab ===
                                'my_profile' &&
                            isMyProfile
                        "
                        class="border-y border-[var(--poet-border)] bg-[var(--poet-surface)] px-3 py-4 sm:rounded-2xl sm:border sm:p-6"
                    >
                        <Edit
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import {
    computed,
    ref,
    watch
} from 'vue';

import {
    router,
    useForm,
    usePage
} from '@inertiajs/vue3';

import {
    CameraIcon,
    CheckCircleIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

import {
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import CompactPaginator
    from '@/Components/app/CompactPaginator.vue';

import ProfilePublicationGrid
    from '@/Components/app/ProfilePublicationGrid.vue';

import UserListItem
    from '@/Components/app/UserListItem.vue';

import TabPhotos
    from '@/Pages/Profile/TabPhotos.vue';

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

    followingCount: {
        type: Number,
        default: 0,
    },

    profileTab: {
        type: String,
        default: 'posts'
    },

    peopleSearch: {
        type: String,
        default: ''
    },

    posts: {
        type: Object,
        default: null
    },

    followers: {
        type: Object,
        default: () => ({
            data: [],
            meta: null
        })
    },

    followings: {
        type: Object,
        default: () => ({
            data: [],
            meta: null
        })
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

const allTabs = [
    {
        value: 'posts',
        label: 'Posts'
    },
    {
        value: 'followers',
        label: 'Followers'
    },
    {
        value: 'following',
        label: 'Following'
    },
    {
        value: 'photos',
        label: 'Photos'
    },
    {
        value: 'my_profile',
        label: 'My Profile',
        ownerOnly: true
    }
];

const visibleTabs =
    computed(() =>
        allTabs.filter(
            tab =>
                !tab.ownerOnly ||
                isMyProfile.value
        )
    );

const selectedTab =
    ref(
        visibleTabs.value.some(
            tab =>
                tab.value ===
                props.profileTab
        )
            ? props.profileTab
            : 'posts'
    );

const peopleSearchInput =
    ref(props.peopleSearch);

watch(
    () => props.profileTab,
    value => {
        if (
            visibleTabs.value.some(
                tab =>
                    tab.value === value
            )
        ) {
            selectedTab.value = value;
        }
    }
);

watch(
    () => props.peopleSearch,
    value => {
        peopleSearchInput.value =
            value ?? '';
    }
);

const isPeopleTab =
    computed(() =>
        [
            'followers',
            'following'
        ].includes(
            selectedTab.value
        )
    );

const activePeopleMeta =
    computed(() =>
        selectedTab.value ===
            'followers'
            ? props.followers.meta
            : selectedTab.value ===
                'following'
                ? props.followings.meta
                : null
    );

function selectTab(tab) {
    selectedTab.value = tab;
}

function profileQuery(extra = {}) {
    const query = {
        tab: selectedTab.value,
        ...extra
    };

    const search =
        peopleSearchInput.value
            .trim();

    if (search) {
        query.people_search = search;
    }

    return query;
}

function searchPeople() {
    const search =
        peopleSearchInput.value
            .trim();

    router.get(
        route(
            'profile',
            {
                username:
                    props.user.username
            }
        ),
        {
            tab: selectedTab.value,
            ...(search
                ? {
                    people_search:
                        search
                }
                : {})
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true
        }
    );
}

function clearPeopleSearch() {
    peopleSearchInput.value = '';

    router.get(
        route(
            'profile',
            {
                username:
                    props.user.username
            }
        ),
        {
            tab: selectedTab.value
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true
        }
    );
}

function goToPostPage(page) {
    router.get(
        route(
            'profile',
            {
                username:
                    props.user.username
            }
        ),
        {
            tab: 'posts',
            posts_page: page
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true
        }
    );
}

function goToPeoplePage(page) {
    const pageKey =
        selectedTab.value ===
            'followers'
            ? 'followers_page'
            : 'following_page';

    router.get(
        route(
            'profile',
            {
                username:
                    props.user.username
            }
        ),
        profileQuery({
            [pageKey]: page
        }),
        {
            preserveScroll: true,
            preserveState: true,
            replace: true
        }
    );
}

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
