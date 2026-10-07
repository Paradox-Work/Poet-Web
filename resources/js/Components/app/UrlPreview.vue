<script setup>
defineProps({
    preview: {
        type: Object,
        default: null
    },

    url: {
        type: String,
        default: null
    }
});
</script>


<template>

    <a
        v-if="
            url &&
            preview?.title
        "
        :href="url"
        target="_blank"
        rel="noopener noreferrer"
        class="mt-4 block overflow-hidden rounded-md border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 transition"
    >

        <img
            v-if="preview.image"
            :src="preview.image"
            :alt="preview.title"
            class="max-h-64 w-full object-cover"
        />

        <div class="p-3">

            <h3
                class="font-semibold text-gray-900"
            >
                {{ preview.title }}
            </h3>

            <p
                v-if="preview.description"
                class="mt-1 text-sm text-gray-600"
            >
                {{ preview.description }}
            </p>

            <small
                class="mt-2 block truncate text-gray-500"
            >
                {{ url }}
            </small>

        </div>

    </a>


    <a
        v-else-if="url"
        :href="url"
        target="_blank"
        rel="noopener noreferrer"
        class="mt-4 block rounded-md border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-700 hover:bg-indigo-100"
    >
        {{ url }}
    </a>

</template>