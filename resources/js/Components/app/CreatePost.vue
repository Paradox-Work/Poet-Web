<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const postCreating = ref(false); 
const form = useForm({
    title: '',
    body: '',
});

function submit() {
    form.post(route('posts.store'), {
        onSuccess: () => {
            form.reset();
            postCreating.value = false;
        },
    });
}
</script>

<template>

<div>
    <div class="bg-white border rounded p-4 mb-3">
        <div v-if="!postCreating" @click="postCreating = true" class="py-3 px-2 text-gray-400 border border-2 border-gray-200 rounded mb-3">
            Click here to create a new post
        </div>
        <form v-if="postCreating" @submit.prevent="submit" class="space-y-3">
            <div>
                <TextInput v-model="form.title" type="text" class="w-full" placeholder="Title" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>
            <div>
                <textarea v-model="form.body" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="What would you like to share?"></textarea>
                <InputError :message="form.errors.body" class="mt-2" />
            </div>
            <div class="flex justify-end gap-2">
                <PrimaryButton type="submit" :disabled="form.processing">Save</PrimaryButton>
            </div>
        </form>
    </div>
</div>

</template>

<style scoped>

</style>