# Localization and Language Flow

## Overview

Poet-Web includes a lightweight bilingual interface for the main qualification-presentation flow.

Supported interface languages:

```text
lv → Latvian
en → English
```

Latvian is the default when the browser has no saved Poet-Web language preference.

## Implementation

The shared language helper is:

```text
resources/js/i18n.js
```

It exposes:

```js
useI18n()
```

with:

```text
locale
t()
setLocale()
supportedLocales
```

The implementation intentionally uses a small custom composable instead of adding a larger localization dependency.

## Initial language

On page load:

```text
localStorage.locale exists and is supported
→ use saved locale

otherwise
→ use lv
```

The selected locale is also written to:

```text
document.documentElement.lang
```

which keeps the HTML language attribute aligned with the active interface language.

## Changing language

The shared navigation and guest layout provide:

```text
LV
EN
```

controls.

Selecting a language:

1. updates the reactive Vue locale;
2. stores the choice in `localStorage`;
3. updates the document language;
4. immediately changes translated strings using the same reactive state.

The selection therefore persists while navigating between Inertia pages and after refreshing the browser.

## Current coverage

The presentation-facing flow is translated across:

- primary navigation;
- guest navigation;
- Home/Explore;
- search;
- login;
- registration;
- profile presentation flow;
- post/poem publication modal;
- poem writing studio;
- comments;
- group discovery;
- group creation;
- group profile/settings;
- About / rights page.

## Deliberate limitation

Localization is not yet a full translation of every low-traffic screen, every server-side validation message, or every notification/email.

Therefore the accurate project claim is:

> Poet-Web supports a Latvian-first bilingual main interface and presentation flow.

It should not be described as complete application-wide localization until every remaining string and backend message is moved into a shared translation system.

## Main files

```text
resources/js/i18n.js
resources/js/Layouts/AuthenticatedLayout.vue
resources/js/Layouts/GuestLayout.vue
resources/js/Pages/Home.vue
resources/js/Pages/Search.vue
resources/js/Pages/Auth/Login.vue
resources/js/Pages/Auth/Register.vue
resources/js/Pages/Profile/View.vue
resources/js/Pages/Group/Index.vue
resources/js/Pages/Group/View.vue
resources/js/Components/app/GroupModal.vue
resources/js/Components/app/PostModal.vue
resources/js/Components/app/CommentList.vue
resources/js/Pages/Poem/Write.vue
resources/js/Pages/About.vue
```
