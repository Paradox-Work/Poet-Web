<script setup>
    
    defineProps({
        selected: Boolean,
        text: String,
    })
    
</script>

<template>

    <button
                  :class="[
                    'px-3 py-2.5 outline-none text-sm',
                    '',
                    selected
                      ? 'bg-white text-blue-500 border-b-2 border-blue-500 dark:bg-gray-800 dark:text-blue-400'
                      : 'text-gray-700 dark:text-gray-300',
                  ]"
                >
                  {{text}}
    </button>

</template>

<style scoped>

</style>