<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FollowingList from '@/Components/app/FollowingList.vue';
import GroupList from '@/Components/app/GroupList.vue';
import PostList from '@/Components/app/PostList.vue';
import CreatePost from '@/Components/app/CreatePost.vue';

const props = defineProps({
    posts: {
        type: Object,
        default: () => ({ data: [] }),
    },
});
</script>

<template>
    <Head title="Poet Web" />

    <AuthenticatedLayout>
        <div class="grid gap-3 p-4 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <GroupList />
            </div>

            <div class="lg:col-span-3">
                <FollowingList />
            </div>

            <div class="lg:col-span-6">
                <CreatePost />
                <PostList :posts="props.posts.data || []" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
