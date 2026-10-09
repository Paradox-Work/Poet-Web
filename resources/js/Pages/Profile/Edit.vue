<script setup>
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
        <div class="space-y-4 sm:space-y-6">
            <div
                class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] p-4 sm:p-6"
            >
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div
                class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] p-4 sm:p-6"
            >
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div
                class="rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface-soft)] p-4 sm:p-6"
            >
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
</template>
