import { computed, ref } from 'vue';

const supportedLocales = ['lv', 'en'];

function initialLocale() {
    if (typeof window === 'undefined') {
        return 'lv';
    }

    const saved = localStorage.getItem('locale');

    return supportedLocales.includes(saved)
        ? saved
        : 'lv';
}

const locale = ref(initialLocale());

const messages = {
    lv: {
        'nav.home': 'Sākums',
        'nav.groups': 'Grupas',
        'nav.drafts': 'Melnraksti',
        'nav.create': 'Izveidot',
        'nav.post': 'Ieraksts',
        'nav.poem': 'Dzejolis',
        'nav.create_post': 'Izveidot ierakstu',
        'nav.write_poem': 'Rakstīt dzejoli',
        'nav.profile': 'Profils',
        'nav.logout': 'Izrakstīties',
        'nav.login': 'Pieteikties',
        'nav.register': 'Reģistrēties',
        'nav.search': 'Meklēt dzejoļus, cilvēkus vai grupas...',
        'nav.light_mode': 'Gaišais režīms',
        'nav.dark_mode': 'Tumšais režīms',
        'nav.about': 'Par projektu',
        'nav.tagline': 'Lasi · Raksti · Piederi',

        'auth.email': 'E-pasts',
        'auth.password': 'Parole',
        'auth.name': 'Vārds',
        'auth.confirm_password': 'Apstiprināt paroli',
        'auth.remember': 'Atcerēties mani',
        'auth.forgot': 'Aizmirsi paroli?',
        'auth.already_registered': 'Jau esi reģistrējies?',
        'auth.login': 'Pieteikties',
        'auth.register': 'Reģistrēties',

        'home.title': 'Lasi pa vienam {item}.',
        'home.feed': 'Plūsma',
        'home.poem': 'dzejolim',
        'home.post': 'ierakstam',
        'home.publication': 'darbam',
        'home.poems': 'Dzejoļi',
        'home.posts': 'Ieraksti',
        'home.everything': 'Viss',
        'home.for_you': 'Tev',
        'home.following': 'Sekoju',
        'home.groups': 'Grupas',
        'home.mine': 'Mani darbi',
        'home.explore': 'Atklāj',
        'home.feed_type': 'Plūsmas veids',
        'home.genre': 'Dzejoļa žanrs',

        'search.kicker': 'Meklēšana',
        'search.results': 'Rezultāti vaicājumam “{query}”',
        'search.users': 'Lietotāji',
        'search.groups': 'Grupas',
        'search.publications': 'Publikācijas',
        'search.no_users': 'Lietotāji netika atrasti.',
        'search.no_groups': 'Grupas netika atrastas.',
        'search.no_publications': 'Publikācijas netika atrastas.',

        'groups.kicker': 'Grupas',
        'groups.title': 'Tavas rakstīšanas kopienas.',
        'groups.new': 'Jauna grupa',
        'groups.yours': 'Tavas grupas',
        'groups.joined_spaces': '{count} rakstīšanas kopienas',
        'groups.none_member': 'Tu vēl neesi pievienojies nevienai grupai.',
        'groups.none_guest': 'Piesakies, lai pievienotos rakstīšanas grupām.',
        'groups.none_member_hint': 'Atrodi kopienu zemāk vai izveido savu.',
        'groups.none_guest_hint': 'Vari apskatīt grupas pirms konta izveides.',
        'groups.discover': 'Atklāj grupas',
        'groups.discover_hint': 'Meklē pēc nosaukuma vai tēmas.',
        'groups.search': 'Meklēt grupas...',
        'groups.admin': 'Administrators',
        'groups.member': 'Dalībnieks',
        'groups.pending': 'Gaida apstiprinājumu',
        'groups.join': 'Pievienoties',
        'groups.request_join': 'Nosūtīt pieteikumu',
        'groups.joining': 'Pievienojas...',
        'groups.sending': 'Nosūta...',
        'groups.public': 'Publiska',
        'groups.private': 'Privāta',
        'groups.public_help': 'Publiskas grupas publikācijas var lasīt arī viesi.',
        'groups.private_help': 'Privātas grupas publikācijas redz tikai apstiprināti dalībnieki.',
        'groups.visibility': 'Redzamība',
        'groups.open_membership': 'Brīva pievienošanās',
        'groups.approval_required': 'Nepieciešams apstiprinājums',

        'group.create_title': 'Izveidot jaunu grupu',
        'group.name': 'Grupas nosaukums',
        'group.auto_approve': 'Automātiski apstiprināt jaunus dalībniekus',
        'group.about': 'Par grupu',
        'group.cancel': 'Atcelt',
        'group.create': 'Izveidot',
        'group.creating': 'Izveido...',
        'group.members': '{count} dalībnieki',
        'group.change_cover': 'Mainīt vāku',
        'group.settings': 'Grupas iestatījumi',
        'group.save': 'Saglabāt',
        'group.join': 'Pievienoties grupai',
        'group.sign_in_join': 'Piesakies, lai pievienotos',

        'about.title': 'Par Poet-Web',
        'about.subtitle': 'Vieta dzejai, autoriem un kopienām.',
        'about.problem_title': 'Kāpēc Poet-Web?',
        'about.problem': 'Dzeja bieži tiek glabāta dažādās vietās un publicēta platformās, kas nav veidotas tieši autoriem. Poet-Web apvieno publicēšanu, ilgtermiņa glabāšanu, kopienas un diskusiju vienā sistēmā.',
        'about.rights_title': 'Autora tiesības',
        'about.rights': 'Autors saglabā tiesības uz savu iesniegto darbu. Poet-Web neuzskata publicēšanas faktu par autora tiesību nodošanu platformai.',
        'about.access_title': 'Piekļuves līmeņi',
        'about.access': 'Viesi var lasīt publisku saturu. Reģistrēti lietotāji var publicēt un mijiedarboties. Privātu grupu saturs ir pieejams tikai apstiprinātiem dalībniekiem.',
        'about.tech_title': 'Tehnoloģijas',
        'about.tech': 'Laravel, Vue, Inertia, Tailwind CSS, MySQL un Docker/Sail ar automatizētiem testiem un CI.',
    },
    en: {
        'nav.home': 'Home',
        'nav.groups': 'Groups',
        'nav.drafts': 'Drafts',
        'nav.create': 'Create',
        'nav.post': 'Post',
        'nav.poem': 'Poem',
        'nav.create_post': 'Create post',
        'nav.write_poem': 'Write poem',
        'nav.profile': 'Profile',
        'nav.logout': 'Log Out',
        'nav.login': 'Log in',
        'nav.register': 'Register',
        'nav.search': 'Search poems, people, or groups...',
        'nav.light_mode': 'Light mode',
        'nav.dark_mode': 'Dark mode',
        'nav.about': 'About',
        'nav.tagline': 'Read · Write · Belong',

        'auth.email': 'Email',
        'auth.password': 'Password',
        'auth.name': 'Name',
        'auth.confirm_password': 'Confirm Password',
        'auth.remember': 'Remember me',
        'auth.forgot': 'Forgot your password?',
        'auth.already_registered': 'Already registered?',
        'auth.login': 'Log in',
        'auth.register': 'Register',

        'home.title': 'Read one {item} at a time.',
        'home.feed': 'Feed',
        'home.poem': 'poem',
        'home.post': 'post',
        'home.publication': 'publication',
        'home.poems': 'Poems',
        'home.posts': 'Posts',
        'home.everything': 'Everything',
        'home.for_you': 'For You',
        'home.following': 'Following',
        'home.groups': 'Groups',
        'home.mine': 'My Art',
        'home.explore': 'Explore',
        'home.feed_type': 'Feed type',
        'home.genre': 'Poem genre',

        'search.kicker': 'Search',
        'search.results': 'Results for “{query}”',
        'search.users': 'Users',
        'search.groups': 'Groups',
        'search.publications': 'Publications',
        'search.no_users': 'No users were found.',
        'search.no_groups': 'No groups were found.',
        'search.no_publications': 'No publications were found.',

        'groups.kicker': 'Groups',
        'groups.title': 'Your writing spaces.',
        'groups.new': 'New group',
        'groups.yours': 'Your groups',
        'groups.joined_spaces': '{count} joined writing spaces',
        'groups.none_member': "You haven't joined a group yet.",
        'groups.none_guest': 'Sign in to join writing groups.',
        'groups.none_member_hint': 'Discover a writing space below or start your own.',
        'groups.none_guest_hint': 'You can browse groups below before creating an account.',
        'groups.discover': 'Discover',
        'groups.discover_hint': 'Find groups by name or what they write about.',
        'groups.search': 'Search groups...',
        'groups.admin': 'Admin',
        'groups.member': 'Member',
        'groups.pending': 'Pending',
        'groups.join': 'Join',
        'groups.request_join': 'Request to join',
        'groups.joining': 'Joining...',
        'groups.sending': 'Sending...',
        'groups.public': 'Public',
        'groups.private': 'Private',
        'groups.public_help': 'Public group publications can be read by guests.',
        'groups.private_help': 'Private group publications are visible only to approved members.',
        'groups.visibility': 'Visibility',
        'groups.open_membership': 'Open membership',
        'groups.approval_required': 'Approval required',

        'group.create_title': 'Create new group',
        'group.name': 'Group name',
        'group.auto_approve': 'Automatically approve new members',
        'group.about': 'About group',
        'group.cancel': 'Cancel',
        'group.create': 'Create',
        'group.creating': 'Creating...',
        'group.members': '{count} members',
        'group.change_cover': 'Change cover',
        'group.settings': 'Group settings',
        'group.save': 'Save',
        'group.join': 'Join group',
        'group.sign_in_join': 'Sign in to join',

        'about.title': 'About Poet-Web',
        'about.subtitle': 'A place for poetry, authors, and communities.',
        'about.problem_title': 'Why Poet-Web?',
        'about.problem': 'Poetry is often stored across different places and published on platforms that are not designed specifically for authors. Poet-Web combines publishing, long-term storage, communities, and discussion in one system.',
        'about.rights_title': 'Author rights',
        'about.rights': 'Authors retain the rights to work they submit. Publishing on Poet-Web is not treated as transferring copyright ownership to the platform.',
        'about.access_title': 'Access levels',
        'about.access': 'Guests can read public content. Registered users can publish and interact. Private group content is available only to approved members.',
        'about.tech_title': 'Technology',
        'about.tech': 'Laravel, Vue, Inertia, Tailwind CSS, MySQL, and Docker/Sail with automated tests and CI.',
    },
};

function interpolate(value, replacements = {}) {
    return Object.entries(replacements).reduce(
        (text, [key, replacement]) =>
            text.replaceAll(
                `{${key}}`,
                String(replacement),
            ),
        value,
    );
}

export function useI18n() {
    const t = (key, replacements = {}) => {
        const value =
            messages[locale.value]?.[key]
            ?? messages.en[key]
            ?? key;

        return interpolate(value, replacements);
    };

    const setLocale = (value) => {
        if (!supportedLocales.includes(value)) {
            return;
        }

        locale.value = value;

        if (typeof window !== 'undefined') {
            localStorage.setItem('locale', value);
            document.documentElement.lang = value;
        }
    };

    return {
        locale: computed(() => locale.value),
        t,
        setLocale,
        supportedLocales,
    };
}
