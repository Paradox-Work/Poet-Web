<script setup>
import { Head } from '@inertiajs/vue3';
import FollowingList from '@/Components/app/FollowingList.vue';
import GroupList from '@/Components/app/GroupList.vue';
import PostList from '@/Components/app/PostList.vue';
import CreatePost from '@/Components/app/CreatePost.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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

    <Head title="Poet Web" />
    <AuthenticatedLayout>

        <div class="grid lg:grid-cols-12 gap-3 p-4 lg:h-[calc(100vh-4rem)] lg:overflow-hidden">
            <div class="lg:col-span-3 lg:order-1 overflow-hidden">
                <GroupList />
            </div>
            <div class="lg:col-span-3 lg:order-3 h-full overflow-auto">
            <FollowingList :following="following" />
            </div>
            <div class="lg:col-span-6 lg:order-2 overflow-hidden flex flex-col">
                <CreatePost />
                <PostList :posts="posts" :following="following" />
            </div>
            
        </div>

    </AuthenticatedLayout>


</template>
