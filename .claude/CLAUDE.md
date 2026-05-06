# biarritzdomiciliation.com — Contexte projet

## Hébergement & déploiement

- **Hébergement** : VPS Hetzner avec cPanel
- **Username cPanel** : `biarritzdomicili`
- **Deploy path** : `/home/biarritzdomicili/public_html/`
- **Méthode** : cPanel Git Version Control — chaque push sur `main` déclenche un déploiement via `.cpanel.yml`
- **Domaine** : https://biarritzdomiciliation.com (HTTPS forcé, redirection www → sans-www)

## Stack

- HTML5 + CSS3 + JavaScript vanilla (pas de framework)
- Pas de build step, pas de bundler
- Tout est servi en statique depuis `public_html/`

## Conventions

- **Mobile-first** : écrire les styles pour mobile d'abord, puis `@media (min-width: …)` pour desktop
- **Images** : WebP en priorité, fallback JPEG/PNG si nécessaire
- **SVG** : inline dans le HTML pour les icônes (permet color via `currentColor`)
- **Jamais de hotlink** d'images externes — toujours héberger localement dans `assets/`
- **Alt text obligatoire** sur toutes les images (accessibilité + SEO)
- **Chemins relatifs uniquement** : `css/style.css` pas `/css/style.css`. Sinon le site casse en `file://` ou en sous-dossier.

## SEO

- Title unique et descriptif sur chaque page (50-60 caractères)
- Meta description sur chaque page (150-160 caractères)
- Open Graph complet : `og:title`, `og:description`, `og:image`, `og:url`, `og:type`, `og:locale`
- Schema.org JSON-LD pour les données structurées (LocalBusiness, Service, etc.)
- Mettre à jour `sitemap.xml` à chaque ajout/suppression de page (changer `lastmod`)
- `robots.txt` : ne pas bloquer ce qui doit être indexé

## Cache-busting

⚠️ **Important** : `.htaccess` configure un cache navigateur d'**1 mois** sur CSS et JS.

À chaque modification de `css/style.css` ou `js/main.js`, **bumper le query string** `?v=AAAAMMJJx` dans `index.html` (et toutes les pages qui les référencent), sous peine de servir du CSS/JS périmé pendant un mois aux visiteurs récurrents.

Format : `?v=AAAAMMJJx` où :
- `AAAAMMJJ` = date du jour
- `x` = lettre de version (a, b, c…) pour les modifs multiples dans la journée

Exemple :
```html
<link rel="stylesheet" href="css/style.css?v=20260506a">
<script src="js/main.js?v=20260506a"></script>
```

Si on modifie le CSS deux fois dans la même journée : `20260506a` puis `20260506b`.

## Git

- **`main` = production** : chaque push sur `main` déploie via cPanel
- **Jamais de push direct sur `main`** : toujours passer par une branche feature + merge
- **Branches feature** : `feat/nom-feature`, `fix/nom-bug`, `seo/nom-page`
- **Commits** : messages clairs en français, présent de l'indicatif ("Ajoute formulaire contact" pas "Ajout du formulaire de contact")

## Structure du projet

```
biarritzdomiciliation.com/
├── .claude/CLAUDE.md       # ce fichier
├── .cpanel.yml             # config déploiement cPanel
├── .htaccess               # config Apache (HTTPS, cache, sécurité)
├── .gitignore
├── robots.txt
├── sitemap.xml
├── index.html              # page d'accueil
├── 404.html                # page erreur
├── css/
│   └── style.css
├── js/
│   └── main.js
└── assets/                 # images, fonts, favicon, og-image
```
