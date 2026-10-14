<script setup>
import { Head } from '@inertiajs/vue3';
import { BookmarkIcon } from '@heroicons/vue/24/outline';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostList from '@/Components/app/PostList.vue';
import { useI18n } from '@/i18n';

defineProps({
    posts: Object,
});

const { locale } = useI18n();
</script>

<template>
    <Head :title="locale === 'lv' ? 'Saglabātie darbi' : 'Saved'" />

    <AuthenticatedLayout>
        <div class="h-full overflow-y-auto bg-[var(--poet-bg)]">
            <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
                <div class="mb-6">
                    <div class="poet-kicker">
                        {{ locale === 'lv' ? 'Lasīšanas saraksts' : 'Reading list' }}
                    </div>
                    <h1 class="poet-title mt-1 text-3xl">
                        {{ locale === 'lv' ? 'Saglabātie darbi' : 'Saved publications' }}
                    </h1>
                </div>

                <div
                    v-if="posts?.data?.length"
                    class="min-h-[28rem]"
                >
                    <PostList :posts="posts" mode="list" />
                </div>

                <div
                    v-else
                    class="poet-card flex min-h-[22rem] flex-col items-center justify-center p-8 text-center"
                >
                    <BookmarkIcon class="h-10 w-10 text-[var(--poet-muted)]" />
                    <h2 class="mt-3 font-serif text-xl font-semibold text-[var(--poet-text)]">
                        {{ locale === 'lv' ? 'Vēl nekas nav saglabāts' : 'Nothing saved yet' }}
                    </h2>
                    <p class="mt-1 max-w-md text-sm text-[var(--poet-muted)]">
                        {{
                            locale === 'lv'
                                ? 'Saglabā dzejoļus un publikācijas, lai pie tiem atgrieztos vēlāk.'
                                : 'Bookmark poems and publications so you can return to them later.'
                        }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
