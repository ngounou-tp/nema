# NEMA — Base de données de production (Neon) et services

Ce document décrit comment brancher NEMA sur Neon (PostgreSQL) et sur les services gratuits prévus par le guide (`docs/guide-implementation-nema.pdf`).

## 1. Créer la base Neon

1. Créer un compte sur <https://neon.com> puis un projet `nema`.
   - Région : **AWS Europe (Frankfurt)**, et choisir aussi Frankfurt pour le service Render, pour que l'application et la base soient proches.
   - Version Postgres : la plus récente proposée.
2. Branches :
   - `main` → production (la démo publique du jury).
   - `dev` → développement et essais de migrations (branche de `main`, copie instantanée).
3. Dans **Connect**, copier deux chaînes de connexion :
   - **Pooled** (le nom d'hôte contient `-pooler`) → `DB_URL`
   - **Direct** (sans `-pooler`) → `DB_URL_UNPOOLED`

## 2. Variables d'environnement

Dans `.env` en local (branche `dev`) ou dans Render (branche `main`) :

```dotenv
DB_CONNECTION=pgsql
DB_URL="postgresql://USER:PASSWORD@ep-xxxx-pooler.eu-central-1.aws.neon.tech/neondb?sslmode=require"
DB_URL_UNPOOLED="postgresql://USER:PASSWORD@ep-xxxx.eu-central-1.aws.neon.tech/neondb?sslmode=require"
DB_SSLMODE=require
```

- L'application utilise la connexion `pgsql` (pooled).
- Les migrations passent par la connexion `pgsql_direct` (direct), définie dans `config/database.php` :

```bash
php artisan migrate --force --database=pgsql_direct
php artisan db:seed --class=DemoSeeder --force --database=pgsql_direct
```

Les sessions, le cache et la file d'attente utilisent déjà le driver `database` : ils fonctionnent tels quels sur Neon.

## 3. Bonnes pratiques Neon pour NEMA

- **Mise en veille** : la base Neon se met en veille après quelques minutes sans requête ; la première requête suivante prend quelques centaines de ms de plus. Le ping UptimeRobot prévu pour Render réveille aussi la base.
- **Migrations** : tester d'abord sur la branche `dev`, puis appliquer sur `main`.
- **Restauration** : Neon permet de restaurer une branche à un instant passé (la durée disponible dépend du plan, à vérifier dans la console). Pour la sauvegarde quotidienne demandée par le guide, ajouter en plus un `pg_dump` planifié sur l'URL directe.
- **RAG** : en phase 1, recherche plein texte (`tsvector`) sur `knowledge_chunks` ; en phase 2, `CREATE EXTENSION vector;` (pgvector, disponible sur Neon) pour la recherche sémantique.
- **Réinitialisation de la démo** : le `migrate:fresh --seed` nocturne prévu par le guide efface toutes les données. Ne le planifier que sur la base de démo, jamais sur une base contenant de vraies données de producteurs.
- **Secrets** : ne jamais committer `.env`. Utiliser un mot de passe différent pour `dev` et `main` (rôles Neon distincts) ; il doit pouvoir être révoqué à tout moment.

## 4. Autres services (déjà installés dans le code)

| Service | Usage NEMA | Variables |
|---|---|---|
| Groq | Texte : conseil, explications | `GROQ_API_KEY`, `GROQ_MODEL` |
| Gemini (Google AI Studio) | Vision : diagnostic photo | `GEMINI_API_KEY`, `GEMINI_MODEL` |
| OpenRouter | Secours si quota atteint | `OPENROUTER_API_KEY`, `OPENROUTER_MODEL` |
| Cloudinary (`cloudinary/cloudinary_php`) | Stockage persistant des photos | `CLOUDINARY_URL`, `CLOUDINARY_FOLDER` |
| Sentry (`sentry/sentry-laravel`) | Suivi des erreurs en production | `SENTRY_LARAVEL_DSN` |
| QR codes (`bacon/bacon-qr-code`, déjà présent) | QR des lots → `/verifier/{uuid}` | — |
| `chart.js` + `vue-chartjs` | Graphiques des prévisions | — |
| `dompurify` | Nettoyage du Markdown des réponses IA | — |

La configuration des clés IA et de Cloudinary se trouve dans `config/services.php` (`services.ai`, `services.groq`, `services.gemini`, `services.openrouter`, `services.cloudinary`).

## 5. Outils pour Claude

- Skills : `neon`, `neon-postgres`, `neon-postgres-branches`, `neon-postgres-egress-optimizer`, `postgres-best-practices` dans `.claude/skills/`.
- Serveur MCP Neon : déclaré dans `.mcp.json` (`https://mcp.neon.tech/mcp`, connexion OAuth au premier usage) pour Claude Code en local.
- Sur claude.ai (sessions cloud), connecter les connecteurs **Neon**, **Render**, **Cloudinary**, **Sentry** et **Hugging Face**.
