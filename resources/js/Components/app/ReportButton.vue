<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { FlagIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useI18n } from '@/i18n';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    targetId: {
        type: Number,
        required: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const { locale } = useI18n();
const open = ref(false);
const reason = ref('spam');
const details = ref('');
const sending = ref(false);
const sent = ref(false);
const error = ref('');

async function submit() {
    sending.value = true;
    error.value = '';

    try {
        await axios.post(route('reports.store'), {
            type: props.type,
            id: props.targetId,
            reason: reason.value,
            details: details.value || null,
        });

        sent.value = true;
        setTimeout(() => {
            open.value = false;
            sent.value = false;
            details.value = '';
        }, 900);
    } catch (e) {
        error.value =
            e.response?.data?.message
            ?? (locale.value === 'lv'
                ? 'Ziņojumu neizdevās nosūtīt.'
                : 'Could not submit report.');
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <button
        type="button"
        @click="open = true"
        :class="[
            'inline-flex items-center gap-1.5 text-xs text-[var(--poet-muted)] transition hover:text-red-500',
            compact ? '' : 'rounded-full px-2 py-1'
        ]"
        :aria-label="locale === 'lv' ? 'Ziņot par saturu' : 'Report content'"
    >
        <FlagIcon class="h-4 w-4" />
        <span v-if="!compact">
            {{ locale === 'lv' ? 'Ziņot' : 'Report' }}
        </span>
    </button>

    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
            role="dialog"
            aria-modal="true"
            :aria-label="locale === 'lv' ? 'Ziņot par saturu' : 'Report content'"
            @click.self="open = false"
        >
            <form
                @submit.prevent="submit"
                class="w-full max-w-md rounded-2xl border border-[var(--poet-border)] bg-[var(--poet-surface)] p-5 shadow-2xl"
            >
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-serif text-xl font-semibold text-[var(--poet-text)]">
                        {{ locale === 'lv' ? 'Ziņot par saturu' : 'Report content' }}
                    </h2>

                    <button
                        type="button"
                        @click="open = false"
                        class="poet-focus rounded-full p-2 text-[var(--poet-muted)] hover:bg-[var(--poet-surface-soft)]"
                        aria-label="Close"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>

                <label class="mt-4 block text-sm font-medium text-[var(--poet-text)]">
                    {{ locale === 'lv' ? 'Iemesls' : 'Reason' }}
                </label>

                <select
                    v-model="reason"
                    class="mt-1 w-full rounded-xl border border-[var(--poet-border)] bg-[var(--poet-bg)] text-[var(--poet-text)]"
                >
                    <option value="spam">Spam</option>
                    <option value="harassment">{{ locale === 'lv' ? 'Uzmākšanās' : 'Harassment' }}</option>
                    <option value="copyright">{{ locale === 'lv' ? 'Autortiesības' : 'Copyright' }}</option>
                    <option value="inappropriate">{{ locale === 'lv' ? 'Nepiemērots saturs' : 'Inappropriate content' }}</option>
                    <option value="impersonation">{{ locale === 'lv' ? 'Uzdošanās par citu' : 'Impersonation' }}</option>
                    <option value="other">{{ locale === 'lv' ? 'Cits' : 'Other' }}</option>
                </select>

                <label class="mt-4 block text-sm font-medium text-[var(--poet-text)]">
                    {{ locale === 'lv' ? 'Papildu informācija' : 'Additional details' }}
                </label>

                <textarea
                    v-model="details"
                    rows="4"
                    maxlength="2000"
                    class="mt-1 w-full rounded-xl border border-[var(--poet-border)] bg-[var(--poet-bg)] text-[var(--poet-text)]"
                />

                <p v-if="error" class="mt-2 text-sm text-red-500">
                    {{ error }}
                </p>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        @click="open = false"
                        class="rounded-full border border-[var(--poet-border)] px-4 py-2 text-sm text-[var(--poet-muted)]"
                    >
                        {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                    </button>

                    <button
                        type="submit"
                        :disabled="sending"
                        class="rounded-full bg-red-500 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        {{
                            sent
                                ? (locale === 'lv' ? 'Nosūtīts' : 'Sent')
                                : sending
                                    ? (locale === 'lv' ? 'Sūta...' : 'Sending...')
                                    : (locale === 'lv' ? 'Nosūtīt ziņojumu' : 'Submit report')
                        }}
                    </button>
                </div>
            </form>
        </div>
    </Teleport>
</template>
