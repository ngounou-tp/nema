# NEMA — agent IA agricole pour le Cameroun

Plateforme web qui relie coopératives, producteurs et acheteurs camerounais, avec un agent IA (conseil agronomique, diagnostic photo, score de fiabilité, traçabilité, prévisions de prix). Démo en ligne le **25 octobre 2026**. Le plan complet est dans `docs/guide-implementation-nema.pdf` : c'est la référence pour le périmètre, les écrans et les règles.

## Rôles (`users.role`)

| Rôle | Ce qu'il fait | Compte de démo |
|---|---|---|
| `producer` | Diagnostic des cultures, conseil IA, publication d'offres | producteur@nema.demo |
| `cooperative` | Membres, récoltes, stocks, lots traçables | coop@nema.demo |
| `buyer` | Recherche, score de fiabilité, commandes, scan QR (entreprise ou particulier) | acheteur@nema.demo |
| `admin` | Modération, statistiques, journal de l'IA | admin@nema.demo |

Mot de passe des comptes de démo : `nema2026`.

## Stack

- Laravel 13 (PHP 8.4) + Inertia v3 + Vue 3 (TypeScript) + Tailwind 4 + shadcn-vue (starter kit officiel, Fortify pour l'authentification).
- Base de données : **SQLite dans les tests**, **PostgreSQL (Neon)** sinon — branche `dev` pour le développement, branche par défaut = production ; voir `docs/production-database.md`. L'app utilise la connexion `pgsql` (pooled, `PDO::ATTR_EMULATE_PREPARES` obligatoire : le pooler de Neon annule sinon toute transaction) et les migrations passent par `--database=pgsql_direct` (endpoint sans `-pooler`).
- IA : **Groq** pour le texte, **Gemini** pour la vision, OpenRouter en secours. Clés et modèles dans `config('services.groq|gemini|openrouter')`, comportement (ordre, quotas, délais, mode démo) dans `config('nema')` — voir « Couche IA ».
- Photos : jamais en base, seulement leur chemin ; disque `config('filesystems.photos')` (`local` en dev, `neon` = Neon Object Storage en production), affichées par `temporaryUrl()`.
- Autres : Sentry (erreurs), `bacon/bacon-qr-code` (QR des lots), `chart.js` + `vue-chartjs` (graphiques), `dompurify` (Markdown IA).

## Conventions

- Code, noms de tables, colonnes, classes et routes **en anglais**.
- **Application entièrement bilingue français / anglais** (les deux langues officielles du Cameroun) :
  - aucun texte d'interface en dur : textes dans `lang/fr.json` et `lang/en.json` (clés `nav.*`, `dashboard.*`…, mêmes clés dans les deux fichiers), libellés des enums dans `lang/{fr,en}/enums.php` (via `label()`), messages de Laravel dans `lang/{fr,en}/*.php` (laravel-lang) ;
  - côté Vue : `$t('cle')` / `trans()` de `laravel-vue-i18n` (lit les mêmes fichiers, PHP compris via le plugin Vite) ; titres passés en `defineOptions({ layout })` = des clés, traduites par le layout ;
  - langue choisie par `SetLocale` : `?lang` (pages publiques), puis `users.locale`, puis session, puis `Accept-Language`, puis `fr` ; sélecteur FR | EN (`LanguageSwitcher`, route `locale.update`) qui enregistre en session et dans `users.locale` ;
  - formats : `App\Support\Format` (PHP) et `resources/js/lib/format.ts` (JS), mêmes règles : « 12 500 FCFA » en fr, « 12,500 XAF » en en, dates `fr-CM` / `en-CM`, poids en kg puis t ;
  - l'IA répond dans la langue de l'utilisateur ; les contenus saisis par les utilisateurs (noms de produits, avis, lieux et origine des lots) restent dans leur langue d'origine ;
  - contrôle : `npm run lint:i18n` (texte en dur dans un `.vue`, clé absente de `fr.json`/`en.json`), aussi lancé par les tests Pest.
- Valeurs fixes en enums PHP dans `app/Enums`, stockées en **clés anglaises** et traduites à l'affichage : rôles, statuts, filières (`cocoa`, `maize`…), régions (`Region`), qualités, étapes de lot (`LotStep` : `harvest`, `drying`…).
- Logique métier dans `app/Services` (scores, prévisions, clients IA) ; les contrôleurs restent fins.
- Validation par **Form Requests**, autorisations par **Policies** (un producteur ne modifie que ses produits, une coopérative que ses membres).
- Tests **Pest** (`tests/Feature` surtout), modèles créés via leurs factories.
- L'IA explique, conseille ou résume ; elle ne calcule jamais seule un score ou un prix (calculés en PHP).
- Avant tout travail d'interface : skills `nema-product-ux`, `frontend-design`, `vue-best-practices`, `tailwindcss-development` ; audit avec `web-design-guidelines`, vérification dans un navigateur avec `webapp-testing`.

## Interface (charte « Broadsheet », maquettes de la phase 0)

- Thème clair uniquement. Papier `#f3f2f2`, surface `#eae9e9`, encre `#201e1d` ; cyan `brand-*` pour l'interactif (`brand-700` `#006786` : le cyan de base rate le contraste AA) ; magenta `alert-*` réservé aux problèmes (annulations, litiges, erreurs). Jetons dans `resources/css/app.css`, mappés sur les variables shadcn.
- Police Source Serif 4 partout (auto-hébergée), icônes Phosphor duotone (`@phosphor-icons/vue`), rayons de 1 à 4 px. Signatures : filet double `.rule-double`, ligne d'index avec pointillés (`StatLine`), dateline en tête des tableaux de bord.
- Composants de base : `ui/*` (shadcn restylés : `Button`, `Card`, `Badge` variantes `accent`/`alert`/`neutral`, `Alert` variante `info`, `Table`), `FormField`, `EmptyState`, `LoadingState`, `ScoreBadge` (≥ 80 très fiable, 60–79 fiable, < 60 à surveiller), `OrdersList` (tableau ou blocs selon la largeur de sa colonne, container query).
- Logo : `public/images/logo.svg` (ou `.png`) s'il existe, sinon le logotype « NEMA » (`brand.name`, indépendant de `APP_NAME`).
- `AppLayout` : barre latérale par rôle sur ordinateur, barre du bas sur mobile (`useNavigation` : 4 premiers liens + « Plus »), bandeau « données fictives » quand `NEMA_DEMO_MODE=true`.
- Accès : middleware `role:cooperative,admin` (`EnsureRole`) sur les routes, Policies pour les modèles ; l'admin passe toutes les Policies (`Gate::before`). Connexion démo : `POST /demo/login/{role}` (mode démo seulement, 10/min par IP).
- Erreurs 403, 404, 429 (et 500/503 hors debug) : page Inertia `Error`, bilingue.
- Vérifier à 375 px dans les deux langues (le français est plus long).

## Sécurité

- Aucune clé d'API dans le code, le dépôt ou le front : uniquement dans `.env` (ignoré par git), lue via `config/services.php`. Le navigateur ne parle qu'à Laravel.
- Jamais de `v-html` sur du contenu généré par l'IA ; le Markdown passe par DOMPurify.
- Uploads validés : JPEG/PNG/WebP, 5 Mo maximum, renommés, stockés hors du dossier public, métadonnées GPS supprimées.
- Routes IA limitées (`throttle`) et plafond quotidien `AI_DAILY_LIMIT_PER_USER` ; jamais de données personnelles inutiles envoyées aux API.
- Pas de dose de pesticide dans les réponses ; mention « Conseil généré par IA, à vérifier auprès d'un technicien agricole » (en anglais : « AI-generated advice, to be checked with an agricultural technician »).

## Couche IA (`app/Services/AI`)

- Toujours passer par `AiManager` : `chat($messages, ['user' => $user])` et `analyzeImage($prompt, $bytes, $mime, $user)`. Les clients (`GroqClient`, `OpenRouterClient`, `GeminiClient`) ne s'appellent directement que dans `nema:ai-ping`.
- Texte : fournisseurs essayés dans l'ordre de `nema.ai.text_providers` (`AI_TEXT_PROVIDERS`, défaut `groq,openrouter`, `gemini` possible) ; un fournisseur sans clé est ignoré. Toute erreur (429, 5xx, timeout, autre code HTTP) → fournisseur suivant ; 1 nouvel essai sur 5xx et timeout, pas sur 429. Délais : 30 s texte, 45 s vision. Tous en échec → `AiUnavailableException`.
- Vision : Gemini seul, réponse JSON imposée ; JSON invalide → `InvalidAiJsonException`.
- Prompts : uniquement les constantes versionnées de `PromptLibrary`, via `PromptLibrary::render(PromptLibrary::ADVISOR_V1, ['context' => …])`. `{language}` (« français » / « English ») vient de `app()->getLocale()` et ne peut pas être remplacé par une variable. Changer un texte = nouvelle constante (`ADVISOR_V2`), jamais modifier une V1.
- Quotas : middleware `throttle:ai` (10 requêtes/min) sur toute route IA, et `AiQuota` (`AI_DAILY_LIMIT_PER_USER`, défaut 30/jour) appliqué par `AiManager` dès que `user` est passé. Réponses en cache et réponses de secours ne comptent pas.
- Cache : requêtes identiques (messages, options, modèles, locale) gardées 24 h ; `'cache' => false` pour une conversation suivie.
- Mode démo (`NEMA_DEMO_MODE=true`) : si tout échoue, réponse la plus proche de `storage/app/demo/fallbacks.json` (fr et en, choisie par mots-clés), avec `fallback = true` à signaler dans l'interface. Aucune réponse proche → l'exception remonte.
- Journal : canal `ai` (`storage/logs/ai.log`) avec fournisseur, modèle, tokens, durée et erreur ; jamais de clé, d'en-tête ni de prompt.
- Tests : `configureFakeAiProviders()` (`tests/Pest.php`) active `Http::preventStrayRequests()` et des clés factices ; `Sleep::fake()` pour les nouveaux essais.

## Commandes utiles

```bash
composer setup                         # installation complète (dépendances, .env, clé, migrations, build)
composer run dev                       # serveur Laravel + Vite
php artisan migrate:fresh --seed       # base de démo réinitialisée (DemoSeeder)
php artisan test --compact             # tests (lancer `npm run build` avant, pour le manifeste Vite)
php artisan nema:ai-ping               # état et latence de chaque fournisseur IA (vrais appels)
php artisan db:seed --class=DemoSeeder # recrée seulement les comptes @nema.demo et leurs données
npm run lint:i18n                      # aucun texte en dur, aucune clé de traduction manquante
npm run types:check                    # vue-tsc
vendor/bin/pint --dirty --format agent # formatage PHP
composer types:check                   # Larastan
```

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
