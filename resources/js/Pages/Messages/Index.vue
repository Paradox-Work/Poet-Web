<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    conversations: { type: Array, default: () => [] },
    activeConversation: { type: Object, default: null },
    messages: { type: Array, default: () => [] },
    search: { type: String, default: '' },
    userMatches: { type: Array, default: () => [] },
});

const { locale } = useI18n();
const authUser = computed(() => usePage().props.auth.user);
const localMessages = ref([...props.messages]);
const body = ref('');
const sending = ref(false);
const searchInput = ref(props.search ?? '');
const messagePane = ref(null);
let pollTimer = null;

const otherUser = computed(
    () => props.activeConversation?.users?.[0] ?? null
);

const lastMessageId = computed(() =>
    localMessages.value.length
        ? localMessages.value[localMessages.value.length - 1].id
        : 0
);

function scrollToBottom() {
    nextTick(() => {
        if (messagePane.value) {
            messagePane.value.scrollTop = messagePane.value.scrollHeight;
        }
    });
}

async function sendMessage() {
    const text = body.value.trim();

    if (!text || sending.value || !props.activeConversation) {
        return;
    }

    sending.value = true;

    try {
        const { data } = await axios.post(
            route('messages.send', props.activeConversation.id),
            { body: text }
        );

        localMessages.value.push(data);
        body.value = '';
        scrollToBottom();
    } finally {
        sending.value = false;
    }
}

async function poll() {
    if (!props.activeConversation) {
        return;
    }

    try {
        const { data } = await axios.get(
            route('messages.poll', props.activeConversation.id),
            { params: { after: lastMessageId.value } }
        );

        if (data.messages?.length) {
            const known = new Set(localMessages.value.map(item => item.id));

            for (const message of data.messages) {
                if (!known.has(message.id)) {
                    localMessages.value.push(message);
                }
            }

            scrollToBottom();
        }
    } catch (error) {
        console.error('Message polling failed:', error);
    }
}

