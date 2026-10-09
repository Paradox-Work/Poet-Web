<?php

namespace Database\Seeders;

use App\Enums\GroupUserRole;
use App\Enums\GroupUserStatus;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SocialStressSeeder extends Seeder
{
    private const STRESS_EMAIL_PREFIX =
        'social-stress-';

    /**
     * Populate one existing account with enough
     * social data to stress-test the Social page.
     */
    public function run(): void
    {
        $defaultUser =
            User::query()
                ->where(
                    'email',
                    'not like',
                    self::STRESS_EMAIL_PREFIX . '%'
                )
                ->orderBy('id')
                ->first();

        if (!$defaultUser) {
            throw new RuntimeException(
                'Create at least one normal user before running SocialStressSeeder.'
            );
        }

        $email =
            $this->command?->ask(
                'Which user email should receive the Social stress-test data?',
                $defaultUser->email
            )
            ?? $defaultUser->email;

        $target =
            User::query()
                ->where(
                    'email',
                    $email
                )
                ->first();

        if (!$target) {
            throw new RuntimeException(
                'No user exists with email "' .
                $email .
                '".'
            );
        }

        $people =
            collect(
                $this->stressNames()
            )
                ->map(
                    function (
                        string $name,
                        int $index
                    ) {
                        $number =
                            str_pad(
                                (string) ($index + 1),
                                2,
                                '0',
                                STR_PAD_LEFT
                            );

                        return User::query()
                            ->updateOrCreate(
                                [
                                    'email' =>
                                        self::STRESS_EMAIL_PREFIX .
                                        $number .
                                        '@poet-web.test',
                                ],
                                [
                                    'name' => $name,
                                    'password' => 'password',
                                    'email_verified_at' => now(),
                                    'avatar_path' => null,
                                    'cover_path' => null,
                                ]
                            );
                    }
                )
                ->values();

        /*
         * 36 people the target follows.
         */
        $target
            ->followings()
            ->syncWithoutDetaching(
                $people
                    ->take(36)
                    ->pluck('id')
                    ->all()
            );

        /*
         * 32 followers.
         *
         * Starting from index 16 intentionally creates
         * mutual follows so both directions get tested.
         */
        $target
            ->followers()
            ->syncWithoutDetaching(
                $people
                    ->slice(16, 32)
                    ->pluck('id')
                    ->all()
            );

        $groupNames =
            $this->stressGroupNames();

        foreach (
            $groupNames
            as $index => $name
        ) {
            $targetIsAdmin =
                $index % 5 === 0;

            $owner =
                $targetIsAdmin
                    ? $target
                    : $people[
                        $index %
                        $people->count()
                    ];

            $group =
                Group::query()
                    ->updateOrCreate(
                        [
                            'name' => $name,
                        ],
                        [
                            'user_id' => $owner->id,
                            'auto_approval' =>
                                $index % 2 === 0,
                            'about' =>
                                'UI stress-test group for checking dense Social page layouts, long names, scrolling and card spacing.',
                            'cover_path' => null,
                            'thumbnail_path' => null,
                        ]
                    );

            /*
             * Keep every group structurally valid:
             * its owner is an approved admin.
             */
            GroupUser::query()
                ->updateOrCreate(
                    [
                        'user_id' => $owner->id,
                        'group_id' => $group->id,
                    ],
                    [
                        'status' =>
                            GroupUserStatus::APPROVED->value,
                        'role' =>
                            GroupUserRole::ADMIN->value,
                        'created_by' =>
                            $owner->id,
                        'token' => null,
                        'token_expire_date' => null,
                        'token_used' => null,
                    ]
                );

            /*
             * Put the target user in every stress group.
             * Some are admin memberships so Social also
             * has mixed role labels to render.
             */
            GroupUser::query()
                ->updateOrCreate(
                    [
                        'user_id' => $target->id,
                        'group_id' => $group->id,
                    ],
                    [
                        'status' =>
                            GroupUserStatus::APPROVED->value,
                        'role' =>
                            $targetIsAdmin
                                ? GroupUserRole::ADMIN->value
                                : GroupUserRole::MEMBER->value,
                        'created_by' =>
                            $owner->id,
                        'token' => null,
                        'token_expire_date' => null,
                        'token_used' => null,
                    ]
                );
        }

        $this->command?->newLine();

        $this->command?->info(
            'Social stress data added to ' .
            $target->email .
            '.'
        );

        $this->command?->table(
            [
                'Data',
                'Added',
            ],
            [
                [
                    'Following',
                    36,
                ],
                [
                    'Followers',
                    32,
                ],
                [
                    'Groups',
                    count($groupNames),
                ],
                [
                    'Synthetic users',
                    $people->count(),
                ],
            ]
        );

        $this->command?->warn(
            'Stress users use password "password". ' .
            'This seeder is intended for local development only.'
        );
    }

    /**
     * Varied lengths intentionally stress truncation,
     * wrapping and horizontal scrolling.
     */
    private function stressNames(): array
    {
        return [
            'Mara Ozola',
            'Aleksandrs Vītols',
            'Elīna',
            'Christopher Montgomery',
            'Nora Vale',
            'Rihards Kalniņš',
            'Amelia Rose Whitmore',
            'Jānis Bērziņš',
            'Luna Grey',
            'Sebastian Alexander Hart',
            'Sofija Liepa',
            'Theo North',
            'Katrīna Ziediņa',
            'Emilia Dawn',
            'Maksims Petrovs',
            'Isabella Marlowe',
            'Roberts Siliņš',
            'Noah Rivers',
            'Anna Marija Zariņa',
            'Felix Evernight',
            'Dāvis Krūmiņš',
            'Evelyn Ashford',
            'Mia',
            'Dominic Theodore Laurent',
            'Līva Jansone',
            'Oliver Reed',
            'Adriana Moon',
            'Tomass Meiers',
            'Charlotte Winterbourne',
            'Emīls',
            'Viktorija Priede',
            'Nathaniel Brooks',
            'Laura Straume',
            'Avery Stone',
            'Kristaps Vanags',
            'Penelope Florence Grey',
            'Matīss',
            'Iris Holloway',
            'Daniel Kalējs',
            'Sophia Wren',
            'Amanda Freimane',
            'Julian Everhart',
            'Rūta',
            'Benjamin Christopher Wells',
            'Alise Vētra',
            'Elliot Pagewood',
            'Markuss Lapiņš',
            'Genevieve Montgomery Hartwell',
        ];
    }

    private function stressGroupNames(): array
    {
        return [
            'Midnight Drafts',
            'Quiet Margins',
            'Glass & Ink',
            'Latvian Poetry Circle',
            'Writers After Midnight',
            'Tiny Poems',
            'The Unreasonably Long Experimental Poetry Collective',
            'Rainy Window Writers',
            'Free Verse Society',
            'Stories Between Stations',
            'Young Poets',
            'Dreams in Lowercase',
            'Nocturne',
            'Ink Without Borders',
            'Cēsis Writers Room',
            'Paper Lanterns',
            'Poetry & Coffee',
            'The Last Line',
            'Words We Never Sent',
            'Archive of Unfinished Things',
        ];
    }
}
