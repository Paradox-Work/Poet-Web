<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

defineProps({
    pending: { type: Array, default: () => [] },
    shared: { type: Array, default: () => [] },
});

const { locale } = useI18n();

function accept(item) {
    router.post(route('collaborations.accept', item.id));
}

function decline(item) {
    router.post(route('collaborations.decline', item.id));
}
</script>

<template>
    <Head :title="locale === 'lv' ? 'Kopdarbi' : 'Collaborations'" />

    <AuthenticatedLayout>
        <div class="mx-auto h-full max-w-5xl overflow-y-auto px-4 py-6">
            <header class="mb-6">
                <div class="poet-kicker">
                    {{ locale === 'lv' ? 'Rakstiet kopā' : 'Write together' }}
                </div>
                <h1 class="poet-title mt-1 text-3xl">
                    {{ locale === 'lv' ? 'Dzejoļu kopdarbi' : 'Poem collaborations' }}
                </h1>
            </header>

            <section class="mb-8">
                <h2 class="mb-3 font-serif text-2xl font-semibold">
                    {{ locale === 'lv' ? 'Uzaicinājumi' : 'Invitations' }}
                </h2>
                <div v-if="pending.length" class="space-y-3">
                    <article v-for="item in pending" :key="item.id" class="poet-card flex items-center justify-between gap-4 p-4">
                        <div>
                            <div class="font-semibold">{{ item.post.title }}</div>
                            <div class="text-sm text-[var(--poet-muted)]">
                                {{ item.post.owner.name }} · @{{ item.post.owner.username }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="rounded-lg border border-[var(--poet-border)] px-3 py-1.5 text-sm" @click="decline(item)">
                                {{ locale === 'lv' ? 'Noraidīt' : 'Decline' }}
                            </button>
                            <button class="rounded-lg bg-[var(--poet-accent)] px-3 py-1.5 text-sm font-semibold text-white" @click="accept(item)">
                                {{ locale === 'lv' ? 'Pieņemt' : 'Accept' }}
                            </button>
                        </div>
                    </article>
                </div>
                <div v-else class="text-sm text-[var(--poet-muted)]">
                    {{ locale === 'lv' ? 'Nav jaunu uzaicinājumu.' : 'No pending invitations.' }}
                </div>
            </section>

            <section>
                <h2 class="mb-3 font-serif text-2xl font-semibold">
                    {{ locale === 'lv' ? 'Kopīgie dzejoļi' : 'Shared poems' }}
                </h2>
                <div v-if="shared.length" class="grid gap-3 md:grid-cols-2">
                    <article v-for="item in shared" :key="item.id" class="poet-card p-4">
                        <h3 class="font-serif text-lg font-semibold">{{ item.post.title }}</h3>
                        <div class="mt-1 text-xs text-[var(--poet-muted)]">
                            {{ locale === 'lv' ? 'Autors' : 'Owner' }}: {{ item.post.owner.name }}
                        </div>
                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="route('poem.write', { edit: item.post.id })"
                                class="rounded-lg bg-[var(--poet-accent)] px-3 py-2 text-sm font-semibold text-white"
                            >
                                {{ locale === 'lv' ? 'Rediģēt dzejoli' : 'Edit poem' }}
                            </Link>
                            <Link
                                :href="route('collaborations.manage', item.post.id)"
                                class="rounded-lg border border-[var(--poet-border)] px-3 py-2 text-sm"
                            >
                                {{ locale === 'lv' ? 'Kopdarba iestatījumi' : 'Collaboration settings' }}
                            </Link>
                        </div>
                    </article>
                </div>
                <div v-else class="text-sm text-[var(--poet-muted)]">
                    {{ locale === 'lv' ? 'Te vēl nav kopīgu dzejoļu.' : 'No shared poems yet.' }}
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
