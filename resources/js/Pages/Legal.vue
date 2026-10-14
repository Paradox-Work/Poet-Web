<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useI18n } from '@/i18n';

const props = defineProps({
    page: String,
});

const { locale } = useI18n();

const content = computed(() => {
    const lv = locale.value === 'lv';

    const pages = {
        privacy: {
            title: lv ? 'Privātuma politika' : 'Privacy Policy',
            intro: lv
                ? 'Poet-Web glabā tikai tos datus, kas nepieciešami kontam un platformas funkcijām.'
                : 'Poet-Web stores the information needed to operate accounts and platform features.',
            sections: [
                [lv ? 'Kādi dati tiek glabāti' : 'Data we store', lv
                    ? 'Konta dati, profila informācija, publikācijas, grupu dalība, ziņas, reakcijas, komentāri, saglabātie darbi un tehniskie sesijas dati.'
                    : 'Account data, profile information, publications, group memberships, messages, reactions, comments, bookmarks, and technical session data.'],
                [lv ? 'Kam tie tiek izmantoti' : 'How data is used', lv
                    ? 'Autentifikācijai, publicēšanai, sociālajām funkcijām, drošībai, moderācijai un lietotāja izvēlēto funkciju nodrošināšanai.'
                    : 'For authentication, publishing, social functionality, security, moderation, and user-requested features.'],
                [lv ? 'Kontroles iespējas' : 'Your controls', lv
                    ? 'Lietotājs var atjaunināt savu profilu un dzēst kontu no profila iestatījumiem.'
                    : 'Users can update their profile and delete their account from profile settings.'],
            ],
        },
        terms: {
            title: lv ? 'Lietošanas noteikumi' : 'Terms of Use',
            intro: lv
                ? 'Izmantojot Poet-Web, lietotājs piekrīt izmantot platformu likumīgi un cienīt citus autorus.'
                : 'By using Poet-Web, users agree to use the platform lawfully and respect other authors.',
            sections: [
                [lv ? 'Saturs' : 'Content', lv
                    ? 'Publicē tikai saturu, kuru drīksti publicēt. Tu paliec atbildīgs par saviem darbiem un komentāriem.'
                    : 'Only publish content you are allowed to publish. You remain responsible for your work and comments.'],
                [lv ? 'Autortiesības' : 'Copyright', lv
                    ? 'Autors saglabā tiesības uz savu oriģināldarbu. Publicēšana pati par sevi nenodod īpašumtiesības Poet-Web.'
                    : 'Authors retain rights to their original work. Publishing does not itself transfer ownership to Poet-Web.'],
                [lv ? 'Moderācija' : 'Moderation', lv
                    ? 'Saturs, kas pārkāpj kopienas noteikumus vai tiesību aktus, var tikt noņemts pēc pārbaudes.'
                    : 'Content that violates community rules or law may be removed after review.'],
            ],
        },
        community: {
            title: lv ? 'Kopienas noteikumi' : 'Community Guidelines',
            intro: lv
                ? 'Poet-Web ir vieta autoriem un lasītājiem. Diskusijām jāpaliek cieņpilnām.'
                : 'Poet-Web is a place for writers and readers. Discussion should remain respectful.',
            sections: [
                [lv ? 'Cieni autorus' : 'Respect authors', lv
                    ? 'Neuzmācies citiem lietotājiem un nepublicē viņu darbu kā savu.'
                    : 'Do not harass other users or present their work as your own.'],
                [lv ? 'Atzīmē sensitīvu saturu' : 'Label sensitive content', lv
                    ? 'Izmanto satura vērtējumu, ja publikācija paredzēta nobriedušai auditorijai.'
                    : 'Use the content rating when a publication is intended for a mature audience.'],
                [lv ? 'Ziņo, nevis eskalē' : 'Report, do not escalate', lv
                    ? 'Izmanto ziņošanas funkciju spamam, uzmākšanai, autortiesību problēmām vai citam neatbilstošam saturam.'
                    : 'Use the report feature for spam, harassment, copyright concerns, or other inappropriate content.'],
            ],
        },
        copyright: {
            title: lv ? 'Autortiesības un autoru darbi' : 'Copyright & Author Work',
            intro: lv
                ? 'Poet-Web ir veidots ar domu, ka autora darbs paliek autora darbs.'
                : 'Poet-Web is designed around the principle that an author’s work remains their work.',
            sections: [
                [lv ? 'Īpašumtiesības' : 'Ownership', lv
                    ? 'Platforma nepretendē uz īpašumtiesībām tikai tāpēc, ka darbs ir publicēts Poet-Web.'
                    : 'The platform does not claim ownership merely because work is published on Poet-Web.'],
                [lv ? 'Ziņošana' : 'Reporting', lv
                    ? 'Ja uzskati, ka kāds publicējis tavu darbu bez atļaujas, izmanto ziņošanas funkciju un izvēlies “Autortiesības”.'
                    : 'If you believe someone published your work without permission, use the report feature and choose “Copyright”.'],
                [lv ? 'Atbildība' : 'Responsibility', lv
                    ? 'Lietotājam jābūt tiesībām publicēt saturu, ko viņš ievieto platformā.'
                    : 'Users must have the right to publish the content they submit.'],
            ],
        },
    };

    return pages[props.page] ?? pages.terms;
});
</script>

<template>
    <Head :title="content.title" />

    <AuthenticatedLayout>
        <div class="h-full overflow-y-auto bg-[var(--poet-bg)]">
            <article class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
                <div class="poet-kicker">Poet-Web</div>
                <h1 class="poet-title mt-1 text-3xl">{{ content.title }}</h1>
                <p class="mt-3 text-[var(--poet-muted)]">{{ content.intro }}</p>

                <section
                    v-for="section in content.sections"
                    :key="section[0]"
                    class="poet-card mt-5 p-5"
                >
                    <h2 class="font-serif text-xl font-semibold text-[var(--poet-text)]">
                        {{ section[0] }}
                    </h2>
                    <p class="mt-2 leading-7 text-[var(--poet-muted)]">
                        {{ section[1] }}
                    </p>
                </section>

                <nav class="mt-8 flex flex-wrap gap-3 text-sm text-[var(--poet-accent-strong)]" aria-label="Legal pages">
                    <Link :href="route('privacy')">{{ locale === 'lv' ? 'Privātums' : 'Privacy' }}</Link>
                    <Link :href="route('terms')">{{ locale === 'lv' ? 'Noteikumi' : 'Terms' }}</Link>
                    <Link :href="route('community')">{{ locale === 'lv' ? 'Kopienas noteikumi' : 'Guidelines' }}</Link>
                    <Link :href="route('copyright')">{{ locale === 'lv' ? 'Autortiesības' : 'Copyright' }}</Link>
                </nav>
            </article>
        </div>
    </AuthenticatedLayout>
</template>
