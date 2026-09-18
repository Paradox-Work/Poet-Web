<template>
  <AuthenticatedLayout>

    <div class="w-[768px] mx-auto h-full overflow-auto">

    <div
        v-show="showNotification && status === 'cover-image-update'"
        class="my-2 py-2 px-3 font-medium text-sm bg-emerald-500 text-white"
    >
        Your cover image has been updated
    </div>
    <div
        v-if="errors.cover"
        class="my-2 py-2 px-3 font-medium text-sm bg-red-400 text-white"
    >
        {{ errors.cover }}
    </div>


    <div class="group relative bg-white">
            <img
                :src="coverImageSrc || user.cover_url || '/img/default_cover.jpg'"
                class="w-full h-[200px] object-cover"
            />
            <div class="absolute top-2 right-2">
    <button
        v-if="!coverImageSrc"
        class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center opacity-0 group-hover:opacity-100"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
        </svg>
        Update Cover Image
        <input
            type="file"
            class="absolute left-0 top-0 bottom-0 right-0 opacity-0"
            @change="onCoverChange"
        />
    </button>
    <div v-else class="flex gap-2 bg-white p-2 opacity-0 group-hover:opacity-100">
        <button
            @click="cancelCoverImage"
            class="bg-gray-50 hover:bg-gray-100 text-gray-800 py-1 px-2 text-xs flex items-center"
        >
            <XMarkIcon class="h-3 w-3 mr-2" />
            Cancel
        </button>
        <button
            @click="submitCoverImage"
            class="bg-gray-800 hover:bg-gray-900 text-gray-100 py-1 px-2 text-xs flex items-center"
        >
            <CheckCircleIcon class="h-3 w-3 mr-2" />
            Submit
        </button>
    </div>
</div>
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
            <TabList class="flex bg-white pl-4 md:pl-[200px]">
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
import { computed, ref } from 'vue';
import { XMarkIcon, CheckCircleIcon } from '@heroicons/vue/24/solid';
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue';
import { usePage, useForm } from '@inertiajs/vue3';
import TabItem from './Partials/TabItem.vue';
import Edit from './Edit.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    errors: Object,
    mustVerifyEmail: Boolean,
    status: String,
    user: Object,
});

const imagesForm = useForm({
    avatar: null,
    cover: null,
})

const showNotification = ref(true)
const coverImageSrc = ref('')


const authUser = usePage().props.auth.user;

const isMyProfile = computed(() =>
    authUser && authUser.id === props.user.id
);

function onCoverChange(event) {
    imagesForm.cover = event.target.files[0]
    if (imagesForm.cover) {
        const reader = new FileReader()
        reader.onload = () => {
            coverImageSrc.value = reader.result;
        }
        reader.readAsDataURL(imagesForm.cover)
    }
}

function cancelCoverImage() {
    imagesForm.cover = null;
    coverImageSrc.value = null
}

function submitCoverImage() {
    imagesForm.post(route('profile.updateCover'), {
        onSuccess: (user) => {
            cancelCoverImage()
            setTimeout(() => {
                showNotification.value = false
            }, 3000)
        },
    })
}

</script>


<style scoped>

</style>