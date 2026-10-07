<script setup>

import {
    computed,
    ref
} from 'vue';

import TextInput
    from '@/Components/TextInput.vue';

import UserListItem
    from '@/Components/app/UserListItem.vue';


const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});


const searchKeyword =
    ref('');


const filteredUsers =
    computed(() => {

        const keyword =
            searchKeyword.value
                .trim()
                .toLowerCase();

        if (!keyword) {
            return props.users;
        }

        return props.users.filter(
            user =>
                user.name
                    ?.toLowerCase()
                    .includes(keyword)
                ||
                user.username
                    ?.toLowerCase()
                    .includes(keyword)
        );
    });

</script>


<template>

    <TextInput
        v-model="searchKeyword"
        placeholder="Type to search..."
        class="w-full"
    />


    <div
        class="mt-3 lg:min-h-full max-h-64 overflow-auto rounded border border-gray-100 dark:border-gray-700"
    >

        <div
            v-if="!filteredUsers.length"
            class="text-gray-400 text-center p-3"
        >
            {{
                searchKeyword
                    ? 'No users found.'
                    : 'You are not following anyone yet.'
            }}
        </div>


        <div v-else>

            <UserListItem
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
            />

        </div>

    </div>

</template>