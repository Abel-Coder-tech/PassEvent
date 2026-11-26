# PaxEvent

Billetterie en ligne 100 % Bénin : les organisateurs créent leurs événements et vendent leurs billets en ligne (Mobile Money via FedaPay), sur le terrain grâce à leurs agents de vente, ou sous forme de tickets et bracelets imprimés. Le contrôle d'accès se fait par scan de QR code.

Site : [paxevent.com](https://paxevent.com)

---

## Sommaire

- [Les espaces](#les-espaces)
- [Fonctionnalités](#fonctionnalités)
- [Technologies](#technologies)
- [Installation en local](#installation-en-local)
- [Configuration (.env)](#configuration-env)
- [Tâches planifiées](#tâches-planifiées)
- [Commandes utiles](#commandes-utiles)
- [Tickets physiques et bracelets](#tickets-physiques-et-bracelets)
- [Tests](#tests)
- [Déploiement](#déploiement)
- [Application PaxAgent (APK)](#application-paxagent-apk)
- [Sécurité](#sécurité)

---

## Les espaces

| Espace | Accès | Pour qui |
|---|---|---|
| **Site public** | `/` | Acheteurs : catalogue des événements, achat de billets, récupération de billets |
| **Organisateur** | `/login` puis `/dashboard` | Création d'événements et de tarifs, ventes, agents, tickets physiques, statistiques, retraits |
| **Super admin** | `/superadmin` | Propriétaire de la plateforme : validation des organisateurs, retraits, remboursements, journaux, sécurité |
| **Équipe** | `/equipe` | Membres de l'équipe PaxEvent, avec des droits limités par rôle |
| **Agent de scan** | `/agent` et API | Contrôle des billets à l'entrée |
| **Agent de vente** | `/vente` et API | Vente de billets sur le terrain (espèces ou Mobile Money) |
| **Affilié** | `/affilie` | Liens d'affiliation et commissions |

## Fonctionnalités

**Billetterie**
- Événements sur un ou plusieurs jours, tarifs avec quotas, billets gratuits, codes promo
- Paiement Mobile Money via **FedaPay** (MTN, Moov, Celtiis), webhook et rapprochement automatique des paiements
- Billet PDF avec QR code envoyé par email, récupération du billet par l'acheteur
- Demandes de remboursement et demandes de modification de tarif

**Organisateurs**
- Inscription avec validation par PaxEvent, contrat de prestation généré en PDF
- Agents de scan et agents de vente, attribués par événement
- Statistiques de ventes et de scans, retraits des recettes (commission PaxEvent déduite)
- Gestion d'un compte par PaxEvent avec mandat (journal des actions effectuées)

**Tickets physiques**
- Planches PDF de tickets ou de bracelets à partir de l'image du design de l'organisateur
- Éditeur visuel : placement du QR au dixième de millimètre, zoom, aperçu en direct, sauvegarde automatique
- QR codes pour des événements hors plateforme

**Super admin**
- Organisateurs : validation, suspension, **corbeille** (suppression réversible, restauration à l'identique)
- Journal général des opérations (billetterie et actions administratives) avec recherche
- **Journal de sécurité** : connexions échouées, requêtes suspectes, blocage d'IP
- Sauvegarde quotidienne de la base de données

**Application mobile PaxAgent** (Android) : scan et vente pour les agents, via l'API (`routes/api.php`, authentification Sanctum).

## Technologies

- **PHP 8.2+**, **Laravel 12**, MySQL / MariaDB
- **Blade**, Bootstrap, Tailwind CSS 4, Vite
- **DomPDF** (billets, planches, contrats), **bacon/bacon-qr-code** (QR codes)
- **FedaPay** (paiements), **Laravel Sanctum** (API des agents), **Socialite** (connexion Google)

## Installation en local

Prérequis : PHP 8.2+ avec les extensions `gd` (et FreeType) et `pdo_mysql`, Composer, Node.js, MySQL.

```bash
git clone https://github.com/Abel-Coder-tech/PassEvent.git
cd PassEvent

composer install
npm install

cp .env.example .env
php artisan key:generate
# Renseigner la base de données dans .env, puis :
php artisan migrate
php artisan storage:link

npm run dev           # assets en développement
php artisan serve     # http://localhost:8000
```

Pour faire tourner la file d'attente (emails) et les tâches planifiées en local :

```bash
php artisan queue:work
php artisan schedule:work
```

## Configuration (.env)

En plus des variables Laravel habituelles (`APP_*`, `DB_*`, `MAIL_*`) :

| Variable | Rôle |
|---|---|
| `FEDAPAY_PUBLIC_KEY`, `FEDAPAY_SECRET_KEY` | Clés API FedaPay |
| `FEDAPAY_SANDBOX` | `true` en test, `false` en production |
| `FEDAPAY_WEBHOOK_SECRET` | Secret de signature du webhook FedaPay (**obligatoire en production**) |
| `SUPPORT_MAIL_*` | Boîte email du support (notifications, alertes) |
| `PAYMENT_ALERT_EMAIL` | Destinataire des alertes de paiement et de sécurité |
| `PAYMENT_ALERT_COOLDOWN_MINUTES`, `PAYMENT_FAILURE_ALERT_THRESHOLD`, `PAYMENT_FAILURE_ALERT_WINDOW_MINUTES` | Réglage des alertes de paiement |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | Connexion avec Google |
| `SANCTUM_ABSOLUTE_EXPIRATION` | Durée de vie des jetons de l'application agents |
| `QUEUE_CONNECTION` | `database` : les emails partent par la file d'attente |

Ne jamais committer le fichier `.env`.

## Tâches planifiées

Le planificateur Laravel doit tourner chaque minute (tâche cron sur le serveur) :

```
* * * * * cd /chemin/vers/PassEvent && php artisan schedule:run >> /dev/null 2>&1
```

| Commande | Fréquence | Rôle |
|---|---|---|
| `paiements:rapprocher-fedapay` | 5 min | Rattache aux billets les paiements FedaPay approuvés restés orphelins |
| `tickets:envoyer-emails` | 5 min | Envoie les billets par email |
| `billetterie:notifier-ouverture` | 5 min | Prévient de l'ouverture d'une billetterie |
| `tickets:purger-en-attente` | 15 min | Libère les réservations non payées expirées |
| `evenements:terminer` | 1 h | Passe les événements terminés au statut « terminé » |
| `tickets:reconcilier`, `tickets:corriger-operateurs` | 1 h | Cohérence des billets et des opérateurs de paiement |
| `affiliations:liberer-commissions` | 1 h | Libère les commissions d'affiliation |
| `paxevent:sauvegarder-base` | 3 h 00 | Sauvegarde de la base (14 jours conservés) |
| `securite:purger` | 3 h 30 | Nettoie le journal de sécurité (90 jours) et les blocages expirés |
| `sanctum:prune-expired`, `ventes:purger-idempotences` | 1 jour | Nettoyage |

## Commandes utiles

```bash
php artisan paxevent:sauvegarder-base       # sauvegarde manuelle → storage/app/private/backups
php artisan evenements:recalculer-compteurs # recalcule les compteurs de billets des événements
php artisan profils:normaliser-types        # harmonise les types de profils organisateurs
php artisan consentements:purger            # purge les consentements cookies expirés
```

## Tickets physiques et bracelets

Les formats sont définis dans `App\Models\LotPhysique::FORMATS` et mis en page par `App\Services\LotPhysiqueTemplatePdfService`.

| Format | Ticket | Feuille | Par feuille |
|---|---|---|---|
| Standard | 13,7 × 4,9 cm | A4 paysage | 8 |
| Standard 2 | 13,2 × 6,6 cm | A4 paysage | 6 |
| VIP | 18 × 7 cm | A4 portrait | 3 |
| VIP 2 | 9,9 × 7 cm | A4 portrait | 3 |
| A5 | 20,2 × 14,3 cm | A4 portrait | 2 |
| **Bracelet** | **22 × 1,9 cm** + 3 cm blancs (25 × 1,9 cm) | **25 × 19 cm** sur mesure | 10 |
| Personnalisé | de 2 cm jusqu'au format A4 | A4 | calculé |

- Sur A4 : marges de 4 mm, 1 mm entre deux tickets avec la ligne de coupe au milieu.
- **Bracelet** : 10 bracelets bord à bord sur la feuille de 25 × 19 cm, sans marge, sans espace et sans ligne de coupe. Zone QR fixe (1,90 × 1,42 cm) avec le code pass à la verticale. Image attendue : 2598 × 224 px (300 DPI). À imprimer en format personnalisé 25 × 19 cm, à 100 %.
- Les dimensions et la position du QR sont enregistrées au dixième de millimètre.

## Tests

```bash
php artisan test
```

Les tests utilisent SQLite en mémoire (`phpunit.xml`) et créent eux-mêmes les tables dont ils ont besoin. Quand une colonne est ajoutée à une table utilisée par une requête testée, il faut aussi l'ajouter dans ces tables de test : avec SQLite, un nom de colonne inconnu entre guillemets est lu comme un texte, sans erreur.

## Déploiement

Production hébergée chez o2switch (Apache, cPanel). Après chaque mise en ligne :

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

Vérifier ensuite :
- que la tâche cron `schedule:run` est active ;
- dans **Super admin → Sécurité**, que l'encadré « Votre connexion » affiche bien l'IP publique du visiteur et HTTPS = Oui.

## Application PaxAgent (APK)

L'appli mobile des agents (scan et vente) est distribuée hors Play Store. Le dashboard organisateur propose la dernière version publiée, et le mail envoyé à la création d'un agent contient un lien de téléchargement (signé, valable 7 jours, coupé si l'agent est désactivé).

**1. Construire l'APK** (dépôt `pax_scan`) : incrémenter le numéro après le `+` de `version:` dans `pubspec.yaml`, puis :

```bash
flutter build apk --release
# → build/app/outputs/flutter-apk/app-release.apk
```

L'APK doit être signé avec la clé de release (`android/key.properties`) : Android refuse une mise à jour signée avec une autre clé. Ne jamais perdre ni changer cette clé.

**2. L'envoyer sur le serveur** (hors du dossier public, par exemple `~/tmp/`), puis publier :

```bash
php artisan paxagent:publier ~/tmp/app-release.apk --nom-version=1.0.0 --build=7 \
    --notes="Code d'accès à 4 chiffres"
```

La commande copie le fichier dans `storage/app/private/apps/`, calcule son SHA-256 et l'enregistre. Options : `--obligatoire` (mise à jour imposée), `--brouillon` (enregistré sans être proposé).

**Serveur** : par défaut (Apache/o2switch), PHP envoie le fichier par morceaux. Sur Nginx, mettre `PAXAGENT_X_ACCEL=true` et déclarer la location interne :

```nginx
location /protected/apps/ {
    internal;
    alias /chemin/vers/passevent/storage/app/private/apps/;
}
```

Vérifier que `storage/` n'est jamais accessible depuis le web : seul `public/` doit l'être.

## Sécurité

- Les comptes supprimés passent par une corbeille ; la suppression définitive se confirme en saisissant l'email du compte.
- Connexions limitées en nombre d'essais, sessions sans « se souvenir de moi », en-têtes de sécurité sur toutes les réponses.
- Journal de sécurité et blocage d'IP dans le super admin ; les mots de passe et jetons ne sont jamais enregistrés.
- Pour signaler une faille, écrire au support de PaxEvent plutôt que d'ouvrir une issue publique.

---

© PaxEvent. Tous droits réservés. Ce code n'est pas sous licence libre : toute reproduction ou réutilisation est interdite sans autorisation (voir les CGU, article 14).
