<script setup>
import GroupItem from '@/Components/app/GroupItem.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue';

const searchKeyword = ref(''); 
</script>

<template>
<TextInput v-model="searchKeyword" placeholder="Type to search..." class="w-full"/>
            <div class="mt-3 lg:min-h-full max-h-64 overflow-auto rounded border border-gray-100">
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
</template>

<style>

</style>