# Poet-Web: pieci skaidroti Pest Feature testi (kvalifikācijas darbs)

Testu pirmkods: [tests/Feature/QualificationExplainedTest.php](../../tests/Feature/QualificationExplainedTest.php).

Šie testi atbilst piecām 2.1. nodaļā aprakstītajām funkcijām. **Tie nav pārlūkprogrammas testi:** Pest nosūta HTTP pieprasījumus Laravel lietotnei un pārbauda datubāzi un atbildes. Tie nedemonstrē reālu peles klikšķu secību Vue saskarnē.

## Pirms palaišanas

1. Atjaunini projektu: \`git pull origin main\`.
2. Pārbaudi, ka Docker/Sail darbojas: \`./vendor/bin/sail up -d\`.
3. **Pārliecinies, ka testi darbojas atsevišķā testēšanas datubāzē, nevis īstajā lietotnes datubāzē.** Projekta \`tests/Pest.php\` izmanto \`RefreshDatabase\`, kas atsvaidzina testu tabulas. \`phpunit.xml\` norāda testu datubāzes nosaukumu \`testing\`, taču faktisko savienojumu pārbaudi savā vidē pirms testēšanas. Testu datubāzē jābūt atbilstošām migrācijām un piekļuves tiesībām.
4. Palaid failu:

\`\`\`bash
./vendor/bin/sail artisan test tests/Feature/QualificationExplainedTest.php
\`\`\`

Vienu testu (piemēram, 6.1) var palaist atsevišķi:

\`\`\`bash
./vendor/bin/sail artisan test tests/Feature/QualificationExplainedTest.php --filter="KVD 01"
\`\`\`

Ja rodas kļūda, saglabā pilnu termināļa izvadi. **Neieraksti dokumentācijā, ka tests ir veiksmīgs, pirms esi to palaidis.**

## Testu uzbūve

| Dok. nodaļa | Tests | Kas tiek pārbaudīts? | Sagaidāmais |
|---|---|---|---|
| 6.1 / 2.1.1 | KVD 01 — melnraksts | Autors nosūta \`POST /drafts\`; dati tiek saglabāti | HTTP 201, atbildē ID un \`draft_saved_at\`, DB \`status=draft\` |
| 6.2 / 2.1.2 | KVD 02 — publicēšana | Autors nosūta \`POST /posts\` ar derīgu tekstu | Bez validācijas kļūdām, DB \`status=published\` |
| 6.3 / 2.1.3 | KVD 03 — dzēšana | Svešinieks mēģina dzēst autora darbu; pēc tam dzēš autors | Svešiniekam 403; autoram pieprasījums izdodas un ir \`deleted_at\` |
| 6.4 / 2.1.4 | KVD 04 — sekošana | Vienam autoram divreiz nosūta sekošanas pieprasījumu | Tabulā \`followers\` ir tieši viens attiecīgais ieraksts |
| 6.5 / 2.1.5 | KVD 05 — grupa | Dalības pieprasījumu apstiprina grupas administrators | \`pending\` pirms apstiprinājuma un \`approved\` pēc tā |

### Kā lasīt biežāk lietotās Pest metodes

- \`User::factory()->create()\` — izveido izmēģinājuma lietotāju testa datubāzē.
- \`$this->actingAs($user)\` — nākamo pieprasījumu izpilda šī lietotāja vārdā.
- \`route('draft.store')\` — atrod Laravel maršrutu pēc nosaukuma.
- \`post()\` / \`postJson()\` / \`delete()\` — nosūta HTTP pieprasījumu bez īstas pārlūkprogrammas.
- \`assertCreated()\` — pieprasa HTTP atbildi 201.
- \`assertForbidden()\` — pieprasa HTTP atbildi 403 (nav tiesību).
- \`assertSessionHasNoErrors()\` — pārbauda, ka sesijā nav ievades datu validācijas kļūdu.
- \`assertDatabaseHas('posts', [...])\` — pārbauda, vai tabulā ir ieraksts ar norādītajām vērtībām.
- \`expect(...)->toBe(...)\` — salīdzina faktisko vērtību ar sagaidāmo.
- \`Notification::fake()\` — testā neļauj nosūtīt īstus paziņojumus.
- \`poetTestPostPayload()\`, \`poetTestPost()\`, \`poetTestGroup()\` — projekta \`tests/Pest.php\` definētās palīgfunkcijas.

## Kā sagatavot kvalifikācijas darba 6. nodaļu

Katrā no piecām apakšnodaļām iekļauj:
1. Testa veidu (**automatizēts Pest Feature tests**) un mērķi.
2. Saikni ar 2.1. nodaļas konkrēto funkcionālo prasību.
3. Koda attēlu ar **numurētu nosaukumu**, atsauci pirms attēla un skaidrojumu zem tā.
4. Sagaidāmo rezultātu ar konkrētu \`assert...\` vai \`expect(...)\` piemēru.
5. **Reāla testa palaišanas ekrānattēlu** ar numurētu nosaukumu.
6. Faktisko rezultātu un secinājumu par to, vai prasība ir izpildīta.

Pēc katra testa izpildes nofotografē vai saglabā VS Code/termināļa logu, kur redzams testa nosaukums un \`PASS\` vai kļūda. Testu veiksmīgu izpildi nedrīkst apliecināt ar izdomātu ekrānattēlu.

### Piezīme par segumu

Pieci testi demonstrē svarīgus scenārijus, bet **nenozīmē, ka visa platforma ir notestēta**. Pieejas tiesību, XSS, privātu grupu un citiem riskiem repozitorijā ir papildu Feature testi; pārlūkprogrammas interakcijām var veidot atsevišķus browser testus.
