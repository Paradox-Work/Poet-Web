<script setup>
defineProps({
    group: Object
});
</script>


<template>

    <div
        class="mb-2 cursor-pointer hover:bg-gray-100 rounded"
    >

        <div
            class="flex items-start gap-2 py-2 px-2"
        >

            <!-- Thumbnail -->
            <img
                v-if="group.thumbnail_url"
                :src="group.thumbnail_url"
                class="w-10 h-10 rounded-full object-cover"
                alt="Group thumbnail"
            />

            <!-- Temporary fallback -->
            <div
                v-else
                class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold"
            >
                {{
                    group.name
                        ?.charAt(0)
                        .toUpperCase()
                }}
            </div>


            <div class="flex-1 min-w-0">

                <div
                    class="flex justify-between gap-2"
                >

                    <h3
                        class="font-bold truncate"
                    >
                        {{ group.name }}
                    </h3>


                    <span
                        v-if="group.role === 'admin'"
                        class="text-xs text-indigo-600"
                    >
                        Admin
                    </span>

                    <span
                        v-else-if="
                            group.status !== 'approved'
                        "
                        class="text-xs text-gray-400"
                    >
                        Pending
                    </span>

                </div>


                <div
                    class="text-xs text-gray-500"
                >
                    {{ group.description }}
                </div>

            </div>

        </div>

    </div>

</template>