<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CompactPaginator from '@/Components/app/CompactPaginator.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    stats: Object,
    reports: Object,
});

const { locale } = useI18n();

function resolveReport(report, action) {
    const note = window.prompt(
        locale.value === 'lv'
            ? 'Piezīme (neobligāti):'
            : 'Note (optional):'
    );

    router.post(
        route('admin.reports.resolve', report.id),
        { action, note: note || null },
        { preserveScroll: true }
    );
}

function unsuspend(userId) {
    router.post(
        route('admin.users.unsuspend', userId),
        {},
        { preserveScroll: true }
    );
}
</script>

<template>
    <Head :title="locale === 'lv' ? 'Administrēšana' : 'Administration'" />

    <AuthenticatedLayout>
        <div class="mx-auto h-full max-w-7xl overflow-y-auto px-4 py-6">
            <header class="mb-6">
                <div class="poet-kicker">
                    {{ locale === 'lv' ? 'Vietnes pārvaldība' : 'Site management' }}
                </div>
                <h1 class="poet-title mt-1 text-3xl">
                    {{ locale === 'lv' ? 'Administrācijas panelis' : 'Administration dashboard' }}
                </h1>
            </header>

            <section class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <div v-for="(value, key) in stats" :key="key" class="poet-card p-4">
                    <div class="text-xs uppercase tracking-wide text-[var(--poet-muted)]">
                        {{ key.replaceAll('_', ' ') }}
                    </div>
                    <div class="mt-1 text-3xl font-bold">{{ value }}</div>
                </div>
            </section>

            <section>
                <div class="mb-3">
                    <div class="poet-kicker">
                        {{ locale === 'lv' ? 'Moderācija' : 'Moderation' }}
                    </div>
                    <h2 class="font-serif text-2xl font-semibold">
                        {{ locale === 'lv' ? 'Ziņojumu rinda' : 'Report queue' }}
                    </h2>
                </div>

                <div v-if="reports.data?.length" class="space-y-3">
                    <article v-for="report in reports.data" :key="report.id" class="poet-card p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-[var(--poet-border)] px-2 py-0.5 text-xs">
                                        {{ report.target_type }}
                                    </span>
                                    <span class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs text-amber-500">
                                        {{ report.reason }}
                                    </span>
                                    <span class="text-xs text-[var(--poet-muted)]">{{ report.status }}</span>
                                </div>
                                <h3 class="mt-2 font-semibold">{{ report.target.label }}</h3>
                                <p class="mt-1 text-sm text-[var(--poet-muted)]">
                                    {{ report.details || (locale === 'lv' ? 'Nav papildu informācijas.' : 'No extra details.') }}
                                </p>
                                <div class="mt-2 text-xs text-[var(--poet-muted)]">
                                    {{ locale === 'lv' ? 'Ziņoja' : 'Reported by' }}:
                                    @{{ report.reporter.username }}
                                </div>
                            </div>

                            <div v-if="report.status === 'pending'" class="flex flex-wrap gap-2">
                                <button class="rounded-lg border border-[var(--poet-border)] px-3 py-1.5 text-xs" @click="resolveReport(report, 'dismiss')">
                                    {{ locale === 'lv' ? 'Noraidīt' : 'Dismiss' }}
                                </button>
                                <button
                                    v-if="['post', 'comment'].includes(report.target_type)"
                                    class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white"
                                    @click="resolveReport(report, 'remove_content')"
                                >
                                    {{ locale === 'lv' ? 'Dzēst saturu' : 'Remove content' }}
                                </button>
                                <button class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white" @click="resolveReport(report, 'suspend_user')">
                                    {{ locale === 'lv' ? 'Apturēt lietotāju' : 'Suspend user' }}
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else class="poet-card py-12 text-center text-sm text-[var(--poet-muted)]">
                    {{ locale === 'lv' ? 'Nav ziņojumu.' : 'No reports.' }}
                </div>

                <CompactPaginator
                    v-if="reports.meta?.last_page > 1"
                    class="mt-4"
                    :meta="reports.meta"
                />
            </section>
        </div>
    </AuthenticatedLayout>
</template>
