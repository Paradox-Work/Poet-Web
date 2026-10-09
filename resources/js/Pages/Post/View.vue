<script setup>
import {
    onMounted,
    ref
} from 'vue';

import {
    Head,
    router,
    usePage
} from '@inertiajs/vue3';

import {
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

import PostItem
    from '@/Components/app/PostItem.vue';

import PostModal
    from '@/Components/app/PostModal.vue';

import AttachmentPreviewModal
    from '@/Components/app/AttachmentPreviewModal.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true
    }
});

const authUser =
    usePage().props.auth.user;

const showEditModal =
    ref(false);

const showAttachmentsModal =
    ref(false);

const showMatureWarning =
    ref(false);

const editPost =
    ref({});

const previewAttachmentsPost =
    ref({
        post: null,
        index: 0
    });

onMounted(() => {
    if (
        props.post.content_rating !==
        'mature'
    ) {
        return;
    }

    showMatureWarning.value =
        localStorage.getItem(
            'poet_mature_content_allowed'
        ) !== 'true';
});

function continueToMatureContent() {
    localStorage.setItem(
        'poet_mature_content_allowed',
        'true'
    );

    showMatureWarning.value =
        false;
}

function leaveMatureContent() {
    if (
        window.history.length > 1
    ) {
        window.history.back();
        return;
    }

    router.visit(
        route('dashboard')
    );
}

function openEditModal(post) {
    editPost.value =
        post;

    showEditModal.value =
        true;
}

function openAttachmentPreviewModal(
    post,
    index
) {
    previewAttachmentsPost.value = {
        post,
        index
    };

    showAttachmentsModal.value =
        true;
}

function onModalHide() {
    editPost.value = {
        id: null,
        body: '',
        user: authUser
    };
}
</script>

<template>
    <Head title="Post" />

    <AuthenticatedLayout>
        <div
            v-if="showMatureWarning"
            class="flex h-full items-center justify-center px-4 py-8"
        >
            <section
                class="poet-card w-full max-w-lg p-6 text-center sm:p-8"
            >
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-[var(--poet-gold)]/50 bg-amber-500/10 text-[var(--poet-gold)]"
                >
                    <ExclamationTriangleIcon
                        class="h-6 w-6"
                    />
                </div>

                <div
                    class="poet-kicker mt-5"
                >
                    Mature publication
                </div>

                <h1
                    class="poet-title mt-2 text-2xl"
                >
                    Continue to this publication?
                </h1>

                <p
                    class="mx-auto mt-3 max-w-md text-sm leading-6 text-[var(--poet-muted)]"
                >
                    The author marked this publication as Mature. It may contain strong language or adult themes.
                </p>

                <p
                    class="mt-3 text-xs text-[var(--poet-muted)]"
                >
                    Continuing remembers your choice on this browser.
                </p>

                <div
                    class="mt-6 grid grid-cols-2 gap-3"
                >
                    <button
                        type="button"
                        @click="leaveMatureContent"
                        class="poet-focus rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] px-4 py-2.5 text-sm font-semibold text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                    >
                        Go back
                    </button>

                    <button
                        type="button"
                        @click="continueToMatureContent"
                        class="poet-focus rounded-full bg-[var(--poet-accent)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--poet-accent-strong)]"
                    >
                        Continue
                    </button>
                </div>
            </section>
        </div>

        <div
            v-else
            class="mx-auto max-w-2xl p-4"
        >
            <PostItem
                :post="post"
                show-hashtags
                dedicated-page
                @edit-click="
                    openEditModal
                "
                @attachment-click="
                    openAttachmentPreviewModal
                "
            />
        </div>

        <PostModal
            v-model="showEditModal"
            :post="editPost"
            @hide="
                onModalHide
            "
        />

        <AttachmentPreviewModal
            v-model="
                showAttachmentsModal
            "
            v-model:index="
                previewAttachmentsPost.index
            "
            :attachments="
                previewAttachmentsPost
                    .post
                    ?.attachments
                    ?? []
            "
        />
    </AuthenticatedLayout>
</template>
