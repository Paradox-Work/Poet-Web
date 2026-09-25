<script setup>
import { 
    Disclosure,
    DisclosureButton, 
    DisclosurePanel,
    Menu,
    MenuButton,
    MenuItems,
    MenuItem
        } from '@headlessui/vue'
        
import {
    PencilIcon,
    TrashIcon,
    EllipsisVerticalIcon
        } from '@heroicons/vue/20/solid';

import PostUserHeader from '@/Components/app/PostUserHeader.vue';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { isImage } from '@/helpers.js';

const props = defineProps({
    post: Object,
});

const plainBody = computed(() => {

    const body = props.post.body ?? '';

    return body
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
});

const emit = defineEmits([
    'editClick'
]);

function openEditModal() {
    emit('editClick', props.post);
}

function deletePost() {
    if (window.confirm('Are you sure you want to delete this post?')) {
        router.delete(
            route('post.destroy', props.post.id),
            {
                preserveScroll: true
            }
        );
    }
}

</script>

<template>
    <div class="bg-white border rounded p-4 mb-3 shadow">
        <div class="flex items-center justify-between mb-3">

            <PostUserHeader :post="post" />

            <Menu
                as="div"
                class="relative inline-block text-left"
            >

                <MenuButton
                    class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center"
                >
                    <EllipsisVerticalIcon class="w-5 h-5" />
                </MenuButton>

                <transition
                    enter-active-class="transition duration-100 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-75 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >

                    <MenuItems
                        class="absolute right-0 mt-2 w-32 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none z-20"
                    >

                        <div class="px-1 py-1">

                            <MenuItem v-slot="{ active }">

                                <button
                                    @click="openEditModal"
                                    :class="[
                                        active
                                            ? 'bg-indigo-500 text-white'
                                            : 'text-gray-900',

                                        'group flex w-full items-center rounded-md px-2 py-2 text-sm'
                                    ]"
                                >

                                    <PencilIcon class="mr-2 h-5 w-5" />

                                    Edit

                                </button>

                            </MenuItem>

                            <MenuItem v-slot="{ active }">

                                <button
                                    @click="deletePost"
                                    :class="[
                                        active
                                            ? 'bg-indigo-500 text-white'
                                            : 'text-gray-900',

                                        'group flex w-full items-center rounded-md px-2 py-2 text-sm'
                                    ]"
                                >

                                    <TrashIcon class="mr-2 h-5 w-5" />

                                    Delete

                                </button>

                            </MenuItem>

                        </div>

                    </MenuItems>

                </transition>

            </Menu>

        </div>
        <div class="mb-3">

            <Disclosure
                v-if="plainBody.length > 200"
                v-slot="{ open }"
            >

                <div v-if="!open">
                    {{ plainBody.substring(0, 200) }}...
                </div>

                <DisclosurePanel>
                    <div
                        class="rich-text-output"
                        v-html="post.body"
                    />
                </DisclosurePanel>

                <div class="flex justify-end">

                    <DisclosureButton
                        class="text-blue-500 hover:text-blue-700 hover:underline"
                    >
                        {{ open ? 'Display less' : 'Display more' }}
                    </DisclosureButton>

                </div>

            </Disclosure>


            <div
                v-else
                class="rich-text-output"
                v-html="post.body"
            />

        </div>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
            <template v-for="attachment in post.attachments" :key="attachment.id">
                
                <div class="group bg-blue-100 flex flex-col items-center justify-center text-gray-500  rounded  h-48 relative">
                    
                    <!---Download-->
                    <a
                        :href="route('post.download', attachment.id)"
                        class="z-20 opacity-0 group-hover:opacity-100 transition-all w-8 h-8 flex items-center justify-center text-gray-100 bg-gray-600 rounded absolute right-2 top-2 hover:bg-gray-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-400 hover:text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                    </a>
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