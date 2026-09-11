<script setup>
import PostItem from '@/Components/app/PostItem.vue';
defineProps({
    posts: {
        type: Array,
        required: true,
    },
    following: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>

    <div class="overflow-auto flex-1">
        <p v-if="posts.length === 0" class="rounded border bg-white p-4 text-gray-500">
            No posts yet.
        </p>
        <PostItem v-for="post in posts" :key="post.id" :post="post" :following="following" />
    </div>

</template>

<style scoped>

</style>