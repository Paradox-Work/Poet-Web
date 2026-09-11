<script setup>
   import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
   import FollowingItem from '@/Components/app/FollowingItem.vue';
   import TextInput from '@/Components/TextInput.vue';
   import { ref } from 'vue';

   const searchKeyword = ref(''); 
</script>

<template>
   <div class="px-3 bg-white border rounded py-3 lg:h-full overflow-hidden flex flex-col">

      <!-- MOBILE: collapsible -->
      <Disclosure v-slot="{ open }" as="div" class="flex flex-col lg:hidden">
         <DisclosureButton class="text-left">
            <h2 class="text-xl font-bold mb-4">Following</h2>
         </DisclosureButton>

         <DisclosurePanel class="flex flex-col">
            <TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
            <div class="mt-3 max-h-64 overflow-auto rounded border border-gray-100">
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
         </DisclosurePanel>
      </Disclosure>

      <!-- DESKTOP: always open -->
      <div class="hidden lg:flex flex-col min-h-0 flex-1">
         <h2 class="text-xl font-bold mb-4">Following</h2>
         <TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
         <div class="mt-3 flex-1 min-h-0 overflow-auto">
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
      </div>

   </div>
</template>

<style scoped>
</style>