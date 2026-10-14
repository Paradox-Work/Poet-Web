<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    post: Object,
    canInvite: Boolean,
    collaborators: { type: Array, default: () => [] },
});

const { locale } = useI18n();

const form = useForm({
    username: '',
});

function invite() {
    form.post(route('collaborations.invite', props.post.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function remove(item) {
    router.delete(route('collaborations.remove', item.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="locale === 'lv' ? 'Kopdarbs' : 'Collaboration'" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl px-4 py-8">
            <div class="mb-6">
                <div class="poet-kicker">{{ locale === 'lv' ? 'Kopdarbs' : 'Collaboration' }}</div>
                <h1 class="poet-title mt-1 text-3xl">{{ post.title }}</h1>
                <div class="mt-3 flex flex-wrap gap-2">
                    <Link
                        :href="route('poem.write', { edit: post.id })"
                        class="rounded-lg bg-[var(--poet-accent)] px-4 py-2 text-sm font-semibold text-white"
                    >
                        {{ locale === 'lv' ? 'Atvērt redaktorā' : 'Open editor' }}
                    </Link>
                    <Link
                        :href="route('collaborations.index')"
                        class="rounded-lg border border-[var(--poet-border)] px-4 py-2 text-sm"
                    >
                        {{ locale === 'lv' ? 'Visi kopdarbi' : 'All collaborations' }}
                    </Link>
                </div>
            </div>

            <section v-if="canInvite" class="poet-card mb-6 p-4">
                <h2 class="font-serif text-xl font-semibold">
                    {{ locale === 'lv' ? 'Uzaicināt līdzautoru' : 'Invite collaborator' }}
                </h2>
                <form class="mt-3 flex gap-2" @submit.prevent="invite">
                    <input
                        v-model="form.username"
                        required
                        :placeholder="locale === 'lv' ? 'Lietotājvārds' : 'Username'"
                        class="min-w-0 flex-1 rounded-xl border-[var(--poet-border)] bg-[var(--poet-bg)]"
                    />
                    <button class="rounded-xl bg-[var(--poet-accent)] px-4 py-2 font-semibold text-white">
                        {{ locale === 'lv' ? 'Uzaicināt' : 'Invite' }}
                    </button>
                </form>
                <div v-if="form.errors.username" class="mt-2 text-sm text-red-500">
                    {{ form.errors.username }}
                </div>
            </section>

            <section>
                <h2 class="mb-3 font-serif text-xl font-semibold">
                    {{ locale === 'lv' ? 'Līdzautori' : 'Collaborators' }}
                </h2>
                <div v-if="collaborators.length" class="space-y-2">
                    <div
                        v-for="item in collaborators"
                        :key="item.id"
                        class="poet-card flex items-center justify-between gap-4 p-4"
                    >
                        <div>
                            <div class="font-semibold">{{ item.user.name }}</div>
                            <div class="text-xs text-[var(--poet-muted)]">
                                @{{ item.user.username }} · {{ item.status }}
                            </div>
                        </div>
                        <button
                            type="button"
                            class="text-sm font-semibold text-red-500"
                            @click="remove(item)"
                        >
                            {{ locale === 'lv' ? 'Noņemt' : 'Remove' }}
                        </button>
                    </div>
                </div>
                <div v-else class="text-sm text-[var(--poet-muted)]">
                    {{ locale === 'lv' ? 'Līdzautoru vēl nav.' : 'No collaborators yet.' }}
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
