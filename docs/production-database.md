# NEMA — Base de données, photos et services de production

Ce document décrit comment brancher NEMA sur Neon (PostgreSQL + stockage de fichiers) et sur les services gratuits utilisés (`docs/guide-implementation-nema.pdf`).

## Services utilisés

| Besoin | Service |
|---|---|
| Hébergement de l'application | Render |
| Base de données + photos de diagnostic | Neon (un seul compte : PostgreSQL + Object Storage) |
| IA texte | Groq (secours : OpenRouter) |
| IA vision (diagnostic photo) | Gemini (Google AI Studio) |
| Suivi des erreurs | Sentry (optionnel, désactivé sans DSN) |

Les jeux de données Hugging Face sont téléchargés une seule fois dans Colab (pas besoin de compte) puis importés dans la table `knowledge_chunks`.

## 1. Créer le projet Neon

1. Créer un compte sur <https://neon.com> puis un projet `nema`.
   - Région : **AWS Europe (Frankfurt) — `aws-eu-central-1`**. Elle est obligatoire pour l'Object Storage, et le service Render doit aussi être à Frankfurt pour que l'application, la base et les photos soient proches.
2. Branches :
   - `main` → production (la démo publique du jury).
   - `dev` → développement et essais de migrations (copie instantanée de `main`, fichiers compris).
3. Dans **Connect**, copier deux chaînes de connexion :
   - **Pooled** (le nom d'hôte contient `-pooler`) → `DB_URL`
   - **Direct** (sans `-pooler`) → `DB_URL_UNPOOLED`
4. Créer un bucket **privé** nommé `photos` sur la branche `main` (console Neon, ou `neon.ts` + `neon deploy`) et récupérer ses identifiants S3 (console, ou `neon env pull`).

## 2. Variables d'environnement

Dans `.env` en local (branche `dev`) ou dans Render (branche `main`) :

```dotenv
DB_CONNECTION=pgsql
DB_URL="postgresql://USER:PASSWORD@ep-xxxx-pooler.eu-central-1.aws.neon.tech/neondb?sslmode=require"
DB_URL_UNPOOLED="postgresql://USER:PASSWORD@ep-xxxx.eu-central-1.aws.neon.tech/neondb?sslmode=require"
DB_SSLMODE=require

PHOTOS_DISK=neon
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_ENDPOINT_URL_S3=...
AWS_REGION=eu-central-1
NEON_PHOTOS_BUCKET=photos
```

- L'application utilise la connexion `pgsql` (pooled).
- Les migrations passent par la connexion `pgsql_direct` (direct), définie dans `config/database.php` :

```bash
php artisan migrate --force --database=pgsql_direct
php artisan db:seed --class=DemoSeeder --force --database=pgsql_direct
```

Les sessions, le cache et la file d'attente utilisent déjà le driver `database` : ils fonctionnent tels quels sur Neon.

## 3. Photos de diagnostic

- Les photos ne sont **jamais** stockées dans la base : seule leur clé (`diagnoses.image_path`) l'est.
- Le disque utilisé est `config('filesystems.photos')` :
  - `local` en développement et dans les tests (`storage/app/private`, hors du dossier public) ;
  - `neon` en production (Neon Object Storage, S3 compatible, adressage path-style).
- Le bucket reste privé : afficher une photo avec une URL signée temporaire, par exemple `Storage::disk(config('filesystems.photos'))->temporaryUrl($path, now()->addMinutes(10))`.
- Avant l'envoi : JPEG/PNG/WebP, 5 Mo maximum, redimensionnement, nom de fichier aléatoire et suppression des métadonnées GPS (voir le guide).
- Offre gratuite : 5 Go par projet, largement suffisant pour la démo. Le service est récent (disponible pour tous depuis septembre 2026). En cas de problème, changer simplement de disque S3 compatible dans `config/filesystems.php`, sans toucher au code.

## 4. Bonnes pratiques Neon pour NEMA

- **Mise en veille** : la base Neon se met en veille après quelques minutes sans requête ; la première requête suivante prend quelques centaines de ms de plus. Le ping UptimeRobot prévu pour Render réveille aussi la base.
- **Migrations** : tester d'abord sur la branche `dev`, puis appliquer sur `main`.
- **Restauration** : Neon permet de restaurer une branche à un instant passé (la durée disponible dépend du plan, à vérifier dans la console). Pour la sauvegarde quotidienne demandée par le guide, ajouter en plus un `pg_dump` planifié sur l'URL directe.
- **RAG** : en phase 1, recherche plein texte (`tsvector`) sur `knowledge_chunks` ; en phase 2, `CREATE EXTENSION vector;` (pgvector, disponible sur Neon) pour la recherche sémantique.
- **Réinitialisation de la démo** : le `migrate:fresh --seed` nocturne prévu par le guide efface toutes les données. Ne le planifier que sur la base de démo, jamais sur une base contenant de vraies données de producteurs.
- **Secrets** : ne jamais committer `.env`. Utiliser des identifiants différents pour `dev` et `main` ; ils doivent pouvoir être révoqués à tout moment.

## 5. Bibliothèques installées

| Bibliothèque | Usage NEMA | Variables |
|---|---|---|
| `league/flysystem-aws-s3-v3` | Disque `neon` (photos sur Neon Object Storage) | `PHOTOS_DISK`, `AWS_*`, `NEON_PHOTOS_BUCKET` |
| `sentry/sentry-laravel` | Suivi des erreurs en production | `SENTRY_LARAVEL_DSN` |
| `bacon/bacon-qr-code` (déjà présent) | QR des lots → `/verifier/{uuid}` | — |
| `chart.js` + `vue-chartjs` | Graphiques des prévisions | — |
| `dompurify` | Nettoyage du Markdown des réponses IA | — |

Les clés et modèles IA se trouvent dans `config/services.php` (`services.groq`, `services.gemini`, `services.openrouter`) ; le comportement (ordre des fournisseurs, quotas, délais, mode démo) dans `config/nema.php`.

## 6. Outils pour Claude

- Skills : `neon`, `neon-postgres`, `neon-postgres-branches`, `neon-postgres-egress-optimizer`, `neon-object-storage`, `postgres-best-practices` dans `.claude/skills/`.
- Serveur MCP Neon : déclaré dans `.mcp.json` (`https://mcp.neon.tech/mcp`, connexion OAuth au premier usage) pour Claude Code en local.
- Sur claude.ai (sessions cloud), connecter les connecteurs **Neon**, **Render** et **Sentry**.
