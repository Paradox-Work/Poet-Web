<template>
  <AuthenticatedLayout>

    <div class="container mx-auto">
        <div class="relative">
            <img 
                src="https://imgsrv.crunchyroll.com/cdn-cgi/image/fit=cover,format=auto,quality=85,width=1920/keyart/GRE50KV36-backdrop_wide" 
                class="w-full h-[200px] object-cover object-top" 
            />
            <img 
                src="https://i.pinimg.com/736x/a6/72/05/a67205f60f44c386f4bdfb8fab4d8bed.jpg"
                class="absolute left-[48px] w-[128px] h-[128px] -bottom-[41px] rounded-full object-cover border-4 border-white shadow-lg"
            />
        </div>
        <div>
          <TabGroup>
            <TabList class="pl-[200px] flex bg-white">
              <Tab
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
                :key="posts" class="bg-white p-3">
                Posts
              </TabPanel>
              <TabPanel
                :key="followers" class="bg-white p-3">
                Followers
              </TabPanel>
            </TabPanels>
          </TabGroup>
        </div>
    </div>
    
  </AuthenticatedLayout>
  
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import { Link, useForm, usePage } from '@inertiajs/vue3';
import TabItem from './Partials/TabItem.vue';

const user = usePage().props.auth.user;

const categories = ref({
  Recent: [
    {
      id: 1,
      title: 'Does drinking coffee make you smarter?',
      date: '5h ago',
      commentCount: 5,
      shareCount: 2,
    },
    {
      id: 2,
      title: "So you've bought coffee... now what?",
      date: '2h ago',
      commentCount: 3,
      shareCount: 2,
    },
  ],
  Popular: [
    {
      id: 1,
      title: 'Is tech making coffee better or worse?',
      date: 'Jan 7',
      commentCount: 29,
      shareCount: 16,
    },
    {
      id: 2,
      title: 'The most innovative things happening in coffee',
      date: 'Mar 19',
      commentCount: 24,
      shareCount: 12,
    },
  ],
  Trending: [
    {
      id: 1,
      title: 'Ask Me Anything: 10 answers to your questions about coffee',
      date: '2d ago',
      commentCount: 9,
      shareCount: 5,
    },
    {
      id: 2,
      title: "The worst advice we've ever heard about coffee",
      date: '4d ago',
      commentCount: 1,
      shareCount: 2,
    },
  ],
})
</script>


<style>

</style>