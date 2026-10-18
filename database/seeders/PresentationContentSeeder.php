<?php

namespace Database\Seeders;

use App\Models\Bookmark;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Rich, original Latvian sample content for the qualification demonstration.
 *
 * Run only through PresentationDemoSeeder. This class intentionally does not
 * remove existing posts or change authentication details.
 */
class PresentationContentSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->command?->error('Presentation content is only for local/testing.');
            return;
        }

        $users = User::query()
            ->whereIn('email', [
                'elza@poetweb.lv',
                'janis@poetweb.lv',
                'marta@poetweb.lv',
                'rihards@poetweb.lv',
                'nora@poetweb.lv',
                'emils@poetweb.lv',
            ])
            ->get()
            ->keyBy('email');

        $groups = Group::query()
            ->whereIn('name', ['Cēsu Dzejas Telpa', 'Klusie Melnraksti', 'Jaunie Autori'])
            ->get()
            ->keyBy('name');

        if ($users->count() !== 6 || $groups->count() !== 3) {
            throw new RuntimeException(
                'Run PresentationDemoSeeder first to create demonstration authors and groups.'
            );
        }

        $poems = [
            [
                'author' => 'marta@poetweb.lv',
                'title' => 'Kad pilsēta aizver logus',
                'caption' => 'Pilsētas vakars, kas šķiet klusāks par domām.',
                'genres' => ['urban', 'reflection'],
                'tags' => ['vakars', 'pilseta', 'klusums'],
                'lines' => [
                    'Kad pilsēta aizver logus,',
                    'vējš vēl ilgi pārlasa ielas.',
                    'Uz slapjā bruģa atspīd debesis,',
                    'kuras neviens nav paspējis aizvērt.',
                ],
            ],
            [
                'author' => 'emils@poetweb.lv',
                'title' => 'Pirmā sniega vēstule',
                'caption' => 'Neliels dzejolis par pirmo sniegu un māju sajūtu.',
                'genres' => ['nature', 'nostalgia'],
                'tags' => ['ziema', 'sniegs', 'majas'],
                'lines' => [
                    'Pirmais sniegs uz palodzes',
                    'raksta vēstuli bez vārdiem.',
                    'Rīts to atver uzmanīgi,',
                    'lai netraucētu baltumam.',
                ],
            ],
            [
                'author' => 'janis@poetweb.lv',
                'title' => 'Vilciens uz ziemeļiem',
                'caption' => 'Ceļš, kas ved ne tikai uz nākamo staciju.',
                'genres' => ['reflection', 'travel'],
                'tags' => ['vilciens', 'cels', 'domas'],
                'lines' => [
                    'Stacijas paliek aiz muguras',
                    'kā lapas nepabeigtā grāmatā.',
                    'Vilciens ved mani uz ziemeļiem,',
                    'bet domas jau ir atgriezušās mājās.',
                ],
            ],
            [
                'author' => 'elza@poetweb.lv',
                'title' => 'Kafija un novembris',
                'caption' => 'Vienkāršs brīdis, kurā atrodas miers.',
                'genres' => ['everyday', 'reflection'],
                'tags' => ['rudens', 'kafija', 'miers'],
                'lines' => [
                    'Kafija atdziest pie loga,',
                    'novembris ievelk elpu.',
                    'Es nesteidzos viņu pārtraukt:',
                    'dažreiz klusums ir pilns teikums.',
                ],
            ],
            [
                'author' => 'nora@poetweb.lv',
                'title' => 'Gaisma aiz priedēm',
                'caption' => 'Dabas motīvs par gaismu un pacietību.',
                'genres' => ['nature', 'hope'],
                'tags' => ['mezi', 'gaisma', 'ceriba'],
                'lines' => [
                    'Aiz priedēm pamazām atmostas saule,',
                    'bez skaņas, bez steigas, bez vārda.',
                    'Tā zina: visgarākās ēnas',
                    'ir tikai ceļš līdz rītam.',
                ],
            ],
            [
                'author' => 'rihards@poetweb.lv',
                'title' => 'Uz vecā tilta',
                'caption' => 'Par pazīstamu vietu un mainīgu skatījumu.',
                'genres' => ['nature', 'memory'],
                'tags' => ['tilts', 'upe', 'atminas'],
                'lines' => [
                    'Uz vecā tilta stāv vējš,',
                    'un upe zem viņa nesteidzas.',
                    'Mēs katru reizi ejam pāri citādi,',
                    'bet krasti paliek turpat.',
                ],
            ],
            [
                'author' => 'marta@poetweb.lv',
                'title' => 'Nenosūtīta pastkarte',
                'caption' => 'Brīvā panta darbs par vārdiem, kuri palika neizteikti.',
                'genres' => ['reflection', 'memory'],
                'tags' => ['vestule', 'atminas'],
                'lines' => [
                    'Uz galda guļ pastkarte',
                    'ar adresi, ko nezinu.',
                    'Es uzrakstu tikai: viss ir labi,',
                    'un atstāju vietu patiesībai.',
                ],
            ],
            [
                'author' => 'elza@poetweb.lv',
                'title' => 'Vasaras bibliotēka',
                'caption' => 'Par grāmatām, sauli un ilgām pēc vasaras.',
                'genres' => ['summer', 'nostalgia'],
                'tags' => ['vasara', 'gramatas'],
                'lines' => [
                    'Grāmatas smaržo pēc karstas dienas,',
                    'uz lapām nosēžas liepu ēnas.',
                    'Vasara lasa pār manu plecu,',
                    'un neviens nelūdz aizvērt grāmatu.',
                ],
            ],
            [
                'author' => 'janis@poetweb.lv',
                'title' => 'Piektdienas lietus',
                'caption' => 'Mazs ikdienas skats, kurā atklājas ritms.',
                'genres' => ['urban', 'everyday'],
                'tags' => ['lietus', 'ritms'],
                'lines' => [
                    'Piektdienas lietus sit logā',
                    'tik vienmērīgi kā pulkstenis.',
                    'Visi skrien uz mājām,',
                    'bet peļķes vēl brīdi paliek skatīties.',
                ],
            ],
            [
                'author' => 'nora@poetweb.lv',
                'title' => 'Maigākais teikums',
                'caption' => 'Par uzmanību, kuru var izteikt pavisam vienkārši.',
                'genres' => ['friendship', 'reflection'],
                'tags' => ['draudziba', 'vardi'],
                'lines' => [
                    'Ne katram jautājumam vajag atbildi.',
                    'Reizēm pietiek ar krēslu blakus',
                    'un vārdiem: es klausos,',
                    'lai pasaule kļūtu drusku vieglāka.',
                ],
            ],
            [
                'author' => 'emils@poetweb.lv',
                'title' => 'Pirms rītausmas',
                'caption' => 'Pāris rindas par vēlmi sākt no jauna.',
                'genres' => ['hope', 'reflection'],
                'tags' => ['rits', 'sakaums'],
                'lines' => [
                    'Pirms rītausmas viss ir nepabeigts:',
                    'ceļš, doma, neaizvērta grāmata.',
                    'Tad gaisma uzliek punktu',
                    'un tūlīt sāk nākamo teikumu.',
                ],
            ],
            [
                'author' => 'rihards@poetweb.lv',
                'title' => 'Pilsētas pulkstenis',
                'caption' => 'Par laiku, kas vienmēr kustas vienā virzienā.',
                'genres' => ['urban', 'reflection'],
                'tags' => ['laiks', 'pilseta'],
                'lines' => [
                    'Laukuma pulkstenis skaita stundas,',
                    'bet cilvēki — tikšanās.',
                    'Vienā un tajā pašā minūtē',
                    'kāds ierodas, kāds dodas prom.',
                ],
            ],
            [
                'author' => 'marta@poetweb.lv',
                'title' => 'Mazā rudens karte',
                'caption' => 'Veltījums Cēsu ielu krāsām.',
                'genres' => ['nature', 'urban'],
                'tags' => ['cesis', 'rudens'],
                'group' => 'Cēsu Dzejas Telpa',
                'lines' => [
                    'Kļavu lapas rāda ceļu',
                    'pāri pilsētas bruģim.',
                    'Rudens mums atstāj karti,',
                    'kuru vējš tūlīt pārzīmē.',
                ],
            ],
            [
                'author' => 'elza@poetweb.lv',
                'title' => 'Lasījums sestdienā',
                'caption' => 'Aicinājums jauno autoru kopīgajam lasījumam.',
                'genres' => ['community', 'hope'],
                'tags' => ['dzeja', 'kopiena'],
                'group' => 'Jaunie Autori',
                'lines' => [
                    'Viena balss sāk teikumu,',
                    'otra palīdz tam turpināties.',
                    'Kad dzejolis ir izlasīts,',
                    'istabā vēl mazliet paliek tā gaisma.',
                ],
            ],
            [
                'author' => 'nora@poetweb.lv',
                'title' => 'Piezīmes tikai savējiem',
                'caption' => 'Privātas grupas saturs, ko viesi neredz.',
                'genres' => ['reflection'],
                'tags' => ['darbnica', 'idejas'],
                'group' => 'Klusie Melnraksti',
                'lines' => [
                    'Šeit teikumi vēl aug,',
                    'vēl meklē savu balsi.',
                    'Mēs tiem atstājam vietu,',
                    'pirms parādām tos pasaulei.',
                ],
            ],
        ];

        $created = [];

        foreach ($poems as $item) {
            $author = $users[$item['author']];
            $group = isset($item['group']) ? $groups[$item['group']] : null;

            // The author/title pair gives each demo poem a stable identity.
            $post = Post::query()->firstOrCreate(
                ['user_id' => $author->id, 'title' => $item['title']],
                [
                    'group_id' => $group?->id,
                    'type' => 'poem',
                    'content_rating' => 'general',
                    'poem_form' => 'free_verse',
                    'poem_genres' => $item['genres'],
                    'caption' => $item['caption'],
                    'hashtags' => $item['tags'],
                    'body' => '<p>' . implode('<br>', $item['lines']) . '</p>',
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );

            $created[$item['title']] = $post;
        }

        // One unfinished work makes the writing studio/demo drafts look real.
        Post::query()->firstOrCreate(
            ['user_id' => $users['marta@poetweb.lv']->id, 'title' => 'Melnraksts: Vēstule pavasarim'],
            [
                'type' => 'poem',
                'content_rating' => 'general',
                'poem_form' => 'free_verse',
                'poem_genres' => ['nature'],
                'caption' => 'Vēl nav publicēts — saglabāts tikai autoram.',
                'hashtags' => ['pavasaris'],
                'body' => '<p>Es vēl meklēju pirmo rindu,<br>ar kuru uzaicināt pavasari.</p>',
                'status' => 'draft',
                'draft_saved_at' => now(),
            ]
        );

        $commentExamples = [
            ['Kad pilsēta aizver logus', 'elza@poetweb.lv', 'Ļoti dzīva pilsētas noskaņa — īpaši patīk pēdējā rinda.'],
            ['Pirmā sniega vēstule', 'marta@poetweb.lv', 'Tik vienkārši un tēlaini! Šo gribētos dzirdēt arī lasījumā.'],
            ['Uz vecā tilta', 'janis@poetweb.lv', 'Skaists novērojums par vietām un atmiņām.'],
            ['Vasaras bibliotēka', 'nora@poetweb.lv', 'Lasot gandrīz var sajust liepu smaržu.'],
            ['Mazā rudens karte', 'rihards@poetweb.lv', 'Cēsis šajā gadalaikā tiešām izskatās tieši tā.'],
            ['Lasījums sestdienā', 'marta@poetweb.lv', 'Laba ideja — nākamreiz varam sarīkot tematisku lasījumu.'],
            ['Maigākais teikums', 'emils@poetweb.lv', 'Šis ir īss, bet paliek atmiņā.'],
        ];

        foreach ($commentExamples as [$title, $email, $comment]) {
            Comment::query()->firstOrCreate([
                'post_id' => $created[$title]->id,
                'user_id' => $users[$email]->id,
                'parent_id' => null,
                'comment' => $comment,
            ]);
        }

        $readerEmails = [
            'elza@poetweb.lv',
            'janis@poetweb.lv',
            'marta@poetweb.lv',
            'rihards@poetweb.lv',
            'nora@poetweb.lv',
            'emils@poetweb.lv',
        ];

        $index = 0;
        foreach ($created as $poem) {
            // Each visible publication has believable, non-zero interaction.
            foreach (array_slice($readerEmails, 0, 2 + ($index % 4)) as $email) {
                $reader = $users[$email];
                if ($reader->id !== $poem->user_id && $poem->isVisibleTo($reader->id)) {
                    $poem->reactions()->firstOrCreate(
                        ['user_id' => $reader->id],
                        ['type' => 'like']
                    );
                }
            }
            $index++;
        }

        foreach ([
            ['marta@poetweb.lv', 'Kad pilsēta aizver logus'],
            ['elza@poetweb.lv', 'Pirmā sniega vēstule'],
            ['janis@poetweb.lv', 'Vasaras bibliotēka'],
        ] as [$email, $title]) {
            Bookmark::query()->firstOrCreate([
                'user_id' => $users[$email]->id,
                'post_id' => $created[$title]->id,
            ]);
        }

        $this->demoConversation(
            $users['elza@poetweb.lv'],
            $users['marta@poetweb.lv'],
            [
                ['elza@poetweb.lv', 'Sveika, Marta! Vai sestdienas dzejas lasījums vēl ir spēkā?'],
                ['marta@poetweb.lv', 'Jā, noteikti! Varētu sākt ar īsajiem dzejoļiem.'],
                ['elza@poetweb.lv', 'Lieliski — ielikšu atgādinājumu grupā.'],
            ]
        );

        $this->demoConversation(
            $users['janis@poetweb.lv'],
            $users['rihards@poetweb.lv'],
            [
                ['janis@poetweb.lv', 'Izlasīju tavu dzejoli par tiltu. Patika noskaņa!'],
                ['rihards@poetweb.lv', 'Paldies! Biju iedvesmojies no pastaigas pie upes.'],
            ]
        );

        $this->command?->info('Additional original poems and social demo content are ready.');
    }

    private function demoConversation(User $first, User $second, array $messages): void
    {
        $conversation = Conversation::query()
            ->whereHas('users', fn ($query) => $query->where('users.id', $first->id))
            ->whereHas('users', fn ($query) => $query->where('users.id', $second->id))
            ->get()
            ->first(fn ($record) => $record->users()->count() === 2);

        if (!$conversation) {
            $conversation = Conversation::query()->create();
            $conversation->users()->attach([
                $first->id => ['last_read_at' => now()],
                $second->id => ['last_read_at' => now()],
            ]);
        }

        foreach ($messages as [$email, $body]) {
            $author = $first->email === $email ? $first : $second;
            Message::query()->firstOrCreate([
                'conversation_id' => $conversation->id,
                'user_id' => $author->id,
                'body' => $body,
            ]);
        }
    }
}
