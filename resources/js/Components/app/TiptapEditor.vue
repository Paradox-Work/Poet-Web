<script setup>
import {
    ref,
    watch,
    onMounted,
    onBeforeUnmount
} from 'vue';

import GhostText
    from '@/Extensions/GhostText.js';

import WritingSuggestionWorker
    from '@/workers/writingSuggestion.worker.js?worker&inline';

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

const suggestionsEnabled =
    ref(false);

const modelReady =
    ref(false);

const modelLoading =
    ref(false);

const suggestionError =
    ref(null);

const webGpuSupported =
    ref(false);


let suggestionWorker =
    null;

let suggestionTimer =
    null;

let requestCounter =
    0;

let latestRequest =
    0;

const editor = useEditor({
    content: props.modelValue,

 extensions: [

    StarterKit.configure({
        heading: {
            levels: [1, 2, 3]
        }
    }),

    GhostText

],

    onUpdate: ({ editor }) => {

        emit(
            'update:modelValue',
            editor.getHTML()
        );


        scheduleSuggestion(
            editor
        );
    },
});

onSelectionUpdate: ({
    editor
}) => {

    clearTimeout(
        suggestionTimer
    );


    editor.commands
        .clearGhostText();
}

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

function getContext(
    currentEditor
) {

    const {
        state
    } = currentEditor;


    if (
        !state.selection.empty
    ) {

        return '';
    }


    const textBeforeCursor =
        state.doc.textBetween(
            0,
            state.selection.from,
            '\n',
            '\n'
        );


    /*
     * Autocomplete does not need the
     * entire poem.
     *
     * Give the local model only recent
     * context.
     */
    return textBeforeCursor
        .slice(-500);
}


function scheduleSuggestion(
    currentEditor
) {

    clearTimeout(
        suggestionTimer
    );


    currentEditor.commands
        .clearGhostText();


    if (
        !suggestionsEnabled.value ||
        !modelReady.value
    ) {

        return;
    }


    suggestionTimer =
        setTimeout(
            () => {

                requestSuggestion(
                    currentEditor
                );

            },
            850
        );
}


function requestSuggestion(
    currentEditor
) {

    const context =
        getContext(
            currentEditor
        );


    if (
        context.trim()
            .length < 8
    ) {

        return;
    }


    const requestId =
        ++requestCounter;


    latestRequest =
        requestId;


    suggestionWorker
        ?.postMessage({

            type:
                'generate',

            requestId,

            context

        });
}


function toggleSuggestions() {

    suggestionError.value =
        null;


    if (
        !webGpuSupported.value
    ) {

        suggestionError.value =
            'WebGPU is not supported by this browser.';

        return;
    }


    suggestionsEnabled.value =
        !suggestionsEnabled.value;


    editor.value?.commands
        .clearGhostText();


    clearTimeout(
        suggestionTimer
    );


    if (
        suggestionsEnabled.value &&
        !modelReady.value
    ) {

        modelLoading.value =
            true;


        suggestionWorker
            ?.postMessage({
                type:
                    'load'
            });
    }
}


onMounted(
    () => {

        webGpuSupported.value =
            Boolean(
                navigator.gpu
            );


        if (
            !webGpuSupported.value
        ) {

            return;
        }


        suggestionWorker =
            new WritingSuggestionWorker();


        suggestionWorker.onmessage =
            event => {

                const data =
                    event.data;


                if (
                    data.type ===
                    'ready'
                ) {

                    modelReady.value =
                        true;

                    modelLoading.value =
                        false;


                    if (
                        editor.value
                    ) {

                        scheduleSuggestion(
                            editor.value
                        );
                    }


                    return;
                }


                if (
                    data.type ===
                    'suggestion'
                ) {

                    /*
                     * Ignore an old result if
                     * the user typed again while
                     * the model was working.
                     */
                    if (
                        data.requestId !==
                        latestRequest
                    ) {

                        return;
                    }


                    if (
                        !suggestionsEnabled.value ||
                        !editor.value
                    ) {

                        return;
                    }


                    const currentContext =
                        getContext(
                            editor.value
                        );


                    if (
                        currentContext !==
                        data.context
                    ) {

                        return;
                    }


                    if (
                        data.suggestion
                    ) {

                        editor.value
                            .commands
                            .setGhostText(
                                data.suggestion
                            );
                    }


                    return;
                }


                if (
                    data.type ===
                    'error'
                ) {

                    modelLoading.value =
                        false;

                    suggestionError.value =
                        data.message;
                }

            };

    }
);


onBeforeUnmount(
    () => {

        clearTimeout(
            suggestionTimer
        );


        suggestionWorker
            ?.terminate();

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

            <span class="divider"></span>


            <button
                type="button"
                @click="toggleSuggestions"
                :class="{
                    active:
                        suggestionsEnabled
                }"
                :disabled="
                    !webGpuSupported ||
                    modelLoading
                "
            >

                <template
                    v-if="modelLoading"
                >
                    Loading AI...
                </template>

                <template v-else>

                    {{
                        suggestionsEnabled
                            ? 'Suggestions On'
                            : 'Local Suggestions'
                    }}

                </template>

            </button>

        </div>

        <div
            v-if="
                suggestionsEnabled ||
                suggestionError
            "
            class="local-ai-status"
        >

            <span
                v-if="
                    suggestionsEnabled &&
                    modelReady
                "
            >
                Local suggestions enabled.
                Tab accepts · Esc dismisses.
                Draft text stays on this device.
            </span>


            <span
                v-else-if="
                    modelLoading
                "
            >
                Downloading and loading the local writing model…
            </span>


            <span
                v-if="
                    suggestionError
                "
                class="local-ai-error"
            >
                {{ suggestionError }}
            </span>

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

:deep(.ghost-text-suggestion) {
    color: #9ca3af;
    opacity: 0.8;
    pointer-events: none;
    user-select: none;
}

.local-ai-status {
    padding: 0.4rem 0.75rem;

    border-bottom: 1px solid #e5e7eb;

    background: #fafafa;

    color: #6b7280;

    font-size: 0.75rem;
}

.local-ai-error {
    margin-left: 0.5rem;

    color: #dc2626;
}
</style>