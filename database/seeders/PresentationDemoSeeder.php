<?php

namespace Database\Seeders;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Models\Comment;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresentationDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->command?->error(
                'PresentationDemoSeeder is intended only for local/demo environments.'
            );

            return;
        }

        DB::transaction(function () {
            $users = collect([
                ['name' => 'Elza Ozola', 'email' => 'elza@poetweb.lv', 'is_admin' => true],
                ['name' => 'Jānis Vējš', 'email' => 'janis@poetweb.lv'],
                ['name' => 'Marta Liepa', 'email' => 'marta@poetweb.lv'],
                ['name' => 'Rihards Kalns', 'email' => 'rihards@poetweb.lv'],
                ['name' => 'Nora Dauga', 'email' => 'nora@poetweb.lv'],
                ['name' => 'Emīls Straume', 'email' => 'emils@poetweb.lv'],
            ])->mapWithKeys(function (array $data) {
                $user = User::query()->firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => 'Demo1234!',
                        'email_verified_at' => now(),
                        'is_admin' => $data['is_admin'] ?? false,
                    ]
                );

                return [$data['email'] => $user];
            });

            $elza = $users['elza@poetweb.lv'];
            $janis = $users['janis@poetweb.lv'];
            $marta = $users['marta@poetweb.lv'];
            $rihards = $users['rihards@poetweb.lv'];
            $nora = $users['nora@poetweb.lv'];
            $emils = $users['emils@poetweb.lv'];

            $publicGroup = $this->group(
                'Cēsu Dzejas Telpa',
                $elza,
                'public',
                true,
                'Publiska dzejas kopiena lasījumiem, īsformai un autoru sarunām Cēsīs.'
            );

            $privateGroup = $this->group(
                'Klusie Melnraksti',
                $janis,
                'private',
                false,
                'Privāta darba grupa melnrakstiem, atsauksmēm un nepabeigtiem tekstiem.'
            );

            $youngAuthors = $this->group(
                'Jaunie Autori',
                $marta,
                'public',
                false,
                'Vieta jaunajiem autoriem, kuri vēlas publicēt dzeju un saņemt konstruktīvas atsauksmes.'
            );

            $this->member($publicGroup, $elza, GroupUserRole::ADMIN->value);
            $this->member($publicGroup, $marta);
            $this->member($publicGroup, $rihards);

            $this->member($privateGroup, $janis, GroupUserRole::ADMIN->value);
            $this->member($privateGroup, $nora);

            $this->member($youngAuthors, $marta, GroupUserRole::ADMIN->value);
            $this->member($youngAuthors, $elza);
            $this->member(
                $youngAuthors,
                $emils,
                GroupUserRole::MEMBER->value,
                GroupUserStatus::PENDING->value
            );

            $firstPoem = $this->publication($elza, [
                'type' => 'poem',
                'title' => 'Rīts pie Gaujas',
                'caption' => 'Īss brīvā panta darbs par rudens rītu.',
                'hashtags' => ['cesis', 'rudens', 'gauja'],
                'poem_form' => 'free_verse',
                'poem_genres' => ['nature', 'reflection'],
                'body' => '<p>Rīts saloka miglu pār upi,<br>un pilsēta mostas bez steigas.<br>Uz tilta paliek tikai soļi,<br>kas zina ceļu uz gaismu.</p>',
            ]);

            $this->publication($janis, [
                'type' => 'poem',
                'title' => 'Starp pieturām',
                'caption' => 'Par ceļu, kurā domas skrien ātrāk par autobusu.',
                'hashtags' => ['cela', 'klusums'],
                'poem_form' => 'free_verse',
                'poem_genres' => ['reflection'],
                'body' => '<p>Logā pilsētas atkāpjas,<br>bet viena doma paliek sēdēt blakus.<br>Es tai neprasu biļeti.</p>',
            ]);

            $this->publication($marta, [
                'type' => 'post',
                'body' => '<p>Šovakar meklēju cilvēkus, kuri grib kopā pārrunāt īsformas dzeju. Iemetiet savu mīļāko paša rakstīto rindu komentārā.</p>',
                'hashtags' => ['saruna', 'dzeja'],
            ]);

            $publicGroupPost = $this->publication($rihards, [
                'group_id' => $publicGroup->id,
                'type' => 'poem',
                'title' => 'Laternu logs',
                'caption' => 'Publiskas grupas piemērs.',
                'hashtags' => ['gaisma', 'vakars'],
                'poem_form' => 'free_verse',
                'poem_genres' => ['urban', 'reflection'],
                'body' => '<p>Vakarā logos iedegas mazas pasaules,<br>katra ar savu klusumu un tēju.</p>',
            ]);

            $privateGroupPost = $this->publication($nora, [
                'group_id' => $privateGroup->id,
                'type' => 'poem',
                'title' => 'Melnraksts bez punkta',
                'caption' => 'Šis darbs ir redzams tikai apstiprinātiem privātās grupas dalībniekiem.',
                'hashtags' => ['melnraksts'],
                'poem_form' => 'free_verse',
                'poem_genres' => ['experimental'],
                'body' => '<p>Es atstāju teikumu atvērtu,<br>lai rītdiena pati pieliek punktu.</p>',
            ]);

            $this->publication($elza, [
                'group_id' => $youngAuthors->id,
                'type' => 'post',
                'body' => '<p>Jaunie autori — sestdien publicējam vienu īsu darbu un dodam vismaz vienu konstruktīvu atsauksmi citam autoram.</p>',
                'hashtags' => ['jaunieautori'],
            ]);

            $comment = Comment::query()->firstOrCreate([
                'post_id' => $firstPoem->id,
                'user_id' => $marta->id,
                'comment' => 'Ļoti patīk noskaņa un vienkāršais ritms.',
            ]);

            Comment::query()->create([
                'post_id' => $firstPoem->id,
                'user_id' => $elza->id,
                'parent_id' => $comment->id,
                'comment' => 'Paldies! Tieši mierīgu ritmu arī gribēju saglabāt.',
            ]);

            Comment::query()->create([
                'post_id' => $publicGroupPost->id,
                'user_id' => $elza->id,
                'comment' => 'Labs piemērs grupas publiskajai plūsmai.',
            ]);

            Comment::query()->create([
                'post_id' => $privateGroupPost->id,
                'user_id' => $janis->id,
                'comment' => 'Privātajā grupā šo varam attīstīt tālāk.',
            ]);

            $firstPoem->reactions()->firstOrCreate([
                'user_id' => $janis->id,
                'type' => 'like',
            ]);

            $firstPoem->reactions()->firstOrCreate([
                'user_id' => $rihards->id,
                'type' => 'like',
            ]);

            $publicGroupPost->reactions()->firstOrCreate([
                'user_id' => $marta->id,
                'type' => 'like',
            ]);

            $elza->followings()->syncWithoutDetaching([
                $janis->id,
                $marta->id,
            ]);

            $marta->followings()->syncWithoutDetaching([
                $elza->id,
            ]);

            $rihards->followings()->syncWithoutDetaching([
                $elza->id,
            ]);

            $elza->pinned_post_id = $firstPoem->id;
            $elza->save();

            $publicGroup->pinned_post_id = $publicGroupPost->id;
            $publicGroup->save();
        });

        $this->call(PresentationContentSeeder::class);

        $this->command?->newLine();
        $this->command?->info('Poet-Web presentation demo data is ready.');
        $this->command?->table(
            ['Demo account', 'Password'],
            [
                ['elza@poetweb.lv', 'Demo1234!'],
                ['janis@poetweb.lv', 'Demo1234!'],
                ['marta@poetweb.lv', 'Demo1234!'],
            ]
        );
        $this->command?->warn(
            'Demo credentials are for local presentation use only.'
        );
    }

    private function group(
        string $name,
        User $owner,
        string $visibility,
        bool $autoApproval,
        string $about
    ): Group {
        return Group::query()->updateOrCreate(
            ['name' => $name],
            [
                'user_id' => $owner->id,
                'visibility' => $visibility,
                'auto_approval' => $autoApproval,
                'about' => $about,
            ]
        );
    }

    private function member(
        Group $group,
        User $user,
        string $role = GroupUserRole::MEMBER->value,
        string $status = GroupUserStatus::APPROVED->value
    ): GroupUser {
        return GroupUser::query()->updateOrCreate(
            [
                'group_id' => $group->id,
                'user_id' => $user->id,
            ],
            [
                'role' => $role,
                'status' => $status,
                'created_by' => $group->user_id,
                'token' => null,
                'token_expire_date' => null,
                'token_used' => null,
            ]
        );
    }

    private function publication(
        User $author,
        array $attributes
    ): Post {
        return Post::query()->firstOrCreate([
            'user_id' => $author->id,
            'title' => $attributes['title'] ?? null,
            'group_id' => $attributes['group_id'] ?? null,
            'type' => $attributes['type'] ?? 'post',
        ], array_merge([
            'user_id' => $author->id,
            'group_id' => null,
            'type' => 'post',
            'content_rating' => 'general',
            'poem_form' => null,
            'poem_genres' => [],
            'title' => null,
            'caption' => null,
            'hashtags' => [],
            'body' => '<p>Demo publication</p>',
            'status' => 'published',
            'published_at' => now(),
            'draft_saved_at' => null,
        ], $attributes));
    }
}
