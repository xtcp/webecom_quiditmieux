# QuiDitMieux — Backend

Plateforme d'enchères entre particuliers. Backend **PHP 8.2** développé **sans framework**,
avec une architecture MVC maison (Router → Contrôleur → Modèle/Champ → Dépôt PDO),
une couche de sécurité (mots de passe hachés, reCAPTCHA, uploads validés côté serveur),
une conception conforme aux principes du **RGPD**, et une intégration à une API externe
pour les catégories.

## Sommaire

- [Architecture générale](#architecture-générale)
- [Cycle de vie d'une requête](#cycle-de-vie-dune-requête)
- [Couche Modèle : Modele / Champ](#couche-modèle--modele--champ)
- [Couche données : Depot / Connection / Api](#couche-données--depot--connection--api)
- [Contrôleurs et fonctionnalités](#contrôleurs-et-fonctionnalités)
- [Recherche multicritères](#recherche-multicritères)
- [Authentification et sécurité](#authentification-et-sécurité)
- [Conformité RGPD](#conformité-rgpd)
- [Upload d'images](#upload-dimages)
- [Gestion des erreurs](#gestion-des-erreurs)
- [Modèle de données (BDD)](#modèle-de-données-bdd)
- [Frontend JS (AJAX)](#frontend-js-ajax)

## Architecture générale

```
src/
├── Http/
│   ├── Router.php              # Résolution URL/GET → Contrôleur::action()
│   └── Session.php             # Singleton de session (connexion, timeout, utilisateur courant)
├── Bdd/
│   ├── Connection.php          # Fabrique de connexion PDO (options sécurisées)
│   ├── Depot.php                # Construction/exécution des requêtes SQL génériques
│   └── Api.php                  # Client HTTP (cURL) pour les API externes (catégories)
├── Composant/
│   ├── Controleur.php           # Contrôleur de base : actions CRUD génériques (index, view, add, edit, del, search)
│   ├── Modele.php                # Classe abstraite de base des entités (hydratation, __toString, JSON)
│   ├── Champ.php                 # Wrapper typé pour chaque attribut d'un Modele
│   ├── FabriqueModele.php        # Cache d'objets déjà instanciés (évite les doublons/N+1)
│   ├── Debogueur.php              # Journalisation des requêtes SQL/API et des exceptions
│   └── Exception.php
├── Controleur/
│   ├── ControleurVente.php, ControleurUtilisateur.php, ControleurCategorie.php,
│   │   ControleurEnchere.php, ControleurFavori.php, ControleurApp.php
├── Modele/
│   ├── Vente.php, Utilisateur.php, Categorie.php, Enchere.php, Image.php, Favori.php
└── Service/
    └── UploadImages.php          # Validation et stockage sécurisé des images
```

## Cycle de vie d'une requête

1. `index.php` instancie `Depot`, `Session`, `Api`, puis délègue à `Router::orienter($uri)`.
2. `Router` détermine le **modèle** (`vente`, `utilisateur`, `categorie`, `enchere`) et
   l'**action** (`index`, `view`, `add`, `edit`, `del`, `search`, `create`, `update`, ...)
   soit à partir des paramètres `$_GET` (`?vente&action=view&id=1`), soit à partir d'une
   URL propre (`/vente/view/1`).
3. Une **table blanche de routes** (`$routes`) vérifie que l'action demandée est autorisée
   pour ce modèle avant d'instancier le contrôleur — toute action non listée renvoie une
   404 (`Exception` avec code HTTP), même si la méthode PHP existe sur la classe.
4. Le contrôleur (`App\Controleur\Controleur{Modele}`) exécute l'action, interroge le
   `Depot` et appelle `Controleur::afficher($vue, $donnees)`.
5. `afficher()` détecte si la requête attend du JSON (`Accept: application/json` ou
   `Content-Type: application/json`) — utilisé par toutes les recherches AJAX — et bascule
   automatiquement entre rendu HTML (template PHP + layout) et `json_encode()` de la même
   donnée, sans dupliquer la logique métier entre les deux formats.

## Couche Modèle : `Modele` / `Champ`

Chaque entité (`Vente`, `Utilisateur`, `Categorie`, `Enchere`, `Image`, `Favori`) étend
`Modele` et déclare sa structure via des **constantes de classe**, lues par réflexion à
l'exécution :

| Constante | Rôle |
|---|---|
| `CHAMPS` | Colonnes propres à la table (type, opérateur de recherche, sensibilité) |
| `CHAMPS_ASSOCIES` | Relations `1TO1` / `1TON` / `NTON` vers d'autres modèles (jointures auto-générées) |
| `CHAMPS_RECHERCHE` | Sous-ensemble de `CHAMPS` exposé à la recherche publique (`search()`) et à l'affichage générique (`champsAffichage()`) |
| `DISPLAY_FIELD` | Champ utilisé quand le modèle est converti en chaîne (`echo $utilisateur` → son email) |
| `DATETIME` | Champ date principal, utilisé pour générer automatiquement une version formatée (`dateFormatee`) |

Chaque attribut est encapsulé dans un objet **`Champ`** (`Countable`, `IteratorAggregate`,
`JsonSerializable`) qui gère :
- le **typage/casting** automatique (`int`, `float`, `bool`, `array`, ou classe de modèle
  associé) ;
- la **sérialisation JSON** transparente (`jsonSerialize()`) ;
- le **rendu texte** (`__toString()`) — un tableau (association 1TON, ex. `favoris`,
  `images`) est affiché sous forme de **nombre d'éléments**, jamais en tentant de
  concaténer les objets, pour éviter les erreurs `Array to string conversion` ;
- le filtrage des champs sensibles (`sensible => true`, ex. `motdepasse`) hors des
  réponses JSON — voir aussi [Conformité RGPD](#conformité-rgpd).

`Modele::champsAffichage()` retourne uniquement les champs listés dans
`CHAMPS_RECHERCHE`, dans l'ordre déclaré — utilisé par le template générique
`resultats_recherche.php` pour ne jamais exposer de champs internes non voulus (ex. hash
du mot de passe) même si un nouveau champ est ajouté à `CHAMPS` sans y penser.

`FabriqueModele` évite de recréer plusieurs instances d'un même enregistrement au sein
d'une requête (utile pour les associations rechargées plusieurs fois dans une même
hiérarchie de jointures).

## Couche données : `Depot` / `Connection` / `Api`

`Depot` est le seul point d'accès SQL de l'application (aucune requête brute dans les
contrôleurs) :

- **`select()`** génère dynamiquement le `SELECT` (avec alias par table pour éviter les
  collisions de colonnes), les `LEFT JOIN` sur les associations déclarées
  (`CHAMPS_ASSOCIES`, jusqu'à 2 niveaux de profondeur récursifs, y compris les relations
  `NTON` via table pivot), et le `WHERE` à partir d'un tableau de paramètres. Opérateurs
  supportés : `=` (par défaut), `LIKE`, et `IN (...)` (tableau de valeurs). Toutes les
  requêtes sont **préparées** (`PDO::prepare` + paramètres nommés), donc protégées contre
  l'injection SQL.
- Support des filtres sur une **association imbriquée** (`enchere.utilisateur`) pour des
  recherches comme « mes ventes ayant reçu une enchère de tel utilisateur ».
- **`create()` / `update()` / `delete()`** génèrent les requêtes d'écriture correspondantes
  à partir des mêmes tableaux de paramètres.
- Le `Depot` ne sait pas faire de conditions `OR` inter-colonnes ni d'intervalles
  (`>=`/`<=`) : ces cas (recherche titre **ou** description, filtre de prix min/max) sont
  filtrés en PHP après la requête, dans les contrôleurs concernés — un compromis assumé
  plutôt que de complexifier le générateur de requêtes pour deux cas d'usage.

`Connection::recupererPDO()` centralise les options PDO : `ERRMODE_EXCEPTION` uniquement
en mode debug (`ERRMODE_SILENT` en production), `EMULATE_PREPARES => false` (empêche les
attaques par encodage multi-octet), et `FETCH_ASSOC` par défaut.

`Api::select()` interroge une API externe en JSON (cURL) à partir de l'URL déclarée sur le
modèle (`API_URL`), utilisée par `Categorie` (voir plus bas) — permet à un modèle
d'être indifféremment alimenté par la base de données locale ou par un service distant.

## Contrôleurs et fonctionnalités

### `ControleurVente` — annonces et recherche
- **`add`** : création d'une vente (titre, catégorie, description, état, prix de départ,
  date de fin, upload multi-images) — transaction PDO (upload validé → insertion images →
  insertion vente → commit, ou rollback + suppression des fichiers déjà écrits en cas
  d'échec à n'importe quelle étape).
- **`edit`** : modification/suppression réservée au propriétaire, bloquée dès qu'une
  enchère existe sur la vente ou que le statut dépasse « en cours ».
- **`search`** : recherche AJAX (JSON) combinant filtres SQL (`categorie` en `IN`,
  `etat_produit`, `status`) et filtres post-requête en PHP (texte sur **titre OU
  description**, intervalle de prix `prix_min`/`prix_max`), puis **visibilité** :
  les ventes au statut 0 (« à venir », non publiées) sont retirées des résultats sauf
  pour leur propriétaire connecté.

### `ControleurUtilisateur` — comptes
- **`create`** : inscription protégée par Google reCAPTCHA, validation pseudo
  (`[A-Za-z0-9_-]+`), email (`filter_var`), mot de passe (regex : 10 caractères min,
  majuscule + symbole), unicité email/pseudo, hash `password_hash()`, consentement
  explicite requis avant la création du compte.
- **`connecter`** : connexion par email **ou** pseudo au choix, vérification
  `password_verify()`.
- **`edit`** : mise à jour du profil connecté (changement de mot de passe optionnel).
- **`index`** : tableau de bord — dernières ventes, ventes de l'utilisateur triées par
  dernière enchère (sous-requête corrélée `MAX(enchere.dateheure)`), enchères en cours de
  l'utilisateur.
- **`telecharger`** : export des données personnelles (BDD + session) en fichier texte
  téléchargeable — droit à la portabilité, voir [Conformité RGPD](#conformité-rgpd).

### `ControleurEnchere` — enchères
- **`add`** : vérifie qu'aucune enchère de l'utilisateur n'existe déjà sur la vente ; si
  oui, redirige vers `update()` au lieu de dupliquer une ligne (une seule enchère active
  par utilisateur/vente, mise à jour de son montant).

### `ControleurCategorie` — proxy API externe
- **`view`** / **`search`** : ne touchent jamais la base de données locale, tout passe par
  `Api::select()` vers `https://api.mywebecom.ovh/play/qdm/categ.php` — le catalogue de
  catégories n'est donc jamais désynchronisé de la source externe.

### `ControleurFavori`
- Suivi d'une vente sans enchérir, structure minimale héritant des actions génériques du
  `Controleur` de base.

## Recherche multicritères

Deux implémentations de recherche coexistent volontairement :

1. **`Controleur::search()`** (base, générique) : lit `CHAMPS_RECHERCHE`, construit les
   paramètres égalité/`LIKE` via `Depot::select()`, et délègue à un point d'extension
   protégé, **`filtrerResultats()`** (no-op par défaut), que les contrôleurs enfants
   peuvent surcharger pour appliquer des règles de visibilité après la requête SQL — sans
   dupliquer la construction de la requête ni la gestion des cas vides/erreur.
2. **`ControleurVente::search()`** (spécialisée, JSON) : override complet pour la page de
   recherche d'annonces, qui a besoin de logiques que `Depot::select()` ne supporte pas
   nativement (OR titre/description, intervalle de prix) en plus du filtrage de visibilité.

## Authentification et sécurité

- **Mots de passe** : jamais stockés en clair, `password_hash()`/`password_verify()`
  (bcrypt), jamais exposés via JSON (`Champ` marqué `sensible => true`).
- **Session** (`Session`, singleton) : régénération d'ID à la connexion
  (`session_regenerate_id`), expiration automatique après 30 minutes d'inactivité,
  destruction complète à la déconnexion (`session_destroy` + cookie invalidé).
- **Anti-robot** : Google reCAPTCHA sur la création de compte (librairie
  `google/recaptcha`).
- **Autorisation par ressource** : chaque action sensible (`edit`, `del` d'une vente,
  modification du profil) revérifie l'appartenance de l'objet à l'utilisateur en session,
  indépendamment du contrôle d'accès générique du `Router`.
- **Injection SQL** : requêtes 100% préparées via `Depot`, aucune concaténation de valeur
  utilisateur dans le SQL.
- **XSS** : toute sortie HTML passe par le helper `e()` (équivalent `htmlentities`), qui
  gère explicitement les instances de `Champ` et les tableaux (comptage plutôt que
  concaténation) pour ne jamais produire d'erreur ni de sortie non échappée.

## Conformité RGPD

Le projet applique plusieurs principes du RGPD dès la conception du modèle de données et
des contrôleurs :

- **Minimisation des données** (art. 5.1.c) : seules trois données sont collectées à
  l'inscription — pseudo, email, mot de passe (`Utilisateur::CHAMPS`). Aucune autre donnée
  personnelle (nom, adresse, téléphone...) n'est demandée ni stockée.
- **Confidentialité par défaut / minimisation de l'exposition** (art. 25) : seul le
  **pseudo** est visible publiquement (affiché sur les annonces, historiques d'enchères) ;
  l'**email** n'est jamais exposé à d'autres utilisateurs et le **mot de passe** (haché)
  n'est jamais renvoyé, ni en HTML ni en JSON, grâce au marquage `sensible => true` sur le
  `Champ` correspondant (filtré automatiquement par `Modele::jsonSerialize()`).
- **Sécurité du traitement** (art. 32) : mots de passe hachés (`password_hash`, bcrypt),
  requêtes SQL préparées, échappement systématique des sorties (`e()`), protection
  anti-robot à l'inscription.
- **Droit d'accès et à la portabilité** (art. 15 et 20) : `ControleurUtilisateur::telecharger()`
  permet à un utilisateur connecté de télécharger l'ensemble de ses données personnelles
  (enregistrement en base + données de session) dans un fichier texte structuré.
- **Droit à l'effacement** (art. 17) : les clés étrangères de la base sont configurées en
  `ON DELETE CASCADE` sur les tables `vente`, `enchere` et `image` — la suppression d'un
  compte `utilisateur` entraîne automatiquement la suppression de ses ventes et de ses
  enchères associées, sans laisser de données personnelles orphelines en base (voir
  [clés étrangères](#modèle-de-données-bdd)). `ControleurVente::del()` supprime également
  les fichiers image physiquement stockés sur le disque en plus de la ligne en base.
- **Consentement explicite** (art. 7) : la création de compte requiert une case de
  consentement à la politique de confidentialité (`$_POST["consentement"]`), bloquante si
  non cochée (`ControleurUtilisateur::create()`), et une page dédiée (`politique.php`)
  présente les engagements du site.
- **Limitation de la conservation / expiration de session** (art. 5.1.e, esprit de la
  norme) : la session serveur expire automatiquement après 30 minutes d'inactivité
  (`Session::__construct()`), limitant la durée de vie des données d'authentification
  actives côté serveur.

Ces mesures couvrent les principes structurants du RGPD au niveau de l'architecture ;
elles ne remplacent pas une revue juridique complète (registre des traitements, base
légale documentée par finalité, DPO le cas échéant) si le projet devait être exploité en
production avec de vrais utilisateurs.

## Upload d'images

`UploadImages` (service dédié, réutilisable pour n'importe quelle entité liée à `image`) :
- rejette tout fichier dont le type MIME réel (détecté via `finfo`, pas l'en-tête client
  falsifiable) n'est pas `jpeg`/`png`/`webp` ;
- vérifie que le fichier est bien un upload HTTP légitime (`is_uploaded_file`) et une image
  décodable (`getimagesize`), taille (2 Mo) et dimensions (4000×4000) plafonnées ;
- génère un **nom de fichier aléatoire** côté serveur (`random_bytes`), jamais dérivé du
  nom fourni par le client ;
- en cas d'échec partiel sur un upload multiple, **nettoie** les fichiers déjà déplacés
  avant de relancer l'exception, pour ne jamais laisser de fichiers orphelins sur le
  disque.

## Gestion des erreurs

- `Controleur::afficher()` encapsule le rendu dans un `try/catch` : toute exception est
  interceptée par `gererErreur()`, qui renvoie soit une réponse JSON structurée
  (`status`, `code`, `message`, `trace` si `APP_DEBUG=true`) soit une page d'erreur HTML,
  avec le bon code de statut HTTP.
- `Debogueur` journalise chaque requête SQL et chaque appel API externe (temps
  d'exécution inclus) en mode debug, sans impact en production.
- Le mode debug (`APP_DEBUG`) est piloté par variable d'environnement (`app.env`) et
  bascule aussi le mode d'erreur PDO (`ERRMODE_EXCEPTION` vs `ERRMODE_SILENT`).

## Modèle de données (BDD)

| Table | Colonnes clés | Relations |
|---|---|---|
| `utilisateur` | `pseudo`, `email`, `motdepasse` (haché) | 1–N vers `vente`, `enchere`, `favori` |
| `vente` | `titre`, `description`, `categorie`, `status`, `etat_produit`, `prix_depart`, `image_principale`, `dateheure_fin` | N–1 `utilisateur`, 1–N `enchere`, 1–N `image`, 1–N `favori` |
| `enchere` | `prix`, `vente`, `utilisateur`, `dateheure` | N–1 `vente`, N–1 `utilisateur` |
| `image` | `image` (nom de fichier), `vente` | N–1 `vente` |
| `favori` | `utilisateur`, `vente` | N–1 `utilisateur`, N–1 `vente` |

Les catégories ne sont **pas** stockées localement (voir `ControleurCategorie` /
`Api.php`) — `vente.categorie` référence un identifiant de l'API externe.

### Clés étrangères et intégrité référentielle

| Contrainte | Colonne | Référence | `ON DELETE` | `ON UPDATE` |
|---|---|---|---|---|
| `ce_vente_utilisateur` | `vente.utilisateur` | `utilisateur.id` | `CASCADE` | `RESTRICT` |
| `ce_enchere_utilisateur` | `enchere.utilisateur` | `utilisateur.id` | `CASCADE` | `RESTRICT` |
| `ce_enchere_vente` | `enchere.vente` | `vente.id` | `CASCADE` | `RESTRICT` |
| `ce_image_vente` | `image.vente` | `vente.id` | `CASCADE` | `RESTRICT` |
| `ce_favori_utilisateur` | `favori.utilisateur` | `utilisateur.id` | `CASCADE` | `RESTRICT` |
| `ce_favori_vente` | `favori.vente` | `vente.id` | `CASCADE` | `RESTRICT` |

- Les cascades sur `vente`, `enchere`, `favori` et `image` garantissent qu'aucune ligne ne peut
  survivre à la suppression de son `utilisateur` ou de sa `vente` parente — cohérent avec
  le droit à l'effacement RGPD évoqué plus haut.


## Frontend JS (AJAX)

Le frontend (modules ES natifs, sans framework) communique avec ce backend via des
requêtes `fetch()` `POST` en `application/x-www-form-urlencoded`, avec l'en-tête
`Accept: application/json` qui bascule `Controleur::afficher()` en mode JSON. Une classe
`Rechercher` générique (`rechercher.mjs`) factorise le pattern debounce + fetch + rendu de
résultats pour toutes les recherches AJAX (ventes, catégories), avec support de filtres
additionnels (`extraParams`) au-delà du simple champ texte.