function searchUsers() {
    router.get(
        route('messages.index'),
        searchInput.value.trim() ? { q: searchInput.value.trim() } : {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

function startConversation(user) {
    router.post(route('messages.start', user.id));
}

watch(
    () => props.messages,
    value => {
        localMessages.value = [...value];
        scrollToBottom();
    }
);

onMounted(() => {
    scrollToBottom();
    pollTimer = window.setInterval(poll, 2000);
});

onBeforeUnmount(() => {
    if (pollTimer) {
        window.clearInterval(pollTimer);
    }
});
</script>

<template>
    <Head :title="locale === 'lv' ? 'Ziņas' : 'Messages'" />

    <AuthenticatedLayout>
        <div class="mx-auto flex h-full min-h-0 w-full max-w-6xl flex-col px-3 py-4 sm:px-5">
            <header class="mb-4 shrink-0">
                <div class="poet-kicker">
                    {{ locale === 'lv' ? 'Tiešās ziņas' : 'Direct messages' }}
                </div>
                <h1 class="poet-title mt-1 text-3xl">
                    {{ locale === 'lv' ? 'Sarunas' : 'Conversations' }}
                </h1>
                <p class="mt-2 text-sm text-[var(--poet-muted)]">
                    {{
                        locale === 'lv'
                            ? 'Ziņas tiek automātiski atjaunotas ik pēc dažām sekundēm.'
                            : 'Messages refresh automatically every few seconds.'
                    }}
                </p>
            </header>

            <div class="grid min-h-0 flex-1 overflow-hidden rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] md:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="min-h-0 overflow-y-auto border-b border-[var(--poet-border)] p-3 md:border-b-0 md:border-r">
                    <form class="mb-3 flex gap-2" @submit.prevent="searchUsers">
                        <input
                            v-model="searchInput"
                            type="search"
                            :placeholder="locale === 'lv' ? 'Meklēt lietotāju...' : 'Search a user...'"
                            class="min-w-0 flex-1 rounded-full border-[var(--poet-border)] bg-[var(--poet-bg)] text-sm text-[var(--poet-text)]"
                        />
                        <button
                            type="submit"
                            class="rounded-full bg-[var(--poet-accent)] px-3 py-2 text-xs font-semibold text-white"
                        >
                            {{ locale === 'lv' ? 'Meklēt' : 'Search' }}
                        </button>
                    </form>

                    <div v-if="userMatches.length" class="mb-4 space-y-1 border-b border-[var(--poet-border)] pb-3">
                        <button
                            v-for="user in userMatches"
                            :key="user.id"
                            type="button"
                            class="flex w-full items-center gap-2 rounded-xl px-2 py-2 text-left transition hover:bg-[var(--poet-surface-soft)]"
                            @click="startConversation(user)"
                        >
                            <img :src="user.avatar_url" class="h-9 w-9 rounded-full object-cover" alt="" />
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold">{{ user.name }}</span>
                                <span class="block truncate text-xs text-[var(--poet-muted)]">@{{ user.username }}</span>
                            </span>
                        </button>
                    </div>

                    <div class="space-y-1">
                        <Link
                            v-for="conversation in conversations"
                            :key="conversation.id"
                            :href="route('messages.show', conversation.id)"
                            :class="[
                                'flex items-center gap-3 rounded-xl p-3 transition',
                                activeConversation?.id === conversation.id
                                    ? 'bg-[var(--poet-accent-soft)]'
                                    : 'hover:bg-[var(--poet-surface-soft)]'
                            ]"
                        >
                            <img
                                :src="conversation.other_user?.avatar_url || '/img/default_avatar.svg'"
                                class="h-11 w-11 rounded-full object-cover"
                                alt=""
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-semibold">
                                        {{ conversation.other_user?.name || 'User' }}
                                    </span>
                                    <span
                                        v-if="conversation.unread_count"
                                        class="rounded-full bg-[var(--poet-accent)] px-2 py-0.5 text-[10px] font-bold text-white"
                                    >
                                        {{ conversation.unread_count }}
                                    </span>
                                </div>
                                <div class="truncate text-xs text-[var(--poet-muted)]">
                                    {{ conversation.last_message?.body || (locale === 'lv' ? 'Sāc sarunu' : 'Start a conversation') }}
                                </div>
                            </div>
                        </Link>
                    </div>
                </aside>

                <section v-if="activeConversation" class="flex min-h-0 flex-col">
                    <div class="flex shrink-0 items-center gap-3 border-b border-[var(--poet-border)] px-4 py-3">
                        <img
                            :src="otherUser?.avatar_url || '/img/default_avatar.svg'"
                            class="h-10 w-10 rounded-full object-cover"
                            alt=""
                        />
                        <div>
                            <div class="font-semibold">{{ otherUser?.name }}</div>
                            <div class="text-xs text-[var(--poet-muted)]">@{{ otherUser?.username }}</div>
                        </div>
                    </div>

                    <div
                        ref="messagePane"
                        class="min-h-0 flex-1 space-y-3 overflow-y-auto p-4"
                        aria-live="polite"
                    >
                        <div
                            v-for="message in localMessages"
                            :key="message.id"
                            :class="[
                                'flex',
                                message.user.id === authUser.id ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div
                                :class="[
                                    'max-w-[78%] rounded-2xl px-4 py-2.5 text-sm leading-6',
                                    message.user.id === authUser.id
                                        ? 'bg-[var(--poet-accent)] text-white'
                                        : 'bg-[var(--poet-surface-soft)] text-[var(--poet-text)]'
                                ]"
                            >
                                <div class="whitespace-pre-wrap break-words">{{ message.body }}</div>
                                <div
                                    :class="[
                                        'mt-1 text-[10px]',
                                        message.user.id === authUser.id
                                            ? 'text-white/70'
                                            : 'text-[var(--poet-muted)]'
                                    ]"
                                >
                                    {{ new Date(message.created_at).toLocaleString() }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="!localMessages.length"
                            class="flex h-full items-center justify-center text-sm text-[var(--poet-muted)]"
                        >
                            {{ locale === 'lv' ? 'Uzraksti pirmo ziņu.' : 'Write the first message.' }}
                        </div>
                    </div>

                    <form
                        class="flex shrink-0 gap-2 border-t border-[var(--poet-border)] p-3"
                        @submit.prevent="sendMessage"
                    >
                        <textarea
                            v-model="body"
                            rows="1"
                            maxlength="5000"
                            :placeholder="locale === 'lv' ? 'Raksti ziņu...' : 'Write a message...'"
                            class="min-h-11 min-w-0 flex-1 resize-none rounded-2xl border-[var(--poet-border)] bg-[var(--poet-bg)] text-sm text-[var(--poet-text)]"
                            @keydown.enter.exact.prevent="sendMessage"
                        />
                        <button
                            type="submit"
                            :disabled="sending || !body.trim()"
                            class="rounded-full bg-[var(--poet-accent)] px-5 py-2 text-sm font-semibold text-white disabled:opacity-40"
                        >
                            {{ locale === 'lv' ? 'Sūtīt' : 'Send' }}
                        </button>
                    </form>
                </section>

                <section
                    v-else
                    class="flex items-center justify-center p-8 text-center text-sm text-[var(--poet-muted)]"
                >
                    {{
                        locale === 'lv'
                            ? 'Izvēlies sarunu vai atrodi lietotāju, lai sāktu rakstīt.'
                            : 'Choose a conversation or find a user to start messaging.'
                    }}
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
