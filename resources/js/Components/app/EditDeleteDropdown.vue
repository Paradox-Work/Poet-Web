<script setup>
import {
    computed
} from 'vue';

import {
    ClipboardIcon
} from '@heroicons/vue/24/outline';

import {
    EllipsisVerticalIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon
} from '@heroicons/vue/20/solid';

import {
    Menu,
    MenuButton,
    MenuItem,
    MenuItems
} from '@headlessui/vue';

import {
    Link,
    usePage
} from '@inertiajs/vue3';

const props = defineProps({
    user: {
        type: Object,
        required: true
    },

    post: {
        type: Object,
        default: null
    },

    comment: {
        type: Object,
        default: null
    }
});

defineEmits([
    'edit',
    'delete'
]);

const page =
    usePage();

const authUser =
    computed(
        () => page.props.auth.user
    );

const editAllowed =
    computed(() => {

        return (
            props.user?.id ===
            authUser.value.id
        );
    });


const deleteAllowed =
    computed(() => {

        /*
         * User owns the item.
         */
        if (
            props.user?.id ===
            authUser.value.id
        ) {
            return true;
        }


        /*
         * Post owner may delete comments
         * left on their post.
         */
        if (
            props.comment &&
            props.post?.user?.id ===
                authUser.value.id
        ) {
            return true;
        }


        /*
         * Backend-calculated permission,
         * including group admins.
         */
        if (
            !props.comment &&
            props.post?.can_delete
        ) {
            return true;
        }


        return false;
    });


const showMenu =
    computed(() => {

        /*
         * Every visible post gets
         * Open / Copy actions.
         */
        if (
            props.post &&
            !props.comment
        ) {
            return true;
        }

        /*
         * Comment menus still depend
         * on management permissions.
         */
        return (
            editAllowed.value ||
            deleteAllowed.value
        );
    });

async function copyPostUrl() {

    if (
        !props.post ||
        props.comment
    ) {
        return;
    }

    try {

        await navigator.clipboard.writeText(
            route(
                'post.view',
                props.post.id
            )
        );

    } catch (error) {

        console.error(
            'Failed to copy post URL:',
            error
        );
    }
}

</script>

<template>
    <Menu
        v-if="showMenu"
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
                class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none z-20"
            >
                <div class="px-1 py-1">

                    <MenuItem
                        v-if="post && !comment"
                        v-slot="{ active }"
                    >
                        <Link
                            :href="
                                route(
                                    'post.view',
                                    post.id
                                )
                            "
                            :class="[
                                active
                                    ? 'bg-indigo-500 text-white'
                                    : 'text-gray-900',

                                'group flex w-full items-center rounded-md px-2 py-2 text-sm'
                            ]"
                        >
                            <EyeIcon
                                class="mr-2 h-5 w-5"
                            />

                            Open Post
                        </Link>
                    </MenuItem>


                    <MenuItem
                        v-if="post && !comment"
                        v-slot="{ active }"
                    >
                        <button
                            type="button"
                            @click="copyPostUrl"
                            :class="[
                                active
                                    ? 'bg-indigo-500 text-white'
                                    : 'text-gray-900',

                                'group flex w-full items-center rounded-md px-2 py-2 text-sm'
                            ]"
                        >
                            <ClipboardIcon
                                class="mr-2 h-5 w-5"
                            />

                            Copy Post URL
                        </button>
                    </MenuItem>

                    <MenuItem
                        v-if="editAllowed"
                        v-slot="{ active }"
                    >
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

                    <MenuItem
                        v-if="deleteAllowed"
                        v-slot="{ active }"
                    >
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