<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

import PostModal from '@/Components/app/PostModal.vue';


const authUser = usePage().props.auth.user;

const showModal = ref(false);


const newPost = {
    id: null,
    body: '',
    user: authUser,
    group: null,
    updated_at: null
};


function showCreatePostModal() {
    showModal.value = true;
}

</script>

<template>

    <div class="bg-white border rounded p-4 mb-3">

        <button
            type="button"
            @click="showCreatePostModal"
            class="py-2 px-3 border-2 border-gray-200 text-gray-500 rounded-md w-full text-left hover:bg-gray-50"
        >
            Click here to create a new post
        </button>


        <PostModal
            :post="newPost"
            v-model="showModal"
        />

    </div>

</template>

<style scoped>

</style>