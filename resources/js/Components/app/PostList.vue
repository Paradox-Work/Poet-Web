<script setup>
import PostItem from '@/Components/app/PostItem.vue';
const post1 = {
    user: {
        id: 1,
        name: 'Daddy Yankee',
        avatar: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSfauMMomOBRtpLjU4JYa_DwETIsBpQpi9vrDfXyhHo0t7tLA_NMM1P94UUTQzbyf_NVncQPI-FzOPKmj9dKnBnxDsr5DRuXiiZUNxRArLTFg&s=10',
    },
    group: null,
    attachments: [
        {
            id: 1,
            name: 'image1.png',
            url: 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            mime: 'image/png',
        },
        {
            id: 2,
            name: 'image2.png',
            url: 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
            mime: 'image/png',
        },
        {
            id: 2,
            name: 'document.pdf',
            url: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            mime: 'application/pdf',
        }
    ],
    body: 'This is the first post.',
    created_at: '2023-06-01 10:00:00',

};
const post2 = {
    user: {
        id: 2,
        name: 'James Doakes',
        avatar: 'https://preview.redd.it/lets-agree-to-love-this-character-or-rather-is-there-anyone-v0-u7lozj3xywo91.jpg?width=640&crop=smart&auto=webp&s=469cbf940c204d3acb4a57baa4c288da0efa1c66',
    },
    group: {
        id: 1,
        name: 'Tech Enthusiasts'
    },
    body: 'This is the second post.',
    created_at: '2023-07-01 10:00:00',

};
</script>

<template>

    <div class="overflow-auto flex-1">
        <PostItem :post="post1" />
        <PostItem :post="post2" />
    </div>

</template>

<style scoped>

</style>