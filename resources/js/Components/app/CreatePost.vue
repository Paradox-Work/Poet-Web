<script setup>
import { ref } from 'vue';
import TextInputArea from '../TextInputArea.vue';
import { useForm } from '@inertiajs/vue3';

const postCreating = ref(false); 
const newPostForm = useForm({
    body: ''
})

function submit(){
    newPostForm.post(route('post.create'), {
        onSuccess: () => {
            newPostForm.reset()
        }
    })
}

</script>

<template>

<div>
    <div class="bg-white border rounded p-4 mb-3">

        <TextInputArea @click="postCreating = true" 
            class="mb-3 w-full" 
            placeholder="Click here to create a new post" 
            rows="1" 
            v-model="newPostForm.body"
        />
        <pre>{{newPostForm.body}}</pre>
        <div v-if="postCreating" class="flex gap-2 justify-between">
            <button type="button" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 relative">
                Attach files
                <input type="file" class="absolute inset-0 w-full h-full opacity-0" />
            </button>
            <button @click="submit" type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                Save
            </button>
        </div>
    </div>
</div>

</template>

<style scoped>

</style>