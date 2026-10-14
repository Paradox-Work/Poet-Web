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
    const lv = {
        privacy: {
            title: 'Privātuma politika',
            intro: 'Poet-Web glabā tikai informāciju, kas nepieciešama kontiem, publikācijām, grupām, ziņām un drošības funkcijām.',
            sections: [
                ['Ko mēs glabājam', 'Konta informāciju, publicēto saturu, grupu dalību, reakcijas, komentārus, tiešās ziņas, grāmatzīmes un tehnisko sesijas informāciju.'],
                ['Kam ir piekļuve', 'Privātu grupu saturs un tiešās ziņas ir pieejamas tikai autorizētajiem dalībniekiem. Vietnes administratori var pārskatīt ziņojumus par pārkāpumiem.'],
                ['Kontroles iespējas', 'Lietotājs var labot profilu, dzēst saturu un dzēst savu kontu. Konta dzēšana anonimizē identitāti un saglabā datubāzes integritāti.'],
            ],
        },
        terms: {
            title: 'Lietošanas noteikumi',
            intro: 'Izmantojot Poet-Web, lietotājs apņemas publicēt saturu, kuru viņam ir tiesības publicēt, un cienīt citus lietotājus.',
            sections: [
                ['Atbildība par saturu', 'Autors atbild par savu publicēto darbu un norāda pieaugušajiem paredzētu saturu ar atbilstošu vērtējumu.'],
                ['Kopienas drošība', 'Nav atļauta uzmākšanās, surogātpasts, uzdošanās par citu personu vai prettiesiska satura izplatīšana.'],
                ['Moderācija', 'Ziņotais saturs var tikt pārskatīts, noņemts vai lietotāja konts var tikt apturēts, ja tiek pārkāpti noteikumi.'],
            ],
        },
        guidelines: {
            title: 'Kopienas vadlīnijas',
            intro: 'Poet-Web ir rakstīšanas kopiena. Kritika ir vēlama, ja tā ir cieņpilna un vērsta uz darbu, nevis autoru.',
            sections: [
                ['Cieni autoru', 'Nesaglabā un nepārpublicē cita autora darbu kā savu.'],
                ['Sniedz konstruktīvas atsauksmes', 'Komentāriem jāpalīdz sarunai vai darbam, nevis jāaizskar cilvēks.'],
                ['Atzīmē pieaugušajiem paredzētu saturu', 'Ja darbā ir spēcīgas vai pieaugušajiem paredzētas tēmas, izmanto Mature satura vērtējumu.'],
                ['Ziņo par problēmām', 'Publikācijas, komentārus un lietotājus var nosūtīt vietnes moderācijai pārskatīšanai.'],
            ],
        },
        copyright: {
            title: 'Autortiesības un autora darbi',
            intro: 'Autors saglabā tiesības uz savu darbu. Publicēšana Poet-Web pati par sevi nenodod darba īpašumtiesības platformai.',
            sections: [
                ['Tava darba īpašumtiesības', 'Poet-Web piešķirtā tehniskā iespēja darbu attēlot nav autora tiesību nodošana.'],
                ['Citu autoru darbi', 'Neievieto darbu, kura publicēšanai tev nav atļaujas.'],
                ['Autortiesību ziņojumi', 'Ja uzskati, ka tavs darbs publicēts bez atļaujas, izmanto ziņošanas funkciju un izvēlies autortiesību iemeslu.'],
            ],
        },
    };

    const en = {
        privacy: {
            title: 'Privacy Policy',
            intro: 'Poet-Web stores information required for accounts, publications, communities, messaging, and security features.',
            sections: [
                ['What we store', 'Account information, published content, group membership, reactions, comments, direct messages, bookmarks, and technical session information.'],
                ['Who can access it', 'Private-group content and direct messages are limited to authorized participants. Site administrators can review abuse reports.'],
                ['Your controls', 'You can update your profile, remove content, and delete your account. Account deletion anonymizes identity while preserving database integrity.'],
            ],
        },
        terms: {
            title: 'Terms of Use',
            intro: 'By using Poet-Web, users agree to publish content they have the right to share and to respect other members.',
            sections: [
                ['Responsibility for content', 'Authors are responsible for their publications and should mark mature material with the appropriate content rating.'],
                ['Community safety', 'Harassment, spam, impersonation, and unlawful content are not allowed.'],
                ['Moderation', 'Reported material may be reviewed, removed, or an account may be suspended when rules are violated.'],
            ],
        },
        guidelines: {
            title: 'Community Guidelines',
            intro: 'Poet-Web is a writing community. Critique is welcome when it is respectful and focused on the work rather than attacking the author.',
            sections: [
                ['Respect authors', 'Do not claim or repost another author’s work as your own.'],
                ['Give constructive feedback', 'Comments should contribute to the discussion or the writing rather than harass a person.'],
                ['Mark mature material', 'Use the Mature content rating when a work contains strong or adult themes.'],
                ['Report problems', 'Publications, comments, and users can be submitted to site moderation for review.'],
            ],
        },
        copyright: {
            title: 'Copyright and Author Rights',
            intro: 'Authors retain rights to their work. Publishing on Poet-Web does not by itself transfer ownership to the platform.',
            sections: [
                ['Ownership of your work', 'The technical permission required to display your work is not a transfer of copyright ownership.'],
                ['Other authors’ work', 'Do not upload work you do not have permission to publish.'],
                ['Copyright reports', 'If you believe your work was posted without permission, use the report feature and choose the copyright reason.'],
            ],
        },
    };

    return (locale.value === 'lv' ? lv : en)[props.page] ?? en.terms;
});
</script>

<template>
    <Head :title="content.title">
        <meta name="description" :content="content.intro" />
        <meta property="og:title" :content="`${content.title} · Poet-Web`" />
        <meta property="og:description" :content="content.intro" />
        <meta property="og:type" content="website" />
    </Head>

    <AuthenticatedLayout>
        <div class="mx-auto max-w-4xl px-4 py-8">
            <article class="poet-card p-6 sm:p-8">
                <div class="poet-kicker">Poet-Web</div>
                <h1 class="poet-title mt-1 text-3xl">{{ content.title }}</h1>
                <p class="mt-4 leading-7 text-[var(--poet-muted)]">{{ content.intro }}</p>

                <div class="mt-8 space-y-6">
                    <section v-for="[title, body] in content.sections" :key="title">
                        <h2 class="font-serif text-xl font-semibold">{{ title }}</h2>
                        <p class="mt-2 leading-7 text-[var(--poet-muted)]">{{ body }}</p>
                    </section>
                </div>

                <nav class="mt-8 flex flex-wrap gap-3 border-t border-[var(--poet-border)] pt-5 text-sm">
                    <Link :href="route('legal.show', 'privacy')" class="text-[var(--poet-accent)]">Privacy</Link>
                    <Link :href="route('legal.show', 'terms')" class="text-[var(--poet-accent)]">Terms</Link>
                    <Link :href="route('legal.show', 'guidelines')" class="text-[var(--poet-accent)]">Guidelines</Link>
                    <Link :href="route('legal.show', 'copyright')" class="text-[var(--poet-accent)]">Copyright</Link>
                </nav>
            </article>
        </div>
    </AuthenticatedLayout>
</template>
