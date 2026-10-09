<script setup>
import {
    onMounted,
    ref
} from 'vue';

const model = defineModel({
    type: String,
    required: true,
    placeholder: String,
});

const input = ref(null);

onMounted(() => {
    if (
        input.value.hasAttribute(
            'autofocus'
        )
    ) {
        input.value.focus();
    }
});

defineExpose({
    focus: () =>
        input.value.focus()
});
</script>

<template>
    <input
        v-model="model"
        ref="input"
        :placeholder="placeholder"
        class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface)] text-[var(--poet-text)] shadow-sm placeholder:text-[var(--poet-muted)] focus:border-[var(--poet-accent)] focus:ring-[var(--poet-accent)]"
    />
</template>
