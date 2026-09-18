<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    post: Object,
});

const commentForms = reactive({});

function getForm(postId) {
    if (!commentForms[postId]) {
        commentForms[postId] = useForm({ comment: '' });
    }

    return commentForms[postId];
}

function submitComment(postId) {
    const form = getForm(postId);

    form.post(route('posts.comments.store', postId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            router.reload({ only: ['posts'] });
        },
    });
}
</script>

<template>
    <div class="mb-3 rounded border bg-white p-4 shadow-sm">
        <Link
            v-if="post.user?.username"
            :href="route('profile', { username: post.user.username })"
            class="mb-3 flex items-center gap-3 hover:opacity-90"
        >
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-700">
                {{ post.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            <div>
                <div class="font-semibold text-gray-800">{{ post.user?.name || 'Unknown author' }}</div>
                <div class="text-xs text-gray-500">{{ post.created_at }}</div>
            </div>
        </Link>

        <div v-else class="mb-3 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-700">
                {{ post.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            <div>
                <div class="font-semibold text-gray-800">{{ post.user?.name || 'Unknown author' }}</div>
                <div class="text-xs text-gray-500">{{ post.created_at }}</div>
            </div>
        </div>

        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-lg font-bold text-gray-900">{{ post.title || 'Untitled poem' }}</h3>
            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs uppercase tracking-wide text-gray-700">
                {{ post.category || 'general' }}
            </span>
        </div>

        <div class="mb-3 whitespace-pre-line text-gray-700">{{ post.body }}</div>

        <div class="border-t pt-3">
            <div class="mb-2 text-sm font-medium text-gray-700">
                Comments ({{ post.comments?.length || 0 }})
            </div>

            <div v-if="post.comments?.length" class="space-y-2">
                <div v-for="comment in post.comments" :key="comment.id" class="rounded bg-gray-50 p-2 text-sm text-gray-700">
                    <div class="font-medium text-gray-800">{{ comment.user?.name || 'User' }}</div>
                    <div>{{ comment.comment }}</div>
                </div>
            </div>

            <div v-else class="mb-2 text-xs text-gray-500">No comments yet.</div>

            <div class="mt-3 flex gap-2">
                <input
                    v-model="getForm(post.id).data.comment"
                    type="text"
                    placeholder="Write a comment..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                    @keydown.enter.prevent="submitComment(post.id)"
                />
                <button
                    type="button"
                    @click="submitComment(post.id)"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500"
                >
                    Post
                </button>
            </div>
        </div>
    </div>
</template>
