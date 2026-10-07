<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

import PostModal from '@/Components/app/PostModal.vue';


const authUser = usePage().props.auth.user;

const showModal = ref(false);

const props = defineProps({
    group: {
        type: Object,
        default: null
    }
});

const newPost = {
    id: null,
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

    <div class="bg-white border border-gray-200 rounded p-4 mb-3 dark:bg-gray-800 dark:border-gray-700">

        <button
            type="button"
            @click="showCreatePostModal"
            class="py-2 px-3 border-2 border-gray-200 text-gray-500 rounded-md w-full text-left hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
        >
            Click here to create a new post
        </button>


        <PostModal
            :post="newPost"
            :group="group"
            v-model="showModal"
        />

    </div>

</template>

<style scoped>

</style>