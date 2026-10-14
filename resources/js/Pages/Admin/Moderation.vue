<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

defineProps({
    metrics: Object,
    reports: Object,
});

const { locale } = useI18n();

function resolve(report, action) {
    router.post(
        route('admin.reports.resolve', report.id),
        { action },
        { preserveScroll: true }
    );
}
</script>

<template>
    <Head :title="locale === 'lv' ? 'Moderācija' : 'Moderation'" />

    <AuthenticatedLayout>
        <div class="h-full overflow-y-auto bg-[var(--poet-bg)]">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
                <div class="mb-6">
                    <div class="poet-kicker">
                        {{ locale === 'lv' ? 'Administrēšana' : 'Administration' }}
                    </div>
                    <h1 class="poet-title mt-1 text-3xl">
                        {{ locale === 'lv' ? 'Moderācija un pārskats' : 'Moderation & overview' }}
                    </h1>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                    <div
                        v-for="(value, key) in metrics"
                        :key="key"
                        class="poet-card p-4"
                    >
                        <div class="text-xs uppercase tracking-wide text-[var(--poet-muted)]">
                            {{ key.replaceAll('_', ' ') }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-[var(--poet-text)]">
                            {{ value }}
                        </div>
                    </div>
                </div>

                <section class="poet-card mt-6 overflow-hidden">
                    <div class="border-b border-[var(--poet-border)] px-5 py-4">
                        <h2 class="font-serif text-xl font-semibold text-[var(--poet-text)]">
                            {{ locale === 'lv' ? 'Satura ziņojumi' : 'Content reports' }}
                        </h2>
                    </div>

                    <div v-if="reports.data?.length" class="divide-y divide-[var(--poet-border)]">
                        <article
                            v-for="report in reports.data"
                            :key="report.id"
                            class="grid gap-3 px-5 py-4 lg:grid-cols-[1fr_auto]"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-[var(--poet-text)]">
                                        {{ report.type }} #{{ report.target_id }}
                                    </span>
                                    <span class="rounded-full bg-[var(--poet-surface-soft)] px-2 py-0.5 text-xs text-[var(--poet-muted)]">
                                        {{ report.reason }}
                                    </span>
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-medium',
                                            report.status === 'pending'
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-emerald-100 text-emerald-800'
                                        ]"
                                    >
                                        {{ report.status }}
                                    </span>
                                </div>

                                <p v-if="report.details" class="mt-2 text-sm text-[var(--poet-muted)]">
                                    {{ report.details }}
                                </p>

                                <div class="mt-2 text-xs text-[var(--poet-muted)]">
                                    {{ locale === 'lv' ? 'Ziņoja' : 'Reported by' }}:
                                    @{{ report.reporter }}
                                </div>
                            </div>

                            <div v-if="report.status === 'pending'" class="flex items-start gap-2">
                                <button
                                    type="button"
                                    @click="resolve(report, 'dismiss')"
                                    class="rounded-full border border-[var(--poet-border)] px-3 py-1.5 text-xs font-medium text-[var(--poet-muted)]"
                                >
                                    {{ locale === 'lv' ? 'Noraidīt' : 'Dismiss' }}
                                </button>

                                <button
                                    v-if="['post', 'comment'].includes(report.type)"
                                    type="button"
                                    @click="resolve(report, 'remove_content')"
                                    class="rounded-full bg-red-500 px-3 py-1.5 text-xs font-semibold text-white"
                                >
                                    {{ locale === 'lv' ? 'Noņemt saturu' : 'Remove content' }}
                                </button>
                            </div>
                        </article>
                    </div>

                    <div v-else class="p-10 text-center text-sm text-[var(--poet-muted)]">
                        {{ locale === 'lv' ? 'Nav iesniegtu ziņojumu.' : 'No reports have been submitted.' }}
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
