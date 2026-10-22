---
name: nema-product-ux
description: NEMA product context and UX rules (Cameroonian agricultural AI agent). MUST be used when designing, building or reviewing any NEMA page, component, copy, demo data, AI-facing screen (assistant, photo diagnosis, supplier score, traceability, forecasts, cooperative space) or the jury demo flow. Source of truth is docs/guide-implementation-nema.pdf.
---

# NEMA — product & UX rules

NEMA is a web platform (Laravel + Inertia + Vue) that acts as an agricultural AI agent for producers, cooperatives and buyers in **Cameroon**. Phase 1 is a public demo for a jury on **25 October 2026** (first deploy on 23 Oct); phase 2 (fine-tuned model, vision classifier, Flutter app) comes after. Full plan: `docs/guide-implementation-nema.pdf`.

Use together with: `frontend-design` (visual direction), `vue-best-practices`, `inertia-vue` guidelines, `tailwindcss-development`, `web-design-guidelines` (audit), `webapp-testing` (verify in a browser).

## Audience & tone

- The app is **fully bilingual, French and English** (Cameroon's two official languages). French is the default, English the fallback; the user's choice is stored in `users.locale` and a language switcher (FR / EN) is always reachable, including on public pages and the jury's « Essayer en tant que… » / "Try as…" entry.
- Never hard-code interface text: every string goes through a translation key with both `lang/fr` and `lang/en` versions (enum labels: `label()` and `lang/{fr,en}/enums.php`). Copy in both languages is simple, short sentences, no jargon. Users are farmers and cooperative managers, often on a **mobile phone with 3G/4G**.
- Units and formats: kg, ha, t, **FCFA**, formatted for the current locale (`Intl.NumberFormat('fr-CM' | 'en-CM')`, dates likewise).
- AI answers and diagnoses come back in the user's language. User-entered content (product names, reviews, lot steps) is shown as written.
- Names, villages, prices and crops in demo data must be credible for Cameroon (cacao, café, maïs, manioc, plantain, banane, tomate, arachide, igname, palmier à huile; regions Ouest, Littoral, Centre…).

## Roles (one dashboard each)

| Role | Main jobs | Demo account |
|---|---|---|
| Producteur | Photo diagnosis, AI advice, publish offers | producteur@nema.demo |
| Coopérative / GIC | Members, harvests, stocks, traceable lots | coop@nema.demo |
| Acheteur | Search, supplier score, order, scan QR | acheteur@nema.demo |
| Administrateur | Moderation, statistics, AI log | admin@nema.demo |

The public home page has an **« Essayer en tant que… »** control: the jury switches role in one click, without creating an account.

## Screens

1. Public home: value proposition + demo access by role
2. Dashboard (per role)
3. Assistant NEMA: agronomic advice chat
4. Diagnostic: photo upload + structured result
5. Marketplace: list, product page, supplier page with score
6. Traçabilité: lot page + QR code + **public** verification page `/verifier/{uuid}` (no login)
7. Prévisions: price/volume charts by filière and region
8. Espace coopérative: members, harvests, stocks

Priority if time runs short: diagnostic, assistant, marketplace + score, traceability are the core; cut cooperative space, then semantic search, then detailed forecasts.

## The 3-minute jury flow (must always work, desktop and phone)

Producer photographs a sick cacao leaf → gets diagnosis + advice → publishes harvest → buyer finds it, sees score **87/100** with explanation → orders → scans the lot QR and sees its origin.

## UX rules for AI features

- Always show the visible notice: **« Conseil généré par IA, à vérifier auprès d'un technicien agricole »** / **"AI-generated advice, to be checked with an agricultural technician"**.
- Never render AI output with `v-html`. If Markdown is needed, sanitize with DOMPurify.
- Assistant answers cite sources as `[n]`; render those as readable source references. Questions are capped at 2 000 characters (show a counter); history keeps the last 10 messages.
- Each answer has a **« Cette réponse est-elle utile ? »** / "Was this answer helpful?" feedback control.
- Diagnosis: show a clear loading state (target < 15 s), the image preview, then the JSON as readable cards: culture, symptômes, hypothèses with confidence, actions immédiates, prévention, consulter un technicien. If confidence < 60 % show **« Diagnostic incertain »** / "Uncertain diagnosis" and recommend a field agent. If `image_exploitable` is false, ask for a sharper photo of the plant.
- Never show pesticide doses; point to an approved technician.
- When a provider is down or out of quota, show a clear, calm message (or the recorded fallback answer), never a raw error.
- Uploads: JPEG/PNG/WebP, max 5 MB; validate client-side with a friendly message before upload.

## Business displays

- **Supplier score /100** badge: ≥ 80 « Très fiable » / "Very reliable", 60–79 « Fiable » / "Reliable", < 60 « À surveiller » / "To watch"; next to it a 2-sentence AI explanation (main strength + point to watch). The score is computed by Laravel, never by the AI.
- **Forecasts**: show a range (low–high), not a single number, and state the data is simulated in phase 1 and the advice is an estimate, not a guarantee.
- **Traceability**: QR points to `/verifier/{uuid}`; never expose numeric DB ids in URLs. Public page shows origin, harvest date, steps, producer score.
- An « À propos » page lists the datasets used (DigiGreen/Kenya_Agri_queries, KisanVaani/agriculture-qa-english-only, …) and their licences.

## Quality floor

Responsive down to 360 px, visible keyboard focus, reduced motion respected, AA contrast, light/dark via the existing appearance setting, fast on slow networks (small images, no heavy libraries; charts use `chart.js` + `vue-chartjs`).
