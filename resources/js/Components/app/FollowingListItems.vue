<script setup>

    import FollowingItem from '@/Components/app/FollowingItem.vue';
    import TextInput from '@/Components/TextInput.vue';
    import { ref } from 'vue';

    const searchKeyword = ref(''); 

    defineProps({
       following: {
          type: Array,
          default: () => [],
       },
    });

</script>

<template>
<TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
            <div class="mt-3 lg:min-h-full max-h-64 overflow-auto rounded border border-gray-100">
               <div v-if="following.length === 0" class="text-gray-400 text-center p-3">
                  You havent befriended anyone yet.
               </div>
               <div v-else>
                  <FollowingItem v-for="user in following" :key="user.id" :image="user.avatar"
                                 :title="user.name" />
               </div>
            </div>
</template>

<style>

</style>