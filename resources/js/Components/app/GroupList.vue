<script setup>
   import GroupItem from '@/Components/app/GroupItem.vue';
   import TextInput from '@/Components/TextInput.vue';
   import { ref } from 'vue';

   const searchKeyword = ref(''); 
</script>

<template>

            <div class="px-3 bg-white border rounded py-3 mb-3 h-[400px] overflow-hidden flex flex-col">
            <h2 class="text-xl font-bold mb-4">My Groups</h2>
            <TextInput :model-value="searchKeyword" placeholder="Type to search..." class="w-full"/>
            <div class="mt-3 flex-1 overflow-auto">
                <div v-if="false" class="text-gray-400 text-center p-3">
                    You are not a member of any groups yet.
                </div>
                  <div v-else>
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                    <GroupItem image="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" 
                               title="Group Name" 
                               description="Group description" 
                            />
                </div>
            </div>
            </div>
</template>

<style scoped>

</style>