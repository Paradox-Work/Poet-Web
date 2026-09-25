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

    <TextInput
        v-model="searchKeyword"
        placeholder="Type to search..."
        class="w-full"
    />

    <div
        class="mt-3 lg:min-h-full max-h-64 overflow-auto rounded border border-gray-100"
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