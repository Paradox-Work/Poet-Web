<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    provide,
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

import { useI18n } from '@/i18n';

import {
    Link,
    router,
    usePage
} from '@inertiajs/vue3';




const { locale, t, setLocale } = useI18n();

const showingNavigationDropdown = ref(false);

const isDark = ref(false);

const page = usePage();
const authUser = computed(() => page.props.auth?.user ?? null);

const showCreatePostModal = ref(false);

// Keep the shared navbar out of the way whenever a create-post composer is open,
// including composers opened from group pages rather than the navbar.
const isPostComposerOpen = ref(false);
const activePostComposers = new Set();

provide('setPostComposerVisibility', (composerId, isOpen) => {
    if (isOpen) {
        activePostComposers.add(composerId);
        showingNavigationDropdown.value = false;
    } else {
        activePostComposers.delete(composerId);
    }

    isPostComposerOpen.value = activePostComposers.size > 0;
});

const newPost = computed(() => ({
    id: null,
    type: 'post',
    body: '',
    user: authUser.value,
    group: null,
    updated_at: null
}));

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

    document.documentElement.lang =
        locale.value;
});

const keywords =
    ref(usePage().props.search ?? '');

const searchHints = computed(() => locale.value === 'lv'
    ? ['Meklēt dzejoļus', 'Atrast autorus', 'Atklāt grupas', 'Meklēt tēmas']
    : ['Find a poem', 'Discover authors', 'Explore groups', 'Search topics']
);
const activeSearchHint = ref(0);
const currentSearchHint = computed(() => searchHints.value[activeSearchHint.value % searchHints.value.length]);
let searchHintTimer;
onMounted(() => {
    searchHintTimer = window.setInterval(() => {
        activeSearchHint.value = (activeSearchHint.value + 1) % searchHints.value.length;
    }, 3400);
});
onBeforeUnmount(() => window.clearInterval(searchHintTimer));


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
                class="relative z-40 overflow-visible border-b border-[var(--poet-border)] bg-[var(--poet-nav)]/95 backdrop-blur-md transition-[margin-top,transform,opacity] duration-200 ease-in-out motion-reduce:transition-none"
                :class="isPostComposerOpen
                    ? '-mt-[65px] -translate-y-full opacity-0 pointer-events-none'
                    : 'mt-0 translate-y-0 opacity-100'"
                :aria-hidden="isPostComposerOpen ? 'true' : undefined"
                :inert="isPostComposerOpen"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 min-w-0 justify-between">
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
                                            {{ t('nav.tagline') }}
                                        </span>
                                    </span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden shrink-0 space-x-5 min-[1380px]:-my-px min-[1380px]:ms-6 min-[1380px]:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    {{ t('nav.home') }}
                                </NavLink>

                                <NavLink
                                    :href="route('group.index')"
                                    :active="
                                        route().current('group.index') ||
                                        route().current('group.profile')
                                    "
                                >
                                    {{ t('nav.groups') }}
                                </NavLink>

                                <NavLink
                                    :href="route('about')"
                                    :active="route().current('about')"
                                >
                                    {{ t('nav.about') }}
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
                                                {{ t('nav.create') }}

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
                                                        {{ locale === 'lv' ? 'Dalies ar īsu domu' : 'Share a quick thought' }}
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
                                                            {{ t('nav.poem') }}
                                                        </span>

                                                        <span class="block text-xs text-[var(--poet-muted)]">
                                                            {{ locale === 'lv' ? 'Atver rakstīšanas studiju' : 'Open the writing studio' }}
                                                        </span>
                                                    </span>
                                                </span>
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="hidden min-[1380px]:ms-6 min-[1380px]:flex min-[1380px]:items-center">

                            <div
                                class="mx-2 hidden w-44 min-w-0 shrink min-[1380px]:flex xl:w-52"
                            >
                                <div class="relative w-full min-w-0">
                                    <TextInput
                                        v-model="keywords"
                                        :placeholder="keywords ? '' : ' '"
                                        :aria-label="t('nav.search')"
                                        class="w-full !rounded-full"
                                        @keyup.enter="search"
                                    />
                                    <span v-if="!keywords" :key="currentSearchHint" class="poet-search-hint pointer-events-none absolute inset-y-0 left-3 right-3 flex items-center overflow-hidden whitespace-nowrap text-sm text-[var(--poet-muted)]">
                                        <span class="poet-search-hint-text">{{ currentSearchHint }}</span>
                                    </span>
                                </div>
                            </div>

                            <NotificationDropdown
                                v-if="authUser"
                                class="mr-1"
                            />

                            <div
                                class="mr-2 flex items-center rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] p-0.5 text-[11px] font-semibold"
                                aria-label="Language"
                            >
                                <button
                                    type="button"
                                    @click="setLocale('lv')"
                                    :class="[
                                        'rounded-full px-2 py-1 transition',
                                        locale === 'lv'
                                            ? 'bg-[var(--poet-accent)] text-white'
                                            : 'text-[var(--poet-muted)]'
                                    ]"
                                >
                                    LV
                                </button>
                                <button
                                    type="button"
                                    @click="setLocale('en')"
                                    :class="[
                                        'rounded-full px-2 py-1 transition',
                                        locale === 'en'
                                            ? 'bg-[var(--poet-accent)] text-white'
                                            : 'text-[var(--poet-muted)]'
                                    ]"
                                >
                                    EN
                                </button>
                            </div>

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
                                            {{ t('nav.profile') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('draft.index')"
                                        >
                                            {{ t('nav.drafts') }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('saved.index')"
                                        >
                                            {{ locale === 'lv' ? 'Saglabātie darbi' : 'Saved publications' }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('messages.show')"
                                        >
                                            {{ locale === 'lv' ? 'Tiešās ziņas' : 'Direct messages' }}
                                        </DropdownLink>
                                        <DropdownLink
                                            v-if="authUser?.is_admin"
                                            :href="route('admin.moderation')"
                                        >
                                            {{ locale === 'lv' ? 'Moderācija' : 'Moderation' }}
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            {{ t('nav.logout') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                                <div v-else class="hidden min-[1380px]:flex min-[1380px]:items-center">
                                    <Link
                                        :href="route('login')"
                                        class="text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        {{ t('nav.login') }}
                                    </Link>
                                    <Link
                                        :href="route('register')"
                                        class="ms-4 text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                    >
                                        {{ t('nav.register') }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile notifications + hamburger -->
                        <div
                            class="-me-2 flex items-center gap-1 min-[1380px]:hidden"
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
                    class="absolute inset-x-0 top-full max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain border-b border-[var(--poet-border)] bg-[var(--poet-nav)] shadow-xl min-[1380px]:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <div class="px-4 pb-2">
                            <div class="relative w-full">
                                <TextInput
                                    v-model="keywords"
                                    :placeholder="keywords ? '' : ' '"
                                    :aria-label="t('nav.search')"
                                    class="w-full !rounded-full"
                                    @keyup.enter="
                                        search();
                                        showingNavigationDropdown = false;
                                    "
                                />
                                <span v-if="!keywords" :key="currentSearchHint" class="poet-search-hint pointer-events-none absolute inset-y-0 left-3 right-3 flex items-center overflow-hidden whitespace-nowrap text-sm text-[var(--poet-muted)]">
                                    <span class="poet-search-hint-text">{{ currentSearchHint }}</span>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 px-4 py-2 text-sm text-[var(--poet-muted)]">
                            <span>Valoda / Language</span>
                            <button
                                type="button"
                                @click="setLocale('lv')"
                                :class="locale === 'lv' ? 'font-bold text-[var(--poet-accent)]' : ''"
                            >
                                LV
                            </button>
                            <span>/</span>
                            <button
                                type="button"
                                @click="setLocale('en')"
                                :class="locale === 'en' ? 'font-bold text-[var(--poet-accent)]' : ''"
                            >
                                EN
                            </button>
                        </div>

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
                                    ? t('nav.light_mode')
                                    : t('nav.dark_mode')
                            }}
                        </button>

                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            {{ t('nav.home') }}
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
                            {{ t('nav.create_post') }}
                        </button>

                        <ResponsiveNavLink
                            v-if="authUser"
                            :href="route('poem.write')"
                        >
                            {{ t('nav.write_poem') }}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('group.index')"
                            :active="
                                route().current('group.index') ||
                                route().current('group.profile')
                            "
                        >
                            {{ t('nav.groups') }}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            :href="route('about')"
                            :active="route().current('about')"
                        >
                            {{ t('nav.about') }}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="authUser?.is_admin"
                            :href="route('admin.moderation')"
                            :active="route().current('admin.*')"
                        >
                            {{ locale === 'lv' ? 'Moderācija' : 'Moderation' }}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="authUser"
                            :href="route('messages.show')"
                            :active="route().current('messages.show')"
                        >
                            {{ locale === 'lv' ? 'Tiešās ziņas' : 'Direct messages' }}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            v-if="authUser"
                            :href="route('draft.index')"
                            :active="route().current('draft.index')"
                        >
                            {{ t('nav.drafts') }}
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
                                {{ t('nav.profile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                {{ t('nav.logout') }}
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
            <main
                id="main-content"
                tabindex="-1"
                :key="$page.component"
                class="poet-page-enter flex-1 min-h-0"
            >
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
