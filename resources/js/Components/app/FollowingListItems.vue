<script setup>

    import FollowingItem from '@/Components/app/FollowingItem.vue';
    import TextInput from '@/Components/TextInput.vue';
    import { ref } from 'vue';

    const searchKeyword = ref(''); 

</script>

<template>
<TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
            <div class="mt-3 lg:min-h-full max-h-64 overflow-auto rounded border border-gray-100">
               <div v-if="false" class="text-gray-400 text-center p-3">
                  You havent befriended anyone yet.
               </div>
               <div v-else>
                  <FollowingItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                                 title="Shakspeare" />
                  <FollowingItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                                 title="Harry Potter" />
               </div>
            </div>
</template>

<style>

</style>