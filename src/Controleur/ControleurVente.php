<?php
// Contrôleur ControleurVente.php
// Role:  Prends les paramètres envoié par l'utilisateur, recupére les donnés des repositories, prepare pour l'affichage et appele les templates
//
// Methodes:
//      index, edit, del, add, search processFields

namespace App\Controleur;

use App\Composant\Controleur;
use App\Composant\Debogueur;
use App\Service\UploadImages;
use App\Modele\Vente;

class ControleurVente extends Controleur {
    public const MODELE = Vente::class;
    public array $paramsRechercher = ["utilisateur", "titre", "description", "categorie", "etat_produit", "prix_depart", "status", "dateheure"];

    public function index(?array $params = []) {
    // Function index
    // Role: Traite une recherche et/ou affiche le template de la page principale avec la recherche
    //
    // Parametres: $params - GET/POST envoiés par le router
    // Retour: L'affichage du template
    
        $champs = array_keys(constant(self::MODELE . "::CHAMPS"));
        $champsAssocies = array_keys(constant(self::MODELE . "::CHAMPS_ASSOCIES"));

        $paramsRecherche = [];
        $requete = array_merge($params ?? [], $_GET, $_POST);
        foreach ($champs as $cle) {
            if (!empty($requete[$cle])) {
                $paramsRecherche[$cle] = $requete[$cle];
            }
        }
        foreach ($champsAssocies as $cle) {
            if (!empty($requete[$cle])) {
                $paramsRecherche[$cle] = $requete[$cle];
            }
        }
        $objets = $this->depot->select(self::MODELE, "" , $paramsRecherche);

        $this->afficher("view/index", ["objets" => $objets]);
    }
    public function filtrerVente(Vente $vente) : Vente|null {
        $utilisateur = $this->session->userConnected();
        // Mettre à jour le status de la vente si la date de fin à echoué
        $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $vente->dateheure_fin);
        if ($dateTime < new \DateTime()) {
            $this->depot->update(Vente::class, "vente", ["status" => 2], ["id" => (int)$vente->id->getValue()]);
        }
        // Enlever la vente de la recherche si la vente n'est pas encore en cours sauf si:
        //   1) l'utilisateur n'est pas le proprietaire
        //   2) l'utilisateur n'est pas connectée
        $estProprietaire = false;
        if ($utilisateur && ((int)$vente->utilisateur->id->getValue() === (int)$utilisateur->id->getValue())) {
            $estProprietaire = true;
        }
        // Si la vente est inaactif, seule le propriétaire à accés aux donnés de la vente
        if ($vente->status->getValue() === 0) {
            if ($utilisateur) {
                if (!$estProprietaire) {
                    return null;
                }
            } else {
                return null;
            }
        }
        // Si la vente est actif ou terminée, empecher les utilisateurs non connectée ou qui n'ont pas enchéri
        //  d'avoir accés aux enchéres
        if ($vente->status->getValue() > 0) {
            $enleverEncheres = true;
            $aEncherie = false;
            if ($utilisateur) {
                foreach ($vente->enchere as $enchere) {
                    if ((int)$enchere->utilisateur->id->getValue() === (int)$utilisateur->id->getValue()) {
                        $aEncherie = true;
                        break;
                    }
                }
                // Si l'utilisateur este connectée et a enréchi, ne pas enlever
                if ($aEncherie) $enleverEncheres = false;
                // Si l'utilisateur este le proprietaire, ne pas enlever
                if ($estProprietaire) $enleverEncheres = false;
            }

            if ($enleverEncheres) {
                unset($vente->enchere);
            }
        }
        return $vente;
    }
    public function view(?array $params = [], ?bool $forceAPI = false) {
    // Function view 
    // Role: Traite l'affichage d'une vue d'une vente
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
    //

        $classe = constant(get_called_class() . "::MODELE");
        $table = constant($classe . "::TABLE");

        // Verifie si l'id existe et n'est pas vide et affiche un message d'erreur
        if (empty($_GET["id"])) {
            $this->message("Aucun ID pour afficher l'objet!", "red");
        } else {

            $id = $_GET["id"];
            $paramsView = ["id" => $id];

            $objet = $this->depot->selectOne($classe, $table, $paramsView);
            
            if (!$objet) {
                $this->message("Erreur objet ID " . $id . " - Contactez votre administrateur!", "red");
            } else {

                $objet = $this->filtrerVente($objet);

                $view = __DIR__ . '/../../src/template/page/view/' . $table . '.php';
                if (!file_exists($view)) {
                    $template = 'view/default';
                } else {
                    $template = 'view/'.$table;
                }
                
                return $this->afficher($template, ["table" => $table, "name" => $classe, "objet" => $objet], $forceAPI);
            }
        }
    }
    public function edit(?array $params = []) {
    // Function edit - ACTION edit
    // Role: Traite et prépare l'affichage de modification d'une vente OU traite la modification d'une vente
    //
    // Parametres: $params - GET/POST envoiés par le router
    //      Affichage:
    //          $_GET/$_POST: id
    //      Envoi de formulaire:
    //          $_FILES: images
    //          $_POST: id, titre, description, status, etat_produit, prix_depart, image_principale, dateheure_fin

    // Retour: L'affichage du template
    // ! A FAIRE: changer pour enlever la constante et faire une autre association avec le controleur->modele


        $classe = self::MODELE;
        $table = constant($classe . "::TABLE");

        // Verifie si l'id existe et n'est pas vide et affiche un message d'erreur
        if (!isset($_GET["id"]) && !isset($_POST["id"])) {
            $this->message("L'ID est nécessaire modifier une vente!", "red");
        } else {
            $utilisateur = $this->session->userConnected();
            if (!$utilisateur) {
                $this->message("Connection requis pour modifier une vente!", "red");
            } else {
                $id = $_GET["id"] ?? $_POST["id"];
                $objet = $this->depot->selectOne($classe, $table, ["id" => $id]);
                if (!$objet) {
                    $this->message("Vente non trouvée!", "red");
                } else {

                    // Affichage de page d'edition
                    if (empty($_POST)) {

                        $paramsView = ["id" => $id];
                        $paramsView["utilisateur"] = $utilisateur->id;

                        $objet = $this->depot->selectOne($classe, $table, $paramsView);

                        if (!$objet) {
                            $this->message("Erreur objet ID " . $id . " - Contactez votre administrateur!", "red");
                        } else {
                            if ($utilisateur->id->getValue() !== $objet->utilisateur->id->getValue()) {
                                $message["couleur"] = "red";
                                $message["texte"] = "Vous pouvez seulement modifier vos propres ventes!";
                            } else {
                                if ($objet->status->getValue() == 2) {
                                    $message["couleur"] = "red";
                                    $message["texte"] = "La vente est terminée, vous ne pouvez plus la modifier!";
                                } else {
                                    if ($objet->status->getValue() == 1 && !empty($objet->encheres)) {
                                        $message["couleur"] = "red";
                                        $message["texte"] = "Une enchère à été placé sur votre vente, vous ne pouvez plus la modifier!";
                                    } else {
                                        $template = 'view/'.$table.'_creer';
                                        return $this->afficher($template, ["table" => $table, "name" => $classe, "objet" => $objet]);
                                    }
                                }
                            }
                        }
                    // Mis a jour d'une vente
                    } else {
                        $id = (int)$_POST["id"] ?: null;
                        $classe = self::MODELE;
                        $table = constant($classe . "::TABLE");
                
                        if ($utilisateur->id->getValue() !== $objet->utilisateur->id->getValue()) {
                            $message["couleur"] = "red";
                            $message["texte"] = "Vous pouvez seulement modifier vos propres ventes!";
                        } else {
                            if ($objet->status->getValue() > 1) {
                                $message["couleur"] = "red";
                                $message["texte"] = "La vente est terminée, vous ne pouvez plus la modifier!";
                            } else {
                                if ($objet->status->getValue() == 1 && !empty($objet->encheres)) {
                                    $message["couleur"] = "red";
                                    $message["texte"] = "Une enchère à été placé sur votre vente, vous ne pouvez plus la modifier!";
                                } else {
                                    $retour = $this->processFields($objet);

                                    $message = $retour['message'];
                                    $erreurs_form = $retour['erreurs_form'];
                                    $champsModifies = $retour['champsModifies'];
                                    $champsUpdate = [];
                                    foreach ($champsModifies as $champ) {
                                        if (isset($_POST[$champ])) {
                                            $champsUpdate[$champ] = $_POST[$champ];
                                        }
                                    }
                                    if (getenv("APP_DEBUG") == "true") {
                                        Debogueur::message($champsModifies);
                                    }
                                    if (empty($champsModifies) && empty($_FILES['images']['name'][0])) {
                                        $message["couleur"] = "red";
                                        $message["texte"] = "Aucun champ a été modifié";
                                    } else {
                                        
                                        $nouvelleImagePrincipal = (int)($_POST['image_principale'] ?? 0);

                                        // Verification des nouvelles images
                                        // L'upload des images est traitée en dernier pour pouvoir annuler la transaction BD PDO verifier dans cette ordre:
                                        // 1) Les images peut être uploadées -> Les images sont uploadées (televerserPlusieurs)
                                        // 2) La requête SQL de mis a jour d'images est validée sans erreur
                                        // 3) La requête SQL de mis a jour de la vente est validée sans erreur
                                        // Si toute est OK on valide la transaction (validerTransaction/commit)
                                        // En cas d'erreur d'une requête SQL on annule tout (annulerTransaction/rollBack et foreach unlink dans les fichiers)
                                        // ! A FAIR: Plus tard traiter tout ça dans ça propre methode pour eviter le doublon en ajout d'une vente (add())
                                        // Pour arriver ici, il y a soit des champs modifiées, soit des images uploadées, confirmer bien s'il y a des images envoyées en HTML
                                        
                                        // On verifier les images qu'on a pas changée dans le formulaire et les images actuelles
                                        $idsImagesConserves = array_map('intval', $_POST['images_conservees'] ?? []);
                                        $idsImagesActuels = [];
                                        foreach ($objet->images as $image) {
                                            $idsImagesActuels[] = (int)$image->id->getValue();
                                        }
                                        array_multisort($idsImagesConserves, $idsImagesActuels);


                                        $serviceImage = new UploadImages('./public/images/vente');
                                        $fichiersStockes = []; // Le fichiers uploadés pour effacer en cas d'annulation
                                        try {

                                            if ((empty($idsImagesConserves) && empty($idsImagesActuels) && !empty($_FILES['images']['name'][0])) || ($idsImagesConserves !== $idsImagesActuels && !empty($_FILES['images']['name'][0]))) {
                                                // Images differentes: Traiter l'upload des nouvelles images
                                                $fichiersStockes = $serviceImage->televerserPlusieurs($_FILES['images']);
                                            }
                                            if (getenv("APP_DEBUG") == "true") {
                                                Debogueur::message($fichiersStockes);
                                            }
                                            // Demarrer la transaction PDO BD
                                            $this->depot->debuterTransaction();

                                            // Insérér les images uploadés en BDD
                                            foreach ($fichiersStockes as $index => $nomFichier) {
                                                $this->depot->create("", "image", ["image" => $nomFichier, "vente" => $id]);
                                            }

                                            // Verification de changement d'image principal
                                            $imagePincipalActuel = (int)($objet->image_principale->getValue() ?? 0);
                                            
                                            // Mettre a jour si different
                                            if ($imagePincipalActuel !== $nouvelleImagePrincipal) {
                                                $champsUpdate["image_principale"] = $_POST['image_principale'] ?? 0;
                                            }
                                            // Verifier s'il n'y a pas d'erreurs de formulaire
                                            if (empty($message) && empty($erreurs_form)) {

                                                if (getenv("APP_DEBUG") == "true") {
                                                    Debogueur::message($champsUpdate);
                                                }
                                                if (!empty($champsModifies)) {
                                                    $update = $this->depot->update($classe, $table, $champsUpdate, ["id" => $id]);
                                                    if (!$update) {
                                                        throw new \RuntimeException("Erreur de modification. Contactez votre administrateur!");
                                                    }
                                                }
                                                $this->depot->validerTransaction();
                                                $message["couleur"] = "green";
                                                $message["texte"] = "La vente a bien été modifiée!";
                                                $objet = $this->depot->selectOne($classe, $table, ["id" => $id]);
                                                return $this->afficher("view/vente", ['objet' => $objet, 'id' => $id, 'message' => $message]);
                                            } else {
                                                throw new \RuntimeException("Il y a des erreurs dans votre formulaire! Consulter les champs.");
                                            }

                                        } catch (\RuntimeException $erreur) {
                                            // Upload lui-meme a echoue avant meme le debut de la transaction -> rien a annuler en base
                                            $this->depot->annulerTransaction();
                                            $message["couleur"] = "red";
                                            $message["texte"] = "Erreur upload d'images ou modification: " . $erreur->getMessage();

                                            if (getenv("APP_DEBUG") == "true") {
                                                Debogueur::message($erreur);
                                            }
                                        } catch (\PDOException $erreur) {
                                            $this->depot->annulerTransaction();

                                            // Les fichiers sont deja sur le disque - effacer puisque le rollback SQL ne les touche pas
                                            foreach ($fichiersStockes as $nomFichier) {
                                                $chemin = './public/images/vente/' . $nomFichier;
                                                if (is_file($chemin)) {
                                                    unlink($chemin);
                                                }
                                            }
                                            $this->message("Erreur base de données! Contactez votre administrateur!", "red");
                                        }

                                        $template = 'view/'.$table.'_creer';
                                        return $this->afficher($template, ["objet" => $objet, "message" => $message, "erreurs_form" => $erreurs_form]);
                                    }
                                }
                            }
                        }
                    }
                    return $this->afficher('view/vente', ["objet" => $objet, "message" => $message]);
                }
            }
        }
    }
    
    public function del(?array $params = []) {
    // Function del
    // Role: Efface une vente de la base de données
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
        $message = [];
        $utilisateur = $this->session->userConnected();
        if ($utilisateur) {
            $classe = constant(get_called_class() . "::MODELE");
            $table = constant($classe . "::TABLE");

            if (!isset($_GET["id"]) || empty($_GET["id"])) {
                $this->message("Aucun ID de vente pour effacer!", "red", true);
            } else {
                $id = $_GET["id"];
                $objet = $this->depot->selectOne($classe, $table, ["id" => $id]);
                if (!$objet) {
                    $this->message("Vente non trouvée!", "red", true);
                } else {
                    if ($utilisateur->id->getValue() !== $objet->utilisateur->id->getValue()) {
                        $message["couleur"] = "red";
                        $message["texte"] = "Vous pouvez seulement effacer vos propres ventes!";
                    } else {
                        if ($objet->status->getValue() > 0) {
                            $message["couleur"] = "red";
                            $message["texte"] = "La vente est terminée ou en cours, vous ne pouvez plus la effacer!";
                        } else {
                            if ($objet->status->getValue() == 1 && !empty($utilisateur->encheres)) {
                                $message["couleur"] = "red";
                                $message["texte"] = "Une enchère à été placé sur votre vente, vous ne pouvez plus la effacer!";
                            } else {
                                $effacer = $this->depot->delete($classe, $id);
                                if (!$effacer) {
                                    $message["couleur"] = "red";
                                    $message["texte"] = "Erreur effacer objet ID " . $id . ": Contactez votre administrateur!";
                                } else {
                                    // Effacer les images de la vente, les données en BDD sont dejáa effacées automatiquemente par la CE
                                    foreach ($objet->images as $image) {
                                        $chemin = './public/images/vente/' . $image->image;
                                        if (is_file($chemin)) {
                                            unlink($chemin);
                                        }
                                    }
                                    $message["couleur"] = "green";
                                    $message["texte"] = "Objet ID " . $id . " effacé!";
                                }
                            }
                        }
                    }
                    $this->afficher('view/vente', ["objet" => $objet, "message" => $message]);
                }
            }
        } else {
            $this->message("Vous devez être connecté(e) pour supprimer une vente!", "red", true);
        }
    }
    public function add(?array $params = []): void {
    // Function add
    // Role: Traite et prépare l'affichage de creation d'une vente OU traite la creation d'une vente
    //
    // Parametres: $params - GET/POST envoiés par le router
    // Retour: L'affichage du template

        $classe = self::MODELE;
        $table = constant($classe . "::TABLE");

        $utilisateur = $this->session->userConnected();
        if (!$utilisateur) {
            $this->message("Connection requis pour creer une vente!", "red");
        } else {
                $paramsView["utilisateur"] = $utilisateur->id;

            if (empty($_POST)) {
                $view = __DIR__ . '/../../src/template/page/view/' . $table . '_creer.php';
                $template = 'view/'.$table.'_creer';
                $this->afficher($template, ["table" => $table, "name" => $classe]);
            } else {
                $titre = $_POST['titre'];
                $description = $_POST['description'];
                $categorie = 1;
                $utilisateur = $this->session->userConnected();
                $paramsFiltres = [
                    'utilisateur' => $utilisateur->id,
                    'titre' => $titre,
                    'description' => $description,
                    'categorie' => $categorie
                ];
                $serviceImage = new UploadImages('./public/images/vente');
                $this->depot->create(Vente::class, "", $paramsFiltres);
                $venteId = $this->depot->dernierId();
                if (!empty($_FILES['images']['name'][0])) {
                    try { 
                        $fichiersStockes = $serviceImage->televerserPlusieurs($_FILES['images']);

                        $indexPrincipale = (int)($_POST['image_principale_index'] ?? 0);

                        foreach ($fichiersStockes as $index => $nomFichier) {
                            $this->depot->create("", "image", ["image" => $nomFichier]);
                            $imageId = $this->depot->dernierId();

                            $this->depot->create("", "image_vente", ["vente" => $venteId, "image" => $imageId]);

                            if ($index === $indexPrincipale) {
                                $this->depot->update("", "vente", ["image_principale" => $imageId], ["id" => $venteId]);
                            }
                        }
                    } catch (\RuntimeException $erreur) {
                        $this->message("Erreur upload: " . $erreur->getMessage(), "red");
                    }
                }
                $objet = $this->depot->selectOne($classe, $table, ['id' => $venteId]);
                if ($objet) $this->afficher('view/vente', ["objet" => $objet]);
            }
        }
    }
    public function search(?array $params = []) {
    // Fonction index - ACTION recherche
    // Role: Traite les parametres du formulaire pour chercher un objet
    //    et prépare l'affichage des resultats
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router

        $classe = constant(get_called_class() . "::MODELE");
        $table = constant($classe . "::TABLE");
        $champs = constant($classe . "::CHAMPS");
        $champsRecherche = constant($classe . "::CHAMPS_RECHERCHE");

        // Creer le tableau avec les paramètres qui existent et verifie qu'ils ne sont pas vides
        $optionsFormulaire = [];

        $optionsFormulaire["nom"] = $table;
        $paramsRecherche = [];
        $requete = array_merge($params ?? [], $_GET, $_POST);
        foreach ($champsRecherche as $cle) {
            if (!empty($requete[$cle])) {
                $paramsRecherche[$cle] = $requete[$cle];
            }
            if (!isset($champs[$cle]['champ_input_type'])) {
                $champs[$cle]['champ_input_type'] = "text";
            }
        }
        
        if (empty($paramsRecherche)) {
            $this->index($params);
        } else {
            if (isset($paramsRecherche["status"]) && ((int)$paramsRecherche["status"] !== 1 && (int)$paramsRecherche["status"] !== 2)) {
               $this->message("Filtres de recherche invalides!" . print_r($paramsRecherche), "blue", false);
               return;
            }

            $objets = $this->depot->select($classe, $table, $paramsRecherche);
                        
            if ($objets === false) {
                $this->message("Erreur recherche - Contactez votre administrateur!", "red", false);
            } elseif ($objets === []) {
                $this->message("Aucun Resultat", "blue", false);
            } else {
                $utilisateur = $this->session->userConnected();
                foreach ($objets as $cle => $objet) {
                    // Filtrer les ventes:
                    // 1) Effacer la vente si on ne peut pas la afficher
                    // 2) Enlever les encheres si on ne peut pas les afficher
                    $objets[$cle] = $this->filtrerVente($objet);                    
                }

                $template = "resultats_recherche";
                return $this->afficher($template, ["table" => $table, "name" => $classe, "objets" => $objets]);
            }
        }
    }
    private function processFields(?Vente $objet) {
    // Function processFields
    // Role: Verifie les champs d'une vente ajoutée ou modifiée et retourne des message d'erreurs et les champs modifiées/validées
    //
    // Parametres: Vente $objet - L'objet a modifier pour recupérer les champs
    //
    // Retour:
    //      champsModifies, message, erreurs_form

        $champs = ['titre','description','dateheure_fin','status'];
        $champsModifies = [];
        $erreurs_form = [];
        $message = [];

        // Verification des champs differents en cas de modifications
        if ($objet) {
            foreach ($champs as $champ) {
                if (isset($_POST[$champ])) {
                    $valeur = normaliserValeur($_POST[$champ] ?? null);
                    if ($objet->$champ->getValue() != $valeur) {
                        $champsModifies[] = $champ;
                    }
                }
            }
        } else {
            $champsModifies = $champs;
        }
        foreach ($champsModifies as $champ) {
            // Verification des champs
            if ($champ == 'status') {
                if ((int)$_POST[$champ] !== 0 && (int)$_POST[$champ] !== 1) {
                    $erreurs_form[$champ] = "Status invalide!";
                }
            }
            if ($champ == 'dateheure_fin') {
                $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $_POST[$champ]);
                $erreurs = \DateTime::getLastErrors();
                if (!$dateTime || ($erreurs && ($erreurs['warning_count'] > 0 || $erreurs['error_count'] > 0))) {
                    $erreurs_form[$champ] = "Date de fin invalide!";
                }
                if ($dateTime < new \DateTime()) {
                    $erreurs_form[$champ] = "La date de fin ne peut pas être antérieure à maintenant!";
                }
            }
            if ($champ == 'etat_produit') {
                if ((int)$_POST[$champ] < 1 || (int)$_POST[$champ] > 4) {
                    $erreurs_form[$champ] = "État produit invalide!";
                }
            }
        }
        if (!empty($erreurs_form)) {
            $message["couleur"] = "red";
            $message["texte"] = "Verifier les erreurs dans le formulaire!";
        }

        return ['champsModifies' => $champsModifies, 'erreurs_form' => $erreurs_form, 'message' => $message];
    }
}