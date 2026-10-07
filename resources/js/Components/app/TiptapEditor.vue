<script setup>
import { watch } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';


const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});


const emit = defineEmits([
    'update:modelValue'
]);


const editor = useEditor({
    content: props.modelValue,

    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3]
            }
        })
    ],

    onUpdate: ({ editor }) => {
        emit(
            'update:modelValue',
            editor.getHTML()
        );
    }
});


watch(
    () => props.modelValue,
    (value) => {

        if (!editor.value) {
            return;
        }

        const currentContent = editor.value.getHTML();

        if (currentContent === value) {
            return;
        }

        editor.value.commands.setContent(
            value || '',
            {
                emitUpdate: false
            }
        );
    }
);


function setLink() {

    if (!editor.value) {
        return;
    }

    const previousUrl =
        editor.value.getAttributes('link').href ?? '';

    const url = window.prompt(
        'Enter link URL',
        previousUrl
    );

    if (url === null) {
        return;
    }

    if (url === '') {
        editor.value
            .chain()
            .focus()
            .extendMarkRange('link')
            .unsetLink()
            .run();

        return;
    }

    editor.value
        .chain()
        .focus()
        .extendMarkRange('link')
        .setLink({
            href: url
        })
        .run();
}
</script>


<template>

    <div
        v-if="editor"
        class="tiptap-editor"
    >

        <div class="tiptap-toolbar">

            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ active: editor.isActive('bold') }"
            >
                Bold
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ active: editor.isActive('italic') }"
            >
                Italic
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleUnderline().run()"
                :class="{ active: editor.isActive('underline') }"
            >
                Underline
            </button>


            <span class="divider"></span>


            <button
                type="button"
                @click="editor.chain().focus().setParagraph().run()"
                :class="{ active: editor.isActive('paragraph') }"
            >
                P
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="{ active: editor.isActive('heading', { level: 1 }) }"
            >
                H1
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ active: editor.isActive('heading', { level: 2 }) }"
            >
                H2
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ active: editor.isActive('heading', { level: 3 }) }"
            >
                H3
            </button>


            <span class="divider"></span>


            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ active: editor.isActive('bulletList') }"
            >
                • List
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ active: editor.isActive('orderedList') }"
            >
                1. List
            </button>

            <button
                type="button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ active: editor.isActive('blockquote') }"
            >
                Quote
            </button>

            <button
                type="button"
                @click="setLink"
                :class="{ active: editor.isActive('link') }"
            >
                Link
            </button>


            <span class="divider"></span>


            <button
                type="button"
                @click="editor.chain().focus().undo().run()"
                :disabled="!editor.can().chain().focus().undo().run()"
            >
                Undo
            </button>

            <button
                type="button"
                @click="editor.chain().focus().redo().run()"
                :disabled="!editor.can().chain().focus().redo().run()"
            >
                Redo
            </button>

        </div>


        <EditorContent
            :editor="editor"
            class="tiptap-content"
        />

    </div>

</template>


<style scoped>

.tiptap-editor {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    overflow: hidden;
}

.tiptap-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;

    padding: 0.5rem;

    border-bottom: 1px solid #e5e7eb;

    background: #f9fafb;
}

.tiptap-toolbar button {
    padding: 0.35rem 0.55rem;

    border-radius: 0.25rem;

    font-size: 0.875rem;

    transition: background 0.15s;
}

.tiptap-toolbar button:hover {
    background: #e5e7eb;
}

.tiptap-toolbar button.active {
    background: #4f46e5;
    color: white;
}

.tiptap-toolbar button:disabled {
    opacity: 0.4;
}

.divider {
    width: 1px;

    margin: 0 0.25rem;

    background: #d1d5db;
}


:deep(.ProseMirror) {
    min-height: 180px;

    padding: 0.75rem;

    outline: none;
}

:deep(.ProseMirror p) {
    margin: 0.5rem 0;
}

:deep(.ProseMirror h1) {
    margin: 0.6rem 0;

    font-size: 2rem;
    font-weight: 700;
}

:deep(.ProseMirror h2) {
    margin: 0.6rem 0;

    font-size: 1.5rem;
    font-weight: 700;
}

:deep(.ProseMirror h3) {
    margin: 0.6rem 0;

    font-size: 1.25rem;
    font-weight: 700;
}

:deep(.ProseMirror ul) {
    padding-left: 2rem;

    list-style: disc;
}

:deep(.ProseMirror ol) {
    padding-left: 2rem;

    list-style: decimal;
}

:deep(.ProseMirror blockquote) {
    margin: 0.75rem 0;

    padding-left: 1rem;

    border-left: 4px solid #d1d5db;

    font-style: italic;
}

:deep(.ProseMirror a) {
    color: #2563eb;

    text-decoration: underline;
}

</style>