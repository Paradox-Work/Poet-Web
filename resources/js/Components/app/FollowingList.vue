<script setup>
   import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
   import FollowingListItems from './FollowingListItems.vue';
   
</script>

<template>
   <div class="px-3 bg-white border rounded py-3 lg:h-full overflow-hidden flex flex-col">

      <!-- MOBILE: collapsible -->
      <Disclosure v-slot="{ open }" as="div" class="flex flex-col lg:hidden">
         <DisclosureButton class="text-left">
            <div class="flex justify-between">
            <h2 class="text-xl font-bold mb-4">Following</h2>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 transition-transform ui-open:rotate-90">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
               </svg>
            </div>
         </DisclosureButton>

         <DisclosurePanel class="flex flex-col">
            <FollowingListItems />
         </DisclosurePanel>
      </Disclosure>

      <!-- DESKTOP: always open -->
      <div class="hidden lg:flex flex-col min-h-0 flex-1">
         <h2 class="text-xl font-bold mb-4">Following</h2>
         <FollowingListItems />
      </div>

   </div>
</template>

<style scoped>
</style>