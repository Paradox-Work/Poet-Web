<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompactPaginator from '@/Components/app/CompactPaginator.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    bookmarks: Object,
    lists: { type: Array, default: () => [] },
});

const { locale } = useI18n();

const listForm = useForm({
    name: '',
    description: '',
});

function createList() {
    listForm.post(route('reading-lists.store'), {
        preserveScroll: true,
        onSuccess: () => listForm.reset(),
    });
}

function deleteList(list) {
    if (!window.confirm(locale.value === 'lv' ? 'Dzēst šo sarakstu?' : 'Delete this list?')) {
        return;
    }

    router.delete(route('reading-lists.destroy', list.id), {
        preserveScroll: true,
    });
}

async function addToList(list, post) {
    await axios.post(
        route('reading-lists.posts.add', {
            readingList: list.id,
            post: post.id,
        })
    );

    router.reload({ only: ['lists'], preserveScroll: true });
}

async function removeFromList(list, post) {
    await axios.delete(
        route('reading-lists.posts.remove', {
            readingList: list.id,
            post: post.id,
        })
    );

    router.reload({ only: ['lists'], preserveScroll: true });
}
</script>

<template>
    <Head :title="locale === 'lv' ? 'Bibliotēka' : 'Library'" />

    <AuthenticatedLayout>
        <div class="mx-auto h-full max-w-6xl overflow-y-auto px-4 py-6">
            <header class="mb-6">
                <div class="poet-kicker">
                    {{ locale === 'lv' ? 'Tava kolekcija' : 'Your collection' }}
                </div>
                <h1 class="poet-title mt-1 text-3xl">
                    {{ locale === 'lv' ? 'Bibliotēka' : 'Library' }}
                </h1>
                <p class="mt-2 text-sm text-[var(--poet-muted)]">
                    {{
                        locale === 'lv'
                            ? 'Saglabā darbus un kārto tos savos lasīšanas sarakstos.'
                            : 'Save publications and organize them into reading lists.'
                    }}
                </p>
            </header>

            <section class="poet-card mb-7 p-4">
                <h2 class="font-serif text-xl font-semibold">
                    {{ locale === 'lv' ? 'Jauns lasīšanas saraksts' : 'New reading list' }}
                </h2>
                <form class="mt-3 grid gap-3 sm:grid-cols-[1fr_2fr_auto]" @submit.prevent="createList">
                    <input
                        v-model="listForm.name"
                        required
                        maxlength="80"
                        :placeholder="locale === 'lv' ? 'Nosaukums' : 'Name'"
                        class="rounded-xl border-[var(--poet-border)] bg-[var(--poet-bg)]"
                    />
                    <input
                        v-model="listForm.description"
                        maxlength="300"
                        :placeholder="locale === 'lv' ? 'Apraksts (neobligāti)' : 'Description (optional)'"
                        class="rounded-xl border-[var(--poet-border)] bg-[var(--poet-bg)]"
                    />
                    <button class="rounded-xl bg-[var(--poet-accent)] px-4 py-2 font-semibold text-white">
                        {{ locale === 'lv' ? 'Izveidot' : 'Create' }}
                    </button>
                </form>
            </section>

            <section class="mb-8">
                <div class="mb-3 flex items-end justify-between">
                    <div>
                        <div class="poet-kicker">
                            {{ locale === 'lv' ? 'Saglabātie darbi' : 'Saved publications' }}
                        </div>
                        <h2 class="font-serif text-2xl font-semibold">
                            {{ locale === 'lv' ? 'Grāmatzīmes' : 'Bookmarks' }}
                        </h2>
                    </div>
                </div>

                <div v-if="bookmarks.data?.length" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="post in bookmarks.data"
                        :key="post.id"
                        class="poet-card p-4"
                    >
                        <Link
                            :href="route('post.view', post.id)"
                            class="block font-serif text-lg font-semibold hover:text-[var(--poet-accent)]"
                        >
                            {{ post.title || (post.type === 'poem' ? (locale === 'lv' ? 'Dzejolis bez nosaukuma' : 'Untitled poem') : (locale === 'lv' ? 'Publikācija' : 'Publication')) }}
                        </Link>
                        <div class="mt-1 text-xs text-[var(--poet-muted)]">
                            {{ post.user.name }}
                        </div>
                        <div class="mt-3 line-clamp-3 text-sm text-[var(--poet-muted)]" v-html="post.body" />

                        <div v-if="lists.length" class="mt-4 flex flex-wrap gap-2">
                            <button
                                v-for="list in lists"
                                :key="list.id"
                                type="button"
                                class="rounded-full border border-[var(--poet-border)] px-2.5 py-1 text-xs hover:bg-[var(--poet-surface-soft)]"
                                @click="addToList(list, post)"
                            >
                                + {{ list.name }}
                            </button>
                        </div>
                    </article>
                </div>

                <div v-else class="poet-card py-12 text-center text-sm text-[var(--poet-muted)]">
                    {{ locale === 'lv' ? 'Te vēl nav saglabātu darbu.' : 'No bookmarked publications yet.' }}
                </div>

                <CompactPaginator
                    v-if="bookmarks.meta?.last_page > 1"
                    class="mt-4"
                    :meta="bookmarks.meta"
                />
            </section>

            <section>
                <div class="poet-kicker">
                    {{ locale === 'lv' ? 'Kolekcijas' : 'Collections' }}
                </div>
                <h2 class="mb-4 font-serif text-2xl font-semibold">
                    {{ locale === 'lv' ? 'Lasīšanas saraksti' : 'Reading lists' }}
                </h2>

                <div v-if="lists.length" class="space-y-5">
                    <article v-for="list in lists" :key="list.id" class="poet-card p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-serif text-xl font-semibold">{{ list.name }}</h3>
                                <p v-if="list.description" class="mt-1 text-sm text-[var(--poet-muted)]">
                                    {{ list.description }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="text-xs font-semibold text-red-500"
                                @click="deleteList(list)"
                            >
                                {{ locale === 'lv' ? 'Dzēst' : 'Delete' }}
                            </button>
                        </div>

                        <div v-if="list.posts.length" class="mt-4 grid gap-2 md:grid-cols-2">
                            <div
                                v-for="post in list.posts"
                                :key="post.id"
                                class="flex items-center justify-between gap-3 rounded-xl border border-[var(--poet-border)] p-3"
                            >
                                <Link :href="route('post.view', post.id)" class="min-w-0 truncate font-medium hover:text-[var(--poet-accent)]">
                                    {{ post.title || (locale === 'lv' ? 'Publikācija' : 'Publication') }}
                                </Link>
                                <button
                                    type="button"
                                    class="shrink-0 text-xs text-red-500"
                                    @click="removeFromList(list, post)"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                        <div v-else class="mt-4 text-sm text-[var(--poet-muted)]">
                            {{ locale === 'lv' ? 'Saraksts ir tukšs.' : 'This list is empty.' }}
                        </div>
                    </article>
                </div>

                <div v-else class="poet-card py-12 text-center text-sm text-[var(--poet-muted)]">
                    {{ locale === 'lv' ? 'Izveido pirmo lasīšanas sarakstu.' : 'Create your first reading list.' }}
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
