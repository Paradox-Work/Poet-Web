<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const postCreating = ref(false);
const newPostForm = useForm({
    title: '',
    body: '',
    category: 'general',
});

function submit() {
    newPostForm.post(route('post.create'), {
        preserveScroll: true,
        onSuccess: () => {
            newPostForm.reset();
            postCreating.value = false;
        },
    });
}
</script>

<template>
    <div class="bg-white border rounded p-4 mb-3 shadow-sm">
        <div class="mb-3">
            <input
                v-model="newPostForm.title"
                type="text"
                placeholder="Poem title"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            />
            <div v-if="newPostForm.errors.title" class="mt-1 text-xs text-red-600">
                {{ newPostForm.errors.title }}
            </div>
        </div>

        <div class="mb-3">
            <select
                v-model="newPostForm.category"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            >
                <option value="general">General</option>
                <option value="love">Love</option>
                <option value="nature">Nature</option>
                <option value="sadness">Sadness</option>
                <option value="inspiration">Inspiration</option>
            </select>
        </div>

        <textarea
            v-model="newPostForm.body"
            rows="4"
            placeholder="Write your poem..."
            class="mb-3 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
            @focus="postCreating = true"
        />

        <div v-if="newPostForm.errors.body" class="mb-2 text-xs text-red-600">
            {{ newPostForm.errors.body }}
        </div>

        <div v-if="postCreating" class="flex justify-end">
            <button
                @click="submit"
                type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
            >
                Publish poem
            </button>
        </div>
    </div>
</template>
