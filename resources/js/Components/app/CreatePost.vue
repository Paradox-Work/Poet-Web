<script setup>
import { ref } from 'vue';
import TextInput from '../TextInput.vue';

const postCreating = ref(false); 
</script>

<template>

<div>
    <div class="bg-white border rounded p-4 mb-3">
        <textarea @click="postCreating = true" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 mb-3 w-full" placeholder="Click here to create a new post">
           
        </textarea>
        <div v-if="postCreating" class="flex gap-2 justify-between">
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 relative">
                Attach files
                <input type="file" class="absolute inset-0 w-full h-full opacity-0" />
            </button>
            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Save
            </button>
        </div>
    </div>
</div>

</template>

<style scoped>

</style>