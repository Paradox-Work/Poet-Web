<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

const { locale, t } = useI18n();
</script>

<template>
    <Head :title="t('about.title')" />

    <AuthenticatedLayout>
        <div class="h-full overflow-y-auto bg-[var(--poet-bg)]">
            <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
                <header class="mb-8">
                    <div class="poet-kicker">Poet-Web</div>
                    <h1 class="poet-title mt-1 text-3xl">
                        {{ t('about.title') }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-[var(--poet-muted)]">
                        {{ t('about.subtitle') }}
                    </p>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <section class="poet-card p-5">
                        <h2 class="font-serif text-xl font-semibold">
                            {{ t('about.problem_title') }}
                        </h2>
                        <p class="mt-2 leading-7 text-[var(--poet-muted)]">
                            {{ t('about.problem') }}
                        </p>
                    </section>

                    <section class="poet-card p-5">
                        <h2 class="font-serif text-xl font-semibold">
                            {{ t('about.rights_title') }}
                        </h2>
                        <p class="mt-2 leading-7 text-[var(--poet-muted)]">
                            {{ t('about.rights') }}
                        </p>
                    </section>

                    <section class="poet-card p-5">
                        <h2 class="font-serif text-xl font-semibold">
                            {{ t('about.access_title') }}
                        </h2>
                        <p class="mt-2 leading-7 text-[var(--poet-muted)]">
                            {{ t('about.access') }}
                        </p>
                    </section>

                    <section class="poet-card p-5">
                        <h2 class="font-serif text-xl font-semibold">
                            {{ t('about.tech_title') }}
                        </h2>
                        <p class="mt-2 leading-7 text-[var(--poet-muted)]">
                            {{ t('about.tech') }}
                        </p>
                    </section>
                </div>

                <nav
                    class="mt-8 flex flex-wrap gap-x-4 gap-y-2 border-t border-[var(--poet-border)] pt-5 text-sm text-[var(--poet-accent-strong)]"
                    aria-label="Policy pages"
                >
                    <Link :href="route('privacy')">
                        {{ locale === 'lv' ? 'Privātums' : 'Privacy' }}
                    </Link>
                    <Link :href="route('terms')">
                        {{ locale === 'lv' ? 'Lietošanas noteikumi' : 'Terms' }}
                    </Link>
                    <Link :href="route('community')">
                        {{ locale === 'lv' ? 'Kopienas noteikumi' : 'Community guidelines' }}
                    </Link>
                    <Link :href="route('copyright')">
                        {{ locale === 'lv' ? 'Autortiesības' : 'Copyright' }}
                    </Link>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
