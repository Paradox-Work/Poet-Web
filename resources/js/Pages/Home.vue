<script setup>
import { Head, Link } from '@inertiajs/vue3';
import FollowingList from '@/Components/app/FollowingList.vue';
import GroupList from '@/Components/app/GroupList.vue';
import PostList from '@/Components/app/PostList.vue';
import CreatePost from '@/Components/app/CreatePost.vue';


defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
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
    <div class="grid lg:grid-cols-12 gap-3 mb-3 p-4 h-fu">
        <div class="lg:col-span-3 lg:order-1 overflow-hidden">
            <GroupList />
        </div>
        <div class="lg:col-span-3 lg:order-3 h-full overflow-auto">
           <FollowingList />
        </div>
        <div class="lg:col-span-6 lg:order-2 overflow-hidden">
            <CreatePost />
            <PostList />
        </div>
        
    </div>


</template>
