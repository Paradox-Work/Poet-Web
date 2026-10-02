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
    }
});

defineEmits([
    'approve',
    'reject'
]);
</script>


<template>
    <div
        class="flex items-center gap-3 border-b border-gray-100 px-3 py-3 last:border-b-0"
    >
        <Link
            :href="
                route(
                    'profile',
                    { username: user.username }
                )
            "
            class="flex min-w-0 flex-1 items-center gap-3"
        >
            <img
                :src="
                    user.avatar_url ||
                    '/img/default_avatar.webp'
                "
                :alt="user.name"
                class="h-10 w-10 rounded-full object-cover"
            />

            <div class="min-w-0">
                <div
                    class="truncate font-medium text-gray-900"
                >
                    {{ user.name }}
                </div>

                <div
                    class="truncate text-sm text-gray-500"
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
    </div>
</template>