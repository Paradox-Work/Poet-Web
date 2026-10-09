<script setup>
import {
    Link
} from '@inertiajs/vue3';

defineProps({
    user: {
        type: Object,
        required: true
    },

    showActions: {
        type: Boolean,
        default: false
    },

    processing: {
        type: Boolean,
        default: false
    },

    showRoleControl: {
        type: Boolean,
        default: false
    },

    isOwner: {
        type: Boolean,
        default: false
    },

    roleProcessing: {
        type: Boolean,
        default: false
    },

    cardMode: {
        type: Boolean,
        default: false
    }
});

defineEmits([
    'approve',
    'reject',
    'role-change',
    'remove'
]);
</script>


<template>
    <div
        :class="[
            'flex min-w-0 items-center gap-2 px-2.5 py-2.5 dark:text-gray-100 sm:gap-3 sm:px-3 sm:py-3',
            cardMode
                ? 'rounded-xl border border-[var(--poet-border)] bg-[var(--poet-surface)] transition hover:border-[var(--poet-border-strong)] hover:bg-[var(--poet-surface-soft)]'
                : 'border-b border-gray-100 last:border-b-0 dark:border-gray-700'
        ]"
    >
        <Link
            :href="
                route(
                    'profile',
                    { username: user.username }
                )
            "
            class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3"
        >
            <img
                :src="
                    user.avatar_url ||
                    '/img/default_avatar.svg'
                "
                :alt="user.name"
                class="h-9 w-9 shrink-0 rounded-full object-cover sm:h-10 sm:w-10"
            />

            <div class="min-w-0">
                <div
                    class="truncate text-xs font-medium text-gray-900 dark:text-gray-100 sm:text-sm"
                >
                    {{ user.name }}
                </div>

                <div
                    class="truncate text-[11px] text-gray-500 dark:text-gray-400 sm:text-sm"
                >
                    @{{ user.username }}
                </div>
            </div>
        </Link>


        <div
            v-if="showActions"
            class="flex gap-2"
        >
            <button
                type="button"
                :disabled="processing"
                @click="$emit('approve', user)"
                class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm text-white hover:bg-emerald-500 disabled:opacity-50"
            >
                Approve
            </button>

            <button
                type="button"
                :disabled="processing"
                @click="$emit('reject', user)"
                class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-500 disabled:opacity-50"
            >
                Reject
            </button>
        </div>
        <div
            v-if="showRoleControl"
            class="ml-2 flex items-center gap-2"
        >
            <span
                v-if="isOwner"
                class="rounded-md bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700"
            >
                Owner
            </span>

            <select
                v-else
                :value="user.role"
                :disabled="roleProcessing"
                @change="
                    $emit(
                        'role-change',
                        user,
                        $event.target.value
                    )
                "
                class="rounded-md border-gray-300 py-1.5 text-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            >
                <option value="member">
                    Member
                </option>

                <option value="admin">
                    Admin
                </option>
            </select>

            <button
                type="button"
                :disabled="roleProcessing"
                @click="$emit('remove', user)"
                class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-500 disabled:opacity-50"
            >
                Remove
            </button>

        </div>
    </div>
</template>