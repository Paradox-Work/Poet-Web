<script setup>
import { computed, ref } from 'vue';
import {
    Head,
    router,
    usePage
} from '@inertiajs/vue3';
import {
    DocumentTextIcon,
    PencilSquareIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import PostModal
    from '@/Components/app/PostModal.vue';

const props = defineProps({
    drafts: {
        type: Array,
        default: () => []
    }
});

const page = usePage();

const selectedDraft = ref(null);
const showEditor = ref(false);

const authUser = computed(
    () => page.props.auth.user
);

function continueDraft(draft) {
    selectedDraft.value = {
        ...draft,
        user: authUser.value,
        attachments: [],
        updated_at:
            draft.draft_saved_at
    };

    showEditor.value = true;
}

function deleteDraft(draft) {
    if (
        !window.confirm(
            'Delete this draft permanently?'
        )
    ) {
        return;
    }

    router.delete(
        route(
            'post.destroy',
            draft.id
        ),
        {
            preserveScroll: true
        }
    );
}

function savedLabel(value) {
    if (!value) {
        return 'Not saved yet';
    }

    return new Date(value)
        .toLocaleString(
            [],
            {
                dateStyle: 'medium',
                timeStyle: 'short'
            }
        );
}
</script>

<template>
    <Head title="Drafts" />

    <AuthenticatedLayout>
        <div
            class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8"
        >
            <div
                class="mb-6 flex items-end justify-between gap-4"
            >
                <div>
                    <p
                        class="mb-1 text-sm font-medium uppercase tracking-[0.18em] text-indigo-500"
                    >
                        Your writing
                    </p>

                    <h1
                        class="text-3xl font-semibold text-gray-900 dark:text-gray-100"
                    >
                        Drafts
                    </h1>

                    <p
                        class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        Unfinished poems are kept private until you publish them.
                    </p>
                </div>

                <div
                    class="text-sm text-gray-400"
                >
                    {{ drafts.length }}
                    {{
                        drafts.length === 1
                            ? 'draft'
                            : 'drafts'
                    }}
                </div>
            </div>

            <div
                v-if="drafts.length"
                class="grid gap-4 md:grid-cols-2"
            >
                <article
                    v-for="draft in drafts"
                    :key="draft.id"
                    class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div
                        class="mb-4 flex items-start justify-between gap-3"
                    >
                        <div
                            class="flex min-w-0 items-start gap-3"
                        >
                            <div
                                class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300"
                            >
                                <DocumentTextIcon
                                    class="h-5 w-5"
                                />
                            </div>

                            <div class="min-w-0">
                                <div
                                    class="mb-1 text-xs font-medium uppercase tracking-wide text-gray-400"
                                >
                                    {{
                                        draft.type === 'poem'
                                            ? 'Poem draft'
                                            : 'Post draft'
                                    }}
                                </div>

                                <h2
                                    class="truncate font-serif text-xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        draft.title ||
                                        'Untitled poem'
                                    }}
                                </h2>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="draft.preview"
                        class="mb-4 line-clamp-3 min-h-[4.5rem] font-serif leading-6 text-gray-600 dark:text-gray-300"
                    >
                        {{ draft.preview }}
                    </p>

                    <p
                        v-else
                        class="mb-4 min-h-[4.5rem] italic text-gray-400"
                    >
                        This page is still blank.
                    </p>

                    <div
                        v-if="draft.hashtags?.length"
                        class="mb-4 flex flex-wrap gap-1.5"
                    >
                        <span
                            v-for="tag in draft.hashtags"
                            :key="tag"
                            class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-500 dark:bg-gray-700 dark:text-gray-300"
                        >
                            #{{ tag }}
                        </span>
                    </div>

                    <div
                        class="mb-4 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400"
                    >
                        <span>
                            Saved
                            {{ savedLabel(
                                draft.draft_saved_at
                            ) }}
                        </span>

                        <span v-if="draft.group">
                            {{ draft.group.name }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="continueDraft(draft)"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                        >
                            <PencilSquareIcon
                                class="h-4 w-4"
                            />

                            Continue writing
                        </button>

                        <button
                            type="button"
                            @click="deleteDraft(draft)"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-gray-500 hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-red-900 dark:hover:bg-red-950/30 dark:hover:text-red-300"
                            aria-label="Delete draft"
                        >
                            <TrashIcon
                                class="h-5 w-5"
                            />
                        </button>
                    </div>
                </article>
            </div>

            <div
                v-else
                class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-800"
            >
                <DocumentTextIcon
                    class="mx-auto mb-4 h-10 w-10 text-gray-300 dark:text-gray-600"
                />

                <h2
                    class="text-lg font-medium text-gray-800 dark:text-gray-200"
                >
                    No drafts yet
                </h2>

                <p
                    class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                >
                    Start a poem and it will appear here after autosave.
                </p>
            </div>
        </div>

        <PostModal
            v-if="selectedDraft"
            v-model="showEditor"
            :post="selectedDraft"
            :group="selectedDraft.group"
        />
    </AuthenticatedLayout>
</template>
