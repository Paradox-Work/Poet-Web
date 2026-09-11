<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue';

const props = defineProps({
    post: Object,
    following: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const authUserId = computed(() => page.props.auth.user.id);
const isFollowing = computed(() =>
    props.following.some(u => u.id === props.post.user.id)
);

function toggleFollow() {
    if (isFollowing.value) {
        router.delete(route('unfollow', props.post.user.id));
        return;
    }

    router.post(route('follow', props.post.user.id));
}

function isImage(attachment) {
    const mime = attachment.mime.split('/');
    return mime[0].toLowerCase() === 'image';
}

const isLong = computed(() => props.post.body.length > 200);

const preview = computed(() => {
    if (!isLong.value) return props.post.body;
    return props.post.body.substring(0, 200).trim() + '…';
});
</script>

<template>
    <div class="bg-white border rounded p-4 mb-3 shadow overflow-hidden">
        <div class="flex items-center gap-2 mb-3">
            <a href="javascript:void(0)">
                <img 
                    :src="post.user.avatar" 
                    alt="User Avatar" 
                    class="w-10 h-10 rounded-full border-2 transition-all duration-150 hover:border-blue-500" 
                />
            </a>
            <div class="min-w-0">
                <h4 class="font-bold truncate">
                    <a href="javascript:void(0)" class="hover:underline">{{ post.user.name }}</a>
                    <template v-if="post.group">
                        <span class="text-gray-400 mx-1">•</span>
                        <a href="javascript:void(0)" class="hover:underline">{{ post.group.name }}</a>
                    </template>
                </h4>
                <button
                v-if="post.user.id !== authUserId"
                type="button"
                class="mt-1 px-3 py-1 rounded-md text-xs font-semibold transition-colors"
                :class="isFollowing
                    ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                    : 'bg-indigo-600 text-white hover:bg-indigo-700'"
                @click="toggleFollow"
            >
                {{ isFollowing ? 'Unfollow' : 'Follow' }}
            </button>           
                <small class="text-gray-500">{{ post.created_at }}</small>
            </div>
        </div>
        <div class="mb-3 min-w-0">
            <div v-if="!isLong" class="whitespace-pre-wrap break-words">{{ post.body }}</div>

            <Disclosure v-else v-slot="{ open }">
                <div v-if="!open" class="whitespace-pre-wrap break-words">{{ preview }}</div>
                <DisclosurePanel>
                    <div class="whitespace-pre-wrap break-words">{{ post.body }}</div>
                </DisclosurePanel>
                <div class="flex justify-end">
                    <DisclosureButton class="text-blue-500 hover:text-blue-700 hover:underline">
                        {{ open ? 'Display less' : 'Display more' }}
                    </DisclosureButton>
                </div>
            </Disclosure>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
            <template v-for="attachment in post.attachments || []" :key="attachment.id">
                
                <div class="group bg-blue-100 flex flex-col items-center justify-center text-gray-500  rounded  h-48 relative">
                    
                    <!---Download-->
                    <button class="opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center text-gray-100 bg-gray-600 rounded absolute right-2 top-2 hover:bg-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-400 hover:text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </button>
                    <!---Download-->

                    <img 
                        v-if="isImage(attachment)"
                        :src="attachment.url" 
                        alt="Attachment" 
                        class="w-full h-48 object-cover rounded"
                            />
                    
                    <template v-else>    
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-2/5 h-2/5 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    <small>{{ attachment.name }}</small>
                    </template>
                </div>
            </template>
        </div>
        <div class="flex gap-2 mt-3">
            <button class=" text-gray-800 flex gap-1 items-center justify-center py-2 px-4 bg-gray-100 hover:bg-gray-200 rounded-lg flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
                Like 
            </button>
            <button class=" text-gray-800 flex gap-1 items-center justify-center py-2 px-4 bg-gray-100 hover:bg-gray-200 rounded-lg flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                </svg>
                Comment
            </button>
        </div>
    </div>
</template>

<style scoped>
</style>