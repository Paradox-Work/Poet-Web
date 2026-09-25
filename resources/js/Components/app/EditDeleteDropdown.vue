<script setup>
import {
    EllipsisVerticalIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/vue/20/solid';

import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems
} from '@headlessui/vue';

import { usePage } from '@inertiajs/vue3';

defineProps({
    user: Object
});

defineEmits([
    'edit',
    'delete'
]);

const authUser =
    usePage().props.auth.user;
</script>

<template>
    <Menu
        v-if="user?.id === authUser.id"
        as="div"
        class="relative inline-block text-left"
    >
        <MenuButton
            type="button"
            class="w-8 h-8 rounded-full hover:bg-black/5 transition flex items-center justify-center"
            aria-label="More options"
        >
            <EllipsisVerticalIcon
                class="w-5 h-5"
            />
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
                            type="button"
                            @click="$emit('edit')"
                            :class="[
                                active
                                    ? 'bg-indigo-500 text-white'
                                    : 'text-gray-900',

                                'group flex w-full items-center rounded-md px-2 py-2 text-sm'
                            ]"
                        >
                            <PencilIcon
                                class="mr-2 h-5 w-5"
                            />

                            Edit
                        </button>
                    </MenuItem>

                    <MenuItem v-slot="{ active }">
                        <button
                            type="button"
                            @click="$emit('delete')"
                            :class="[
                                active
                                    ? 'bg-indigo-500 text-white'
                                    : 'text-gray-900',

                                'group flex w-full items-center rounded-md px-2 py-2 text-sm'
                            ]"
                        >
                            <TrashIcon
                                class="mr-2 h-5 w-5"
                            />

                            Delete
                        </button>
                    </MenuItem>

                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>