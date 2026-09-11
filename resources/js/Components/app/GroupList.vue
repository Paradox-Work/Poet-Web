<script setup>
   import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
   import GroupItem from '@/Components/app/GroupItem.vue';
   import TextInput from '@/Components/TextInput.vue';
   import { ref } from 'vue';

   const searchKeyword = ref(''); 
</script>

<template>
   <div class="px-3 bg-white border rounded py-3 mb-3  min-h-0 lg:h-full overflow-hidden flex flex-col">

      <!-- MOBILE: collapsible -->
      <Disclosure v-slot="{ open }" as="div" class="flex flex-col lg:hidden">
         <DisclosureButton class="text-left">
            <div class="flex justify-between">
               <h2 class="text-xl font-bold mb-4">My Groups</h2>
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 ui-open:rotate-90 ui-open:transform">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
               </svg>
            </div>
         </DisclosureButton>

         <!-- CHANGED: was "block flex-col min-h-0 flex-1" (block + flex conflict) -->
         <DisclosurePanel class="flex flex-col">
            <TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
            <div class="mt-3 max-h-64 overflow-auto rounded border border-gray-100">
               <div v-if="false" class="text-gray-400 text-center p-3">
                  You are not a member of any groups yet.
               </div>
               <div v-else>
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
                  <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             title="Group Name"
                             description="Group description" />
               </div>
            </div>
         </DisclosurePanel>
      </Disclosure>

      <!-- DESKTOP: always open -->
      <!-- CHANGED: was "hidden lg:block" (block can't carry the flex chain) -->
      <div class="hidden lg:flex flex-col min-h-0 flex-1">
         <h2 class="text-xl font-bold mb-4">My Groups</h2>
         <TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
         <!-- CHANGED: was "mt-3 flex-1 overflow-auto" (missing min-h-0) -->
         <div class="mt-3 flex-1 min-h-0 overflow-auto">
            <div v-if="false" class="text-gray-400 text-center p-3">
               You are not a member of any groups yet.
            </div>
            <div v-else>
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
               <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                          title="Group Name"
                          description="Group description" />
            </div>
         </div>
      </div>

   </div>
</template>

<style scoped>
</style>