<script setup>
import {
    Head,
    router
} from '@inertiajs/vue3';

import {
    CheckIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    invitation: {
        type: Object,
        required: true
    }
});

function accept() {
    router.post(
        props.invitation.accept_url
    );
}

function decline() {
    router.post(
        props.invitation.decline_url
    );
}
</script>

<template>
    <Head title="Group invitation" />

    <AuthenticatedLayout>
        <div
            class="flex h-full items-center justify-center px-4 py-8"
        >
            <section
                class="poet-card w-full max-w-lg p-6 text-center sm:p-8"
            >
                <div
                    class="poet-kicker"
                >
                    Group invitation
                </div>

                <h1
                    class="poet-title mt-2 text-2xl"
                >
                    Join
                    {{ invitation.group.name }}?
                </h1>

                <p
                    class="mx-auto mt-3 max-w-md text-sm leading-6 text-[var(--poet-muted)]"
                >
                    You were invited to become a member of this writing space.
                    Accepting will add you to the group.
                </p>

                <div
                    class="mt-6 grid grid-cols-2 gap-3"
                >
                    <button
                        type="button"
                        @click="decline"
                        class="poet-focus inline-flex items-center justify-center gap-2 rounded-full border border-[var(--poet-border)] bg-[var(--poet-surface)] px-4 py-2.5 text-sm font-semibold text-[var(--poet-text)] transition hover:bg-[var(--poet-surface-soft)]"
                    >
                        <XMarkIcon
                            class="h-4 w-4"
                        />
                        Decline
                    </button>

                    <button
                        type="button"
                        @click="accept"
                        class="poet-focus inline-flex items-center justify-center gap-2 rounded-full bg-[var(--poet-accent)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--poet-accent-strong)]"
                    >
                        <CheckIcon
                            class="h-4 w-4"
                        />
                        Accept
                    </button>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
