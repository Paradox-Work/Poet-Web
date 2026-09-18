<template>
  <AuthenticatedLayout>

    <div class="w-[960px] mx-auto h-full overflow-auto">
        <div class="relative bg-white">
            <img 
                src="https://imgsrv.crunchyroll.com/cdn-cgi/image/fit=cover,format=auto,quality=85,width=1920/keyart/GRE50KV36-backdrop_wide" 
                class="w-full h-[200px] object-cover object-top" 
            />
            <div class="flex">
              <img 
                  src="https://i.pinimg.com/736x/a6/72/05/a67205f60f44c386f4bdfb8fab4d8bed.jpg"
                  class="ml-[48px] w-[128px] h-[128px] -mt-[64px] rounded-full object-cover border-4 border-white shadow-lg"
              />
            
              <div class="flex justify-between items-center flex-1 p-4">
                <h3 class="font-bold text-lg">{{  user.name  }}</h3>
                <PrimaryButton v-if="isMyProfile">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                  </svg>

                  Edit Profile
                </PrimaryButton>
              </div>
            </div>
        </div>
        <div class="border-t">
          <TabGroup>
            <TabList class="pl-[200px] flex bg-white">
              <Tab
                v-if="isMyProfile"
                as="template"
                v-slot="{ selected }"
              >
                <TabItem text="About" :selected="selected" />
              </Tab>
              <Tab
                as="template"
                v-slot="{ selected }"
              >
                <TabItem text="Posts" :selected="selected" />
              </Tab>
              <Tab
                as="template"
                v-slot="{ selected }"
              >
                <TabItem text="Followers" :selected="selected" />
              </Tab>
              <Tab
                as="template"
                v-slot="{ selected }"
              >
                <TabItem text="Following" :selected="selected" />
              </Tab>
              <Tab
                as="template"
                v-slot="{ selected }"
              >
                <TabItem text="Photos" :selected="selected" />
              </Tab>
            </TabList>
           

            <TabPanels class="mt-2">
              <TabPanel
                class=""
                v-if="isMyProfile"
                >
                <Edit :must-verify-email="mustVerifyEmail" :status="status"/>
              </TabPanel>
              <TabPanel
                 class="bg-white p-3">
                Posts
              </TabPanel>
              <TabPanel
                 class="bg-white p-3">
                Followers
              </TabPanel>
              <TabPanel
                 class="bg-white p-3">
                Following
              </TabPanel>
              <TabPanel
                 class="bg-white p-3">
                Photos
              </TabPanel>
            </TabPanels>
          </TabGroup>
        </div>
    </div>
    
  </AuthenticatedLayout>
  
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { usePage } from '@inertiajs/vue3';
import TabItem from './Partials/TabItem.vue';
import Edit from './Edit.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
});

const authUser = usePage().props.auth.user;

const isMyProfile = computed(() =>
    authUser && authUser.id === props.user.id
);
</script>


<style>

</style>