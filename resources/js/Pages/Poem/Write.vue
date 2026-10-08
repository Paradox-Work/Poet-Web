<script setup>
import {
    computed,
    onBeforeUnmount,
    ref,
    watch
} from 'vue';

import {
    Head,
    Link,
    router,
    useForm
} from '@inertiajs/vue3';

import {
    ArrowLeftIcon,
    ChevronDownIcon,
    DocumentTextIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

import axios from 'axios';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TiptapEditor from '@/Components/app/TiptapEditor.vue';
import { getPoemForm, poemForms } from '@/data/poemForms.js';

const props = defineProps({
    drafts: {
        type: Array,
        default: () => []
    },
    openDraftId: {
        type: Number,
        default: null
    },
    groupId: {
        type: Number,
        default: null
    }
});

const draftId = ref(null);
const saveState = ref('idle');
const savedAt = ref(null);
const draftMenuOpen = ref(false);
const hashtagsInput = ref('');

let saveTimer = null;

const form = useForm({
    type: 'poem',
    poem_form: 'free_verse',
    title: '',
    caption: '',
    hashtags: [],
    body: '',
    group_id: props.groupId,
    attachments: [],
    deleted_file_ids: [],
    _method: 'POST'
});

const selectedForm = computed(() =>
    getPoemForm(form.poem_form)
);

const lines = computed(() => {
    if (!form.body) return [];

    const wrapper = document.createElement('div');
    wrapper.innerHTML = form.body;

    const paragraphs = Array.from(
        wrapper.querySelectorAll('p')
    );

    if (paragraphs.length) {
        return paragraphs.map(
            paragraph =>
                paragraph.textContent?.trim() ?? ''
        );
    }

    return (wrapper.textContent ?? '')
        .split('\n')
        .map(line => line.trim());
});

const lineCount = computed(
    () => lines.value.filter(Boolean).length
);

const stanzaCount = computed(() => {
    if (!lines.value.length) return 0;

    let count = 0;
    let inStanza = false;

    for (const line of lines.value) {
        if (line) {
            if (!inStanza) count++;
            inStanza = true;
        } else {
            inStanza = false;
        }
    }

    return count;
});

const lineProgress = computed(() =>
    selectedForm.value.lineTarget
        ? `${lineCount.value} / ${selectedForm.value.lineTarget}`
        : String(lineCount.value)
);

function normalizeHashtags(value) {
    const seen = new Set();

    return value
        .split(/[\s,]+/)
        .map(tag => tag.trim().replace(/^#+/, ''))
        .filter(Boolean)
        .filter(tag => {
            const key = tag.toLocaleLowerCase();
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        })
        .slice(0, 10);
}

function hasContent() {
    return Boolean(
        form.title.trim() ||
        form.caption.trim() ||
        lines.value.some(Boolean) ||
        hashtagsInput.value.trim()
    );
}

function draftPayload() {
    return {
        type: 'poem',
        poem_form: form.poem_form,
        title: form.title,
        caption: form.caption,
        hashtags: normalizeHashtags(
            hashtagsInput.value
        ),
        body: form.body,
        group_id: form.group_id
    };
}

async function saveDraft() {
    if (!hasContent()) return;

    saveState.value = 'saving';

    try {
        const response = draftId.value
            ? await axios.put(
                route('draft.update', draftId.value),
                draftPayload()
            )
            : await axios.post(
                route('draft.store'),
                draftPayload()
            );

        draftId.value =
            response.data.draft.id;

        savedAt.value =
            response.data.draft.draft_saved_at;

        saveState.value = 'saved';
    } catch (error) {
        saveState.value = 'error';
        console.error(
            'Draft autosave failed:',
            error
        );
    }
}

function scheduleSave() {
    if (saveTimer) clearTimeout(saveTimer);

    saveTimer = setTimeout(
        saveDraft,
        1200
    );
}

function openDraft(draft) {
    draftId.value = draft.id;
    form.poem_form =
        draft.poem_form ?? 'free_verse';
    form.title = draft.title ?? '';
    form.caption = draft.caption ?? '';
    form.body = draft.body ?? '';
    form.group_id =
        draft.group_id ?? null;
    form.hashtags =
        draft.hashtags ?? [];
    hashtagsInput.value =
        form.hashtags
            .map(tag => `#${tag}`)
            .join(' ');
    savedAt.value =
        draft.draft_saved_at;
    saveState.value = 'saved';
    draftMenuOpen.value = false;
}

function newPoem() {
    draftId.value = null;
    saveState.value = 'idle';
    savedAt.value = null;
    hashtagsInput.value = '';

    form.reset();
    form.type = 'poem';
    form.poem_form = 'free_verse';
    form.group_id = props.groupId;

    draftMenuOpen.value = false;
}

function publish() {
    form.hashtags =
        normalizeHashtags(
            hashtagsInput.value
        );

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(
                route('dashboard')
            );
        }
    };

    if (draftId.value) {
        form._method = 'PUT';
        form.post(
            route(
                'post.update',
                draftId.value
            ),
            options
        );
        return;
    }

    form._method = 'POST';
    form.post(
        route('post.create'),
        options
    );
}

watch(
    [
        () => form.poem_form,
        () => form.title,
        () => form.caption,
        () => form.body,
        hashtagsInput
    ],
    scheduleSave
);

onBeforeUnmount(() => {
    if (saveTimer) {
        clearTimeout(saveTimer);
    }
});

if (props.openDraftId) {
    const draft = props.drafts.find(
        item =>
            item.id === props.openDraftId
    );

    if (draft) {
        openDraft(draft);
    }
}
</script>

<template>
    <Head title="Write poem" />

    <AuthenticatedLayout>
        <div class="flex h-full min-h-0 flex-col bg-stone-50 dark:bg-gray-900">
            <header class="flex items-center justify-between gap-4 border-b border-stone-200 bg-white px-5 py-3 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                    </Link>

                    <div>
                        <h1 class="font-serif text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Write poem
                        </h1>
                        <div class="text-xs text-gray-400">
                            {{
                                saveState === 'saving'
                                    ? 'Saving...'
                                    : saveState === 'saved'
                                        ? 'Saved'
                                        : saveState === 'error'
                                            ? 'Save failed'
                                            : 'Autosave ready'
                            }}
                            <template v-if="savedAt">
                                · {{ new Date(savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="newPoem"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        <PlusIcon class="h-4 w-4" />
                        New poem
                    </button>

                    <div class="relative">
                        <button
                            type="button"
                            @click="draftMenuOpen = !draftMenuOpen"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <DocumentTextIcon class="h-4 w-4" />
                            Open draft
                            <ChevronDownIcon class="h-4 w-4" />
                        </button>

                        <div
                            v-if="draftMenuOpen"
                            class="absolute right-0 z-30 mt-2 w-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div
                                v-if="drafts.length"
                                class="max-h-80 overflow-auto p-2"
                            >
                                <button
                                    v-for="draft in drafts"
                                    :key="draft.id"
                                    type="button"
                                    @click="openDraft(draft)"
                                    class="block w-full rounded-lg px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <div class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">
                                        {{ draft.title || 'Untitled poem' }}
                                    </div>
                                    <div class="mt-1 text-xs capitalize text-gray-400">
                                        {{ (draft.poem_form || 'free_verse').replaceAll('_', ' ') }}
                                    </div>
                                </button>
                            </div>

                            <div v-else class="p-4 text-sm text-gray-400">
                                No saved drafts yet.
                            </div>

                            <Link
                                :href="route('draft.index')"
                                class="block border-t border-gray-100 px-4 py-3 text-sm font-medium text-indigo-600 hover:bg-gray-50 dark:border-gray-700 dark:text-indigo-300 dark:hover:bg-gray-700"
                            >
                                View all drafts
                            </Link>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="publish"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Publishing...' : 'Publish' }}
                    </button>
                </div>
            </header>

            <div class="grid min-h-0 flex-1 lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="overflow-y-auto border-r border-stone-200 bg-stone-100/70 p-5 dark:border-gray-700 dark:bg-gray-800/60">
                    <div class="space-y-6">
                        <section>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">
                                Poem form
                            </label>

                            <select
                                v-model="form.poem_form"
                                class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option
                                    v-for="item in poemForms"
                                    :key="item.id"
                                    :value="item.id"
                                >
                                    {{ item.name }}
                                </option>
                            </select>

                            <p class="mt-2 text-sm leading-5 text-gray-500 dark:text-gray-400">
                                {{ selectedForm.summary }}
                            </p>
                        </section>

                        <section class="rounded-xl border border-stone-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/60">
                            <h2 class="mb-3 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                Structure
                            </h2>

                            <ul class="space-y-2 text-sm leading-5 text-gray-500 dark:text-gray-400">
                                <li
                                    v-for="rule in selectedForm.structure"
                                    :key="rule"
                                >
                                    {{ rule }}
                                </li>
                            </ul>
                        </section>

                        <section class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900/60">
                                <div class="text-xs uppercase tracking-wide text-gray-400">
                                    Lines
                                </div>
                                <div class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    {{ lineProgress }}
                                </div>
                            </div>

                            <div class="rounded-xl border border-stone-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900/60">
                                <div class="text-xs uppercase tracking-wide text-gray-400">
                                    Stanzas
                                </div>
                                <div class="mt-1 text-lg font-semibold text-gray-800 dark:text-gray-100">
                                    {{ stanzaCount }}
                                </div>
                            </div>
                        </section>

                        <section v-if="selectedForm.rhymeScheme">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">
                                Rhyme scheme
                            </div>
                            <div class="mt-2 font-mono text-sm text-gray-700 dark:text-gray-200">
                                {{ selectedForm.rhymeScheme }}
                            </div>
                        </section>

                        <section>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">
                                Caption
                            </label>

                            <textarea
                                v-model="form.caption"
                                rows="4"
                                maxlength="500"
                                placeholder="Context, dedication, or a note about the piece..."
                                class="block w-full resize-none rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            />
                        </section>

                        <section>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">
                                Hashtags
                            </label>

                            <input
                                v-model="hashtagsInput"
                                type="text"
                                placeholder="night, memory, city"
                                class="block w-full rounded-lg border-gray-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            />
                            <p class="mt-2 text-xs text-gray-400">
                                Up to 10 tags. Spaces or commas both work.
                            </p>
                        </section>
                    </div>
                </aside>

                <main class="overflow-y-auto p-5 lg:p-8">
                    <div class="mx-auto max-w-4xl">
                        <div class="rounded-2xl border border-stone-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <input
                                v-model="form.title"
                                type="text"
                                maxlength="160"
                                placeholder="Untitled poem"
                                class="w-full border-0 border-b border-stone-100 bg-transparent px-8 py-6 font-serif text-3xl font-semibold text-gray-900 placeholder:text-gray-300 focus:ring-0 dark:border-gray-700 dark:text-gray-100 dark:placeholder:text-gray-600"
                            />

                            <div class="p-6 lg:p-8">
                                <TiptapEditor
                                    v-model="form.body"
                                    poem-mode
                                />
                            </div>
                        </div>

                        <div class="mt-4 text-center text-xs text-gray-400">
                            Your poem stays private as a draft until you publish it.
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
