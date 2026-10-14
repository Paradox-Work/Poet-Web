<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/i18n';

const { locale, setLocale } = useI18n();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <div class="flex flex-col items-center gap-3">
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>

            <div class="flex items-center rounded-full border border-gray-300 bg-white p-0.5 text-xs font-semibold shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <button
                    type="button"
                    @click="setLocale('lv')"
                    :class="[
                        'rounded-full px-3 py-1 transition',
                        locale === 'lv'
                            ? 'bg-indigo-600 text-white'
                            : 'text-gray-500 dark:text-gray-300'
                    ]"
                >
                    LV
                </button>
                <button
                    type="button"
                    @click="setLocale('en')"
                    :class="[
                        'rounded-full px-3 py-1 transition',
                        locale === 'en'
                            ? 'bg-indigo-600 text-white'
                            : 'text-gray-500 dark:text-gray-300'
                    ]"
                >
                    EN
                </button>
            </div>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>
