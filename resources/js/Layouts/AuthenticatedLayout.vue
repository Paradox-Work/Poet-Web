<script setup>
import {
    onMounted,
    ref
} from 'vue';

import {
    ChevronDownIcon,
    DocumentTextIcon,
    MoonIcon,
    PencilSquareIcon,
    SunIcon
} from '@heroicons/vue/24/outline';

import ApplicationLogo 
    from '@/Components/ApplicationLogo.vue';

import Dropdown 
    from '@/Components/Dropdown.vue';
    
import DropdownLink 
    from '@/Components/DropdownLink.vue';

import NavLink 
    from '@/Components/NavLink.vue';

import ResponsiveNavLink 
    from '@/Components/ResponsiveNavLink.vue';

import TextInput
    from '@/Components/TextInput.vue';

import PostModal
    from '@/Components/app/PostModal.vue';

import NotificationDropdown
    from '@/Components/app/NotificationDropdown.vue';

import {
    Link,
    router,
    usePage
} from '@inertiajs/vue3';




const showingNavigationDropdown = ref(false);

const isDark = ref(false);

const authUser = usePage().props.auth.user;

const showCreatePostModal = ref(false);

const newPost = {
    id: null,
    type: 'post',
    body: '',
    user: authUser,
    group: null,
    updated_at: null
};

function openCreatePost() {
    showCreatePostModal.value = true;
}

function applyTheme(value) {
    isDark.value = value;

    document.documentElement
        .classList
        .toggle('dark', value);

    localStorage.setItem(
        'theme',
        value
            ? 'dark'
            : 'light'
    );
}

function toggleTheme() {
    applyTheme(
        !isDark.value
    );
}

onMounted(() => {
    isDark.value =
        document.documentElement
            .classList
            .contains('dark');
});

const keywords =
    ref(
        usePage().props.search ?? ''
    );


function search() {

    const value =
        keywords.value?.trim();

    if (!value) {
        return;
    }

    router.get(
        `/search/${encodeURIComponent(
            value
        )}`
    );
}

</script>

