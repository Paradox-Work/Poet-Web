<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    target: Object,
});

const { locale } = useI18n();

const form = useForm({
    target_type: props.target.type,
    target_id: props.target.id,
    reason: 'spam',
    details: '',
});

function submit() {
    form.post(route('reports.store'));
}
</script>

<template>
    <Head :title="locale === 'lv' ? 'Ziņot' : 'Report'" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-xl px-4 py-8">
            <div class="poet-card p-6">
                <div class="poet-kicker">
                    {{ locale === 'lv' ? 'Moderācija' : 'Moderation' }}
                </div>
                <h1 class="poet-title mt-1 text-2xl">
                    {{ locale === 'lv' ? 'Ziņot par saturu' : 'Report content' }}
                </h1>
                <p class="mt-2 text-sm text-[var(--poet-muted)]">
                    {{ target.label }}
                </p>

                <form class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            {{ locale === 'lv' ? 'Iemesls' : 'Reason' }}
                        </label>
                        <select
                            v-model="form.reason"
                            class="w-full rounded-xl border-[var(--poet-border)] bg-[var(--poet-bg)]"
                        >
                            <option value="spam">{{ locale === 'lv' ? 'Mēstules / spam' : 'Spam' }}</option>
                            <option value="harassment">{{ locale === 'lv' ? 'Uzmākšanās / aizskaršana' : 'Harassment' }}</option>
                            <option value="copyright">{{ locale === 'lv' ? 'Autortiesību pārkāpums' : 'Copyright' }}</option>
                            <option value="inappropriate">{{ locale === 'lv' ? 'Neatbilstošs saturs' : 'Inappropriate content' }}</option>
                            <option value="impersonation">{{ locale === 'lv' ? 'Uzdošanās par citu' : 'Impersonation' }}</option>
                            <option value="other">{{ locale === 'lv' ? 'Cits' : 'Other' }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium">
                            {{ locale === 'lv' ? 'Papildu informācija' : 'Additional details' }}
                        </label>
                        <textarea
                            v-model="form.details"
                            rows="5"
                            maxlength="2000"
                            class="w-full rounded-xl border-[var(--poet-border)] bg-[var(--poet-bg)]"
                        />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('dashboard')"
                            class="rounded-xl border border-[var(--poet-border)] px-4 py-2"
                        >
                            {{ locale === 'lv' ? 'Atcelt' : 'Cancel' }}
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-red-500 px-4 py-2 font-semibold text-white disabled:opacity-50"
                        >
                            {{ locale === 'lv' ? 'Nosūtīt ziņojumu' : 'Submit report' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
