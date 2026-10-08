<script setup>
import { ref } from 'vue';
import {
    Link,
    usePage
} from '@inertiajs/vue3';

import {
    DocumentTextIcon,
    PencilSquareIcon
} from '@heroicons/vue/24/outline';

import PostModal from '@/Components/app/PostModal.vue';

const authUser =
    usePage().props.auth.user;

const showModal = ref(false);

const props = defineProps({
    group: {
        type: Object,
        default: null
    }
});

const newPost = {
    id: null,
    type: 'post',
    body: '',
    user: authUser,
    group: props.group ?? null,
    updated_at: null
};

function showCreatePostModal() {
    showModal.value = true;
}
</script>

<template>
    <div
        class="mb-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800"
    >
        <div class="grid gap-2 sm:grid-cols-2">
            <button
                type="button"
                @click="showCreatePostModal"
                class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-left text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                <DocumentTextIcon class="h-5 w-5" />

                <div>
                    <div class="text-sm font-medium">
                        Quick post
                    </div>
                    <div class="text-xs text-gray-400">
                        Share a thought or discussion
                    </div>
                </div>
            </button>

            <Link
                :href="
                    route(
                        'poem.write',
                        group
                            ? { group: group.id }
                            : {}
                    )
                "
                class="flex items-center gap-3 rounded-lg border border-indigo-200 bg-indigo-50/60 px-4 py-3 text-left text-indigo-700 transition hover:bg-indigo-50 dark:border-indigo-900/70 dark:bg-indigo-950/20 dark:text-indigo-300 dark:hover:bg-indigo-950/40"
            >
                <PencilSquareIcon class="h-5 w-5" />

                <div>
                    <div class="text-sm font-medium">
                        Write poem
                    </div>
                    <div class="text-xs opacity-70">
                        Open the writing studio
                    </div>
                </div>
            </Link>
        </div>

        <PostModal
            :post="newPost"
            :group="group"
            :allow-poem-mode="false"
            v-model="showModal"
        />
    </div>
</template>
