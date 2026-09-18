<script setup>
import { computed } from 'vue';
import PostItem from '@/Components/app/PostItem.vue';

const props = defineProps({
    posts: {
        type: Array,
        default: () => [],
    },
});

const items = computed(() => props.posts || []);
</script>

<template>
    <div class="overflow-auto flex-1">
        <div v-if="!items.length" class="rounded border bg-white p-4 text-sm text-gray-500">
            No poems yet. Write the first one.
        </div>

        <PostItem v-for="post in items" :key="post.id" :post="post" />
    </div>
</template>
