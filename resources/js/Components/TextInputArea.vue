<script setup>
import { onMounted, ref } from 'vue';

// Vue 3.4+ two-way binding helper.
// `model` is a ref that syncs with the parent's v-model.
const model = defineModel({ type: String });

// Separate, normal props for everything else.
const props = defineProps({
    placeholder: String,
    autoResize: { type: Boolean, default: true },
});

// Placeholder for the DOM element. Filled after mount.
const input = ref(null);

onMounted(() => {
    // `?.` — if input.value is null, don't crash.
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
    resize(); // size it correctly on first render
});

// Allow the parent to call componentRef.focus()
defineExpose({ focus: () => input.value?.focus() });

// Auto-grow: reset to auto, then set to the content's height.
function resize() {
    if (!props.autoResize) return;
    if (!input.value) return;
    input.value.style.height = 'auto';
    input.value.style.height = input.value.scrollHeight + 'px';
}
</script>

<template>
    <!--
        v-model="model"  → two-way binding with parent's v-model
        @input="resize"  → run auto-grow on every keystroke
        ref="input"      → connect to the input ref above
        :placeholder     → from the props
    -->
    <textarea
        ref="input"
        v-model="model"
        @input="resize"
        :placeholder="props.placeholder"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
    ></textarea>
</template>