<template>
    <div>
        <div class="h-screen flex flex-col bg-[var(--poet-bg)] text-[var(--poet-text)]">
            <nav
                class="relative z-[100] overflow-visible border-b border-[var(--poet-border)] bg-[var(--poet-nav)]/95 backdrop-blur-md"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link
                                    :href="route('dashboard')"
                                    class="group flex items-center gap-2.5"
                                >
                                    <ApplicationLogo
                                        class="block h-10 w-10 text-[var(--poet-text)] transition duration-200 group-hover:-rotate-2 group-hover:scale-105"
                                    />

                                    <span
                                        class="hidden lg:block"
                                    >
                                        <span
                                            class="block font-serif text-xl font-semibold tracking-tight text-[var(--poet-text)]"
                                        >
                                            Poet-Web
                                        </span>

                                        <span
                                            class="hidden text-[8px] font-semibold uppercase tracking-[0.22em] text-[var(--poet-gold)] xl:block"
                                        >
                                            Read · Write · Belong
                                        </span>
                                    </span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-7 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Home
                                </NavLink>

                                <NavLink
                                    :href="route('group.index')"
                                    :active="
                                        route().current('group.index') ||
                                        route().current('group.profile')
                                    "
                                >
                                    Groups
                                </NavLink>

                                <NavLink
                                    :href="route('draft.index')"
                                    :active="route().current('draft.index')"
                                >
                                    Drafts
                                </NavLink>

                                <div class="flex items-center">
                                    <Dropdown
                                        v-if="authUser"
                                        align="left"
                                        width="48"
                                        content-classes="py-1 bg-[var(--poet-surface)]"
                                    >
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="poet-focus inline-flex items-center gap-1.5 rounded-full bg-[var(--poet-accent)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[var(--poet-accent-strong)] hover:shadow-md"
                                            >
                                                Create

                                                <ChevronDownIcon
                                                    class="h-4 w-4"
                                                />
                                            </button>
                                        </template>

                                        <template #content>
                                            <button
                                                type="button"
                                                @click="openCreatePost"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                                            >
                                                <DocumentTextIcon
                                                    class="h-5 w-5 text-[var(--poet-muted)]"
                                                />

                                                <div>
                                                    <div class="font-medium">
                                                        Post
                                                    </div>

                                                    <div class="text-xs text-[var(--poet-muted)]">
                                                        Share a quick thought
                                                    </div>
                                                </div>
                                            </button>

                                            <DropdownLink
                                                :href="route('poem.write')"
                                            >
                                                <span class="flex items-center gap-3">
                                                    <PencilSquareIcon
                                                        class="h-5 w-5 text-[var(--poet-accent)]"
                                                    />

                                                    <span>
                                                        <span class="block font-medium text-[var(--poet-text)]">
                                                            Poem
                                                        </span>

                                                        <span class="block text-xs text-[var(--poet-muted)]">
                                                            Open the writing studio
                                                        </span>
                                                    </span>
                                                </span>
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">

                            <div
                                class="hidden sm:flex flex-1 max-w-md mx-4"
                            >
                                <TextInput
                                    v-model="keywords"
                                    placeholder="Search poems, people, or groups..."
                                    class="w-full !rounded-full"
                                    @keyup.enter="search"
                                />
                            </div>

                            <NotificationDropdown
                                v-if="authUser"
                                class="mr-1"
                            />

                            <button
                                type="button"
                                @click="toggleTheme"
                                class="poet-focus mr-2 inline-flex h-9 w-9 items-center justify-center rounded-full text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-gold)]"
                                :aria-label="
                                    isDark
                                        ? 'Switch to light mode'
                                        : 'Switch to dark mode'
                                "
                            >
                                <SunIcon
                                    v-if="isDark"
                                    class="h-5 w-5"
                                />

                                <MoonIcon
                                    v-else
                                    class="h-5 w-5"
                                />
                            </button>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown v-if="authUser" align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="poet-focus inline-flex items-center rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] px-3 py-2 text-sm font-medium leading-4 text-[var(--poet-muted)] transition hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                                            >
                                                {{ authUser.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile', {username: authUser.username})"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                                <div v-else class="hidden sm:flex sm:items-center">
                                    <Link
                                        :href="route('login')"
                                        class="text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        :href="route('register')"
                                        class="ms-4 text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        Register
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile notifications + hamburger -->
                        <div
                            class="-me-2 flex items-center gap-1 sm:hidden"
                        >
                            <NotificationDropdown
                                v-if="authUser"
                            />
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="poet-focus inline-flex items-center justify-center rounded-full p-2 text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <button
                            type="button"
                            @click="toggleTheme"
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                        >
                            <SunIcon
                                v-if="isDark"
                                class="h-5 w-5"
                            />

                            <MoonIcon
                                v-else
                                class="h-5 w-5"
                            />

                            {{
                                isDark
                                    ? 'Light mode'
                                    : 'Dark mode'
                            }}
                        </button>

                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Home
                        </ResponsiveNavLink>

                        <button
                            v-if="authUser"
                            type="button"
                            @click="
                                openCreatePost();
                                showingNavigationDropdown = false;
                            "
                            class="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-[var(--poet-muted)] transition hover:bg-[var(--poet-surface-soft)] hover:text-[var(--poet-text)]"
                        >
                            <DocumentTextIcon
                                class="h-5 w-5"
                            />
                            Create post
                        </button>

                        <ResponsiveNavLink
                            v-if="authUser"
                            :href="route('poem.write')"
                        >
                            Write poem
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('group.index')"
                            :active="
                                route().current('group.index') ||
                                route().current('group.profile')
                            "
                        >
                            Groups
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('draft.index')"
                            :active="route().current('draft.index')"
                        >
                            Drafts
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        v-if="authUser"
                        class="border-t border-[var(--poet-border)] pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-[var(--poet-text)]"
                            >
                                {{ authUser.name }}
                            </div>
                            <div class="text-sm font-medium text-[var(--poet-muted)]">
                                {{ authUser.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile', {username: authUser.username})">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="border-b border-[var(--poet-border)] bg-[var(--poet-surface)] shadow-sm"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 min-h-0">
                <slot />
            </main>

            <PostModal
                v-if="authUser"
                :post="newPost"
                :allow-poem-mode="false"
                v-model="showCreatePostModal"
            />
        </div>
    </div>
</template>
