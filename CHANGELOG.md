# Changelog

Tous les changements notables de ce bundle sont documentés dans ce fichier.

Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).
Le schéma de version (X.Y.Z, canaux alpha/beta/main) est décrit dans
`.github/workflows/release.yml`.

## [Unreleased]

### Changed

- Compatibilité avec la Content-Security-Policy stricte de Kintai (`script-src 'self' 'nonce-…'`, sans `'unsafe-inline'`) : les 10 attributs d'événements inline des vues (`onclick=`/`onchange=`/`onsubmit=`/`oninput=`) sont remplacés par des attributs `data-*` (`data-on-click`, `data-submit-on-change`, `data-confirm`… gérés par `csp-actions.js` du Core) et le `<script>` inline porte désormais le nonce de la requête (`csp_nonce()`, gardé par `function_exists` pour ne pas planter sur un Core plus ancien). Sans ce changement, les boutons, sélecteurs et confirmations de ces vues ne font plus rien sous la nouvelle politique, sans aucune erreur visible. **Nécessite Kintai Core 0.3.0 ou plus** (`kintai_core.min`), version qui introduit `csp-actions.js` et la CSP à nonce. `tests.yml` échoue désormais si un handler inline, un lien `javascript:` ou un `<script>` sans nonce réapparaît dans `Views/` ou `src/`.

## [1.0.0] - 2026-09-29

### Added

- Extraction depuis le monorepo Kintai (`src/Bundles/SalaryReport`), republication en dépôt autonome — même opération que pour `hiring-report`/`resignation-report`. Rapports de salaire mensuels par magasin ou par employé, pré-remplis depuis les rapports journaliers et les shifts, avec modale de recalcul et export PDF (unitaire et groupé).
- Contrairement à ses cousins `daily-report`/`hiring-report`/`resignation-report`, ce dépôt embarque dès sa création une vraie suite PHPUnit (`tests/`, 42 tests) : `composer.json` mappe `kintai\` directement vers les vraies classes du dépôt Kintai principal, sur le modèle de `kintai-bundle-notebook`. Couvre le contrôleur (CRUD, export JSON/PDF, calcul multi-tranches via `ShiftWageCalculator`, retenues, rapport employé vs magasin), les routes (protection `AuthMiddleware`/`PermissionMiddleware`) et le rendu des vues détail/formulaire (formatage monétaire, devise kanji/internationale).
- Son propre CSS/JS (`.sr-period*`, `salary-report-form.js`, le CSS du PDF) est fourni dès le départ via le nouveau mécanisme `Bundle::loadAssetsFrom()`/`bundle_asset()`/`bundle_asset_path()` de Kintai Core (`kintai_core.min: "0.2.0"`) — pas de dette à migrer après coup, contrairement aux bundles déjà publiés avant l'existence de ce mécanisme.
