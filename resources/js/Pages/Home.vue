<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FollowingList from '@/Components/app/FollowingList.vue';
import GroupList from '@/Components/app/GroupList.vue';
import PostList from '@/Components/app/PostList.vue';
import CreatePost from '@/Components/app/CreatePost.vue';


defineProps({
    posts: Object,
    groups: Array,
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>

    <Head title="Poet Web" />

    <AuthenticatedLayout>

        <div class="grid lg:grid-cols-12 gap-3 mb-3 p-4 lg:h-full">
            <div class="lg:col-span-3 lg:order-1 overflow-hidden">
                <GroupList :groups="groups" />
            </div>
            <div class="lg:col-span-3 lg:order-3 h-full overflow-auto">
               <FollowingList />
            </div>
            <div class="lg:col-span-6 lg:order-2 overflow-hidden flex flex-col">
                <CreatePost />
                <PostList :posts="posts.data" />
            </div>
            
        </div>
        
    </AuthenticatedLayout>


</template>
