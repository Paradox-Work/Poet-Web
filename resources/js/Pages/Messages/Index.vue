<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    conversations: {
        type: Array,
        default: () => [],
    },
    activeConversation: {
        type: Object,
        default: null,
    },
});

const { locale } = useI18n();
const messages = ref(props.activeConversation?.messages ?? []);
const body = ref('');
const sending = ref(false);
const listEl = ref(null);
let timer = null;

async function refreshMessages() {
    if (!props.activeConversation?.id) return;

    try {
        const response = await axios.get(
            route('messages.items', props.activeConversation.id)
        );

        messages.value = response.data.messages ?? [];
        await nextTick();
        scrollBottom();
    } catch {
        // Polling failure is intentionally silent; the next interval retries.
    }
}

async function send() {
    const value = body.value.trim();

    if (!value || !props.activeConversation?.id || sending.value) {
        return;
    }

    sending.value = true;

    try {
        const response = await axios.post(
            route('messages.store', props.activeConversation.id),
            { body: value }
        );

        messages.value.push(response.data.message);
        body.value = '';
        await nextTick();
        scrollBottom();
    } finally {
        sending.value = false;
    }
}

function scrollBottom() {
    if (listEl.value) {
        listEl.value.scrollTop = listEl.value.scrollHeight;
    }
}

function startPolling() {
    stopPolling();

    if (props.activeConversation?.id) {
        timer = window.setInterval(refreshMessages, 5000);
    }
}

function stopPolling() {
    if (timer) {
        window.clearInterval(timer);
        timer = null;
    }
}

watch(
    () => props.activeConversation?.id,
    () => {
        messages.value = props.activeConversation?.messages ?? [];
        startPolling();
        nextTick(scrollBottom);
    }
);

onMounted(() => {
    startPolling();
    nextTick(scrollBottom);
});

onBeforeUnmount(stopPolling);
</script>

<template>
    <Head :title="locale === 'lv' ? 'Ziņas' : 'Messages'" />

    <AuthenticatedLayout>
        <div class="h-full min-h-0 bg-[var(--poet-bg)] p-2 sm:p-6">
            <div class="mx-auto grid h-full min-h-0 max-w-6xl grid-cols-1 grid-rows-[minmax(0,1fr)] overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] md:grid-cols-[18rem_1fr]">
                <aside :class="activeConversation ? 'hidden md:block' : 'block'" class="min-h-0 overflow-y-auto border-b border-[var(--poet-border)] md:border-b-0 md:border-r md:overflow-hidden">
                    <div class="border-b border-[var(--poet-border)] px-4 py-4">
                        <h1 class="font-serif text-xl font-semibold text-[var(--poet-text)]">
                            {{ locale === 'lv' ? 'Tiešās ziņas' : 'Direct messages' }}
                        </h1>
                    </div>

                    <div class="overflow-y-auto md:max-h-none md:h-[calc(100%-4rem)]">
                        <Link
                            v-for="conversation in conversations"
                            :key="conversation.id"
                            :href="route('messages.show', conversation.id)"
                            :class="[
                                'flex items-center gap-3 border-b border-[var(--poet-border)] px-4 py-3 transition hover:bg-[var(--poet-surface-soft)]',
                                activeConversation?.id === conversation.id
                                    ? 'bg-[var(--poet-accent-soft)]'
                                    : ''
                            ]"
                        >
                            <img
                                :src="conversation.other_user?.avatar_url"
                                :alt="conversation.other_user?.name ?? ''"
                                class="h-10 w-10 rounded-full object-cover"
                            />

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-[var(--poet-text)]">
                                    {{ conversation.other_user?.name }}
                                </span>
                                <span class="block truncate text-xs text-[var(--poet-muted)]">
                                    {{ conversation.last_message || (locale === 'lv' ? 'Jauna saruna' : 'New conversation') }}
                                </span>
                            </span>

                            <span
                                v-if="conversation.unread_count"
                                class="rounded-full bg-[var(--poet-accent)] px-2 py-0.5 text-[10px] font-bold text-white"
                            >
                                {{ conversation.unread_count }}
                            </span>
                        </Link>

                        <div
                            v-if="!conversations.length"
                            class="p-6 text-center text-sm text-[var(--poet-muted)]"
                        >
                            {{ locale === 'lv' ? 'Sarunu vēl nav.' : 'No conversations yet.' }}
                        </div>
                    </div>
                </aside>

                <section v-if="activeConversation" class="flex h-full min-h-0 flex-col">
                    <header class="flex items-center gap-3 border-b border-[var(--poet-border)] px-4 py-3">
                        <Link :href="route('messages.show')" class="shrink-0 rounded-lg px-2 py-2 text-sm font-semibold text-[var(--poet-accent-strong)] md:hidden" :aria-label="locale === 'lv' ? 'Atpakaļ uz sarunām' : 'Back to conversations'">←</Link>
                        <img
                            :src="activeConversation.other_user?.avatar_url"
                            :alt="activeConversation.other_user?.name ?? ''"
                            class="h-10 w-10 rounded-full object-cover"
                        />
                        <div>
                            <div class="font-semibold text-[var(--poet-text)]">
                                {{ activeConversation.other_user?.name }}
                            </div>
                            <Link
                                :href="route('profile', activeConversation.other_user?.username)"
                                class="text-xs text-[var(--poet-accent-strong)]"
                            >
                                @{{ activeConversation.other_user?.username }}
                            </Link>
                        </div>
                    </header>

                    <div ref="listEl" class="min-h-0 flex-1 space-y-3 overflow-y-auto px-4 py-4">
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="[
                                'flex',
                                message.user?.id === $page.props.auth.user.id
                                    ? 'justify-end'
                                    : 'justify-start'
                            ]"
                        >
                            <div
                                :class="[
                                    'max-w-[80%] rounded-2xl px-4 py-2 text-sm leading-6',
                                    message.user?.id === $page.props.auth.user.id
                                        ? 'bg-[var(--poet-accent)] text-white'
                                        : 'bg-[var(--poet-surface-soft)] text-[var(--poet-text)]'
                                ]"
                            >
                                <p class="whitespace-pre-wrap break-words">{{ message.body }}</p>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="send" class="flex gap-2 border-t border-[var(--poet-border)] p-3">
                        <textarea
                            v-model="body"
                            rows="1"
                            maxlength="5000"
                            :placeholder="locale === 'lv' ? 'Raksti ziņu...' : 'Write a message...'"
                            class="min-h-11 flex-1 resize-none rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-bg)] text-[var(--poet-text)]"
                            @keydown.enter.exact.prevent="send"
                        />
                        <button
                            type="submit"
                            :disabled="sending || !body.trim()"
                            class="rounded-2xl bg-[var(--poet-accent)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-40"
                        >
                            {{ locale === 'lv' ? 'Sūtīt' : 'Send' }}
                        </button>
                    </form>
                </section>

                <section v-else class="hidden items-center justify-center p-8 text-center text-[var(--poet-muted)] md:flex">
                    {{
                        locale === 'lv'
                            ? 'Izvēlies sarunu vai atver autora profilu un sāc jaunu ziņu.'
                            : 'Choose a conversation, or open a writer profile to start a new message.'
                    }}
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
