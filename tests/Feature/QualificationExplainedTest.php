<?php

/**
 * POET-WEB — pieci paskaidroti Pest Feature testi kvalifikācijas darbam.
 *
 * Katrs tests atbilst vienai 2.1. nodaļas funkcionālajai prasībai.
 * Tests/Pest.php nodrošina TestCase, RefreshDatabase un poetTest* palīgfunkcijas.
 *
 * SVARĪGI: Feature testi pārbauda HTTP pieprasījumus un datubāzi,
 * nevis klikšķina Vue saskarni kā pārlūkprogrammas testi.
 */

use App\Enums\GroupUserStatus;
use App\Models\Follower;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

// 6.1. — Prasība 2.1.1: dzejas melnraksta izveide un saglabāšana.
test('KVD 01 - autors var saglabat dzejas melnrakstu', function () {
    // SAGATAVOŠANA — testa lietotājs (nevis reāls konts).
    $author = User::factory()->create();

    // DARBĪBA — autentificēts autors nosūta POST /drafts.
    $response = $this->actingAs($author)->postJson(
        route('draft.store'),
        poetTestPostPayload([
            'type' => 'poem',
            'poem_form' => 'free_verse',
            'title' => 'Nepabeigts dzejolis',
            'body' => '<p>Pirmā rinda</p>',
        ])
    );

    // PĀRBAUDE #1 — HTTP 201 un JSON satur saglabātā melnraksta ID.
    $response->assertCreated()->assertJsonStructure([
        'draft' => ['id', 'draft_saved_at'],
    ]);

    // PĀRBAUDE #2 — datubāzē ieraksts ir melnraksts, nevis publikācija.
    $this->assertDatabaseHas('posts', [
        'user_id' => $author->id,
        'title' => 'Nepabeigts dzejolis',
        'status' => 'draft',
    ]);
});

// 6.2. — Prasība 2.1.2: dzejoļa publicēšana.
test('KVD 02 - autors var publicet dzejoli', function () {
    $author = User::factory()->create();

    // DARBĪBA — POST /posts ar derīgu dzejoļa tekstu.
    $this->actingAs($author)->post(
        route('post.create'),
        poetTestPostPayload([
            'type' => 'poem',
            'poem_form' => 'free_verse',
            'title' => 'Publicēts dzejolis',
            'body' => '<p>Šis ir mans dzejolis.</p>',
        ])
    )->assertSessionHasNoErrors();

    // PĀRBAUDE — autora dzejolis ir saglabāts kā publicēts ieraksts.
    $this->assertDatabaseHas('posts', [
        'user_id' => $author->id,
        'title' => 'Publicēts dzejolis',
        'status' => 'published',
    ]);
});

// 6.3. — Prasība 2.1.3: publikācijas pārvaldīšana un piekļuves tiesības.
test('KVD 03 - tikai tiesigs lietotajs var dzest publikaciju', function () {
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $post = poetTestPost($author);

    // SVEŠS LIETOTĀJS — dzēšanas mēģinājumam jāatgriež 403.
    $this->actingAs($stranger)
        ->delete(route('post.destroy', $post))
        ->assertForbidden();

    // Ierakstam vēl nav dzēšanas laika.
    expect($post->fresh()->deleted_at)->toBeNull();

    // AUTORS — drīkst dzēst savu publikāciju.
    $this->actingAs($author)
        ->delete(route('post.destroy', $post))
        ->assertRedirect();

    // "Soft delete": ieraksts pastāv, bet tam ir deleted_at.
    expect(Post::withTrashed()->findOrFail($post->id)->trashed())
        ->toBeTrue();
});

// 6.4. — Prasība 2.1.4: sekošana autoriem bez dublikātiem.
test('KVD 04 - atkartota sekosana neveido dublikatu', function () {
    // Fiktīvi paziņojumi — netiek sūtīti īsti paziņojumi.
    Notification::fake();
    $author = User::factory()->create();
    $reader = User::factory()->create();

    // Divi sekošanas pieprasījumi vienam un tam pašam autoram.
    for ($attempt = 1; $attempt <= 2; $attempt++) {
        $this->actingAs($reader)->post(
            route('user.follow', $author),
            ['follow' => true]
        )->assertSessionHasNoErrors();
    }

    // Datubāzē drīkst būt tikai viena sekošanas saistība.
    $count = Follower::query()
        ->where('user_id', $author->id)
        ->where('follower_id', $reader->id)
        ->count();

    expect($count)->toBe(1);
});

// 6.5. — Prasība 2.1.5: grupas dalības pieteikšana un apstiprināšana.
test('KVD 05 - grupas administrators apstiprina dalibas pieprasijumu', function () {
    Notification::fake();
    $owner = User::factory()->create();
    $applicant = User::factory()->create();
    $group = poetTestGroup($owner, ['auto_approval' => false]);

    // DALĪBNIEKS — iesniedz dalības pieprasījumu.
    $this->actingAs($applicant)
        ->post(route('group.join', $group))
        ->assertSessionHasNoErrors();

    $membership = GroupUser::query()
        ->where('group_id', $group->id)
        ->where('user_id', $applicant->id)
        ->firstOrFail();

    // Kamēr administrators neapstiprina, statuss ir pending.
    expect($membership->status)->toBe(GroupUserStatus::PENDING->value);

    // ADMINISTRATORS — apstiprina pieteikumu.
    $this->actingAs($owner)->post(
        route('group.resolveJoinRequest', $group),
        ['user_id' => $applicant->id, 'action' => 'approve']
    )->assertSessionHasNoErrors();

    // Pēc apstiprināšanas statuss datubāzē ir approved.
    expect($membership->fresh()->status)
        ->toBe(GroupUserStatus::APPROVED->value);
});
