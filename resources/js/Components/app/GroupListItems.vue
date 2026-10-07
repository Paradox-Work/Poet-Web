<script setup>
import {
    computed,
    ref
} from 'vue';

import GroupItem
    from '@/Components/app/GroupItem.vue';

import TextInput
    from '@/Components/TextInput.vue';

const props = defineProps({
    groups: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits([
    'createGroup'
]);

const searchKeyword = ref('');

const filteredGroups = computed(() => {

    const search =
        searchKeyword.value
            .trim()
            .toLowerCase();

    if (!search) {
        return props.groups;
    }

    return props.groups.filter(group => {

        const name =
            group.name?.toLowerCase() ?? '';

        const about =
            group.about?.toLowerCase() ?? '';

        return (
            name.includes(search) ||
            about.includes(search)
        );
    });
});
</script>


<template>

    <div class="flex gap-2">
        
        <TextInput
            v-model="searchKeyword"
            placeholder="Type to search..."
            class="w-full"
        />

        <button
            type="button"
            @click="emit('createGroup')"
            class="
                shrink-0
                rounded
                bg-indigo-500
                px-3
                py-1
                text-sm
                text-white
                hover:bg-indigo-600
            "
        >
            New group
        </button>

    </div>

    <div
        class="mt-3 lg:min-h-full max-h-64 overflow-auto rounded border border-gray-100 dark:border-gray-700"
    >

        <div
            v-if="filteredGroups.length === 0"
            class="text-gray-400 text-center p-3"
        >
            {{
                searchKeyword
                    ? 'No matching groups.'
                    : 'You are not a member of any groups yet.'
            }}
        </div>


        <div v-else>

            <GroupItem
                v-for="group in filteredGroups"
                :key="group.id"
                :group="group"
            />

        </div>

    </div>

</template>