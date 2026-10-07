<script setup>
import UserListItem
    from '@/Components/app/UserListItem.vue';

import GroupItem
    from '@/Components/app/GroupItem.vue';

import PostList
    from '@/Components/app/PostList.vue';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';


defineProps({
    users: Array,
    groups: Array,
    posts: Object,
    search: String
});
</script>


<template>
    <AuthenticatedLayout>

        <div
            class="p-4 overflow-auto h-full"
        >

            <h1
                class="text-xl font-bold mb-4"
            >
                Search results for
                "{{ search }}"
            </h1>


            <div
                class="grid grid-cols-1 sm:grid-cols-2 gap-3"
            >

                <div
                    class="shadow bg-white p-3 rounded mb-3"
                >

                    <h2
                        class="text-lg font-bold mb-2"
                    >
                        Users
                    </h2>

                    <UserListItem
                        v-for="user of users"
                        :key="user.id"
                        :user="user"
                    />

                    <div
                        v-if="!users.length"
                        class="py-8 text-center text-gray-500"
                    >
                        No users were found.
                    </div>

                </div>


                <div
                    class="shadow bg-white p-3 rounded mb-3"
                >

                    <h2
                        class="text-lg font-bold mb-2"
                    >
                        Groups
                    </h2>

                    <GroupItem
                        v-for="group of groups"
                        :key="group.id"
                        :group="group"
                    />

                    <div
                        v-if="!groups.length"
                        class="py-8 text-center text-gray-500"
                    >
                        No groups were found.
                    </div>

                </div>

            </div>


            <div>

                <h2
                    class="text-lg font-bold mb-2"
                >
                    Posts
                </h2>

                <PostList
                    v-if="posts.data.length"
                    :posts="posts"
                    class="flex-1"
                />

                <div
                    v-else
                    class="py-8 text-center text-gray-500"
                >
                    No posts were found.
                </div>

            </div>

        </div>

    </AuthenticatedLayout>
</template>