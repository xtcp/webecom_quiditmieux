<?php
// Contrôleur Base
// Role:  Gere les methodes et variables global partagé par les autres controleurs
//
// Methodes:
//      message - Prépare l'affichage d'un message
//      view - Prépare l'affichage d'un modele
//      search - Prépare l'affichage de recherche d'un modele
//      add - Prépare l'affichage du formulaire d'ajout d'un modele
//      del - Prépare l'affichage d'effacer un modele
//      edit - Prépare l'affichage d'edition d'un modele

namespace App\Composant;

use App\Bdd\Depot;
use App\Bdd\Api;
use App\Http\Session;
use App\Fonction\GestionnaireException;

use Exception;
use Throwable;


class Controleur {
    
    protected Depot $depot;
    protected Session $session;
    protected Api $api;

    public function __construct(Depot $depot, Session $session, Api $api) {
        $this->depot = $depot;
        $this->session = $session;
        $this->api = $api;
    }

    function message(string $message, string $couleur, bool $showButton = true): void {
    // Role: Affiche un message
    //
    // Params: 
    //      $message    - Texte du message
    //      $couleur    - Couleur du message
    //      $showButton - Montrer le button

        $this->afficher('fragment/message', [
            'message' => $message,
            'erreur' => 1,
            'couleur' => $couleur,
            'showButton' => $showButton] 
        );
        
        //include(__DIR__ . '/../../src/template/page/message.php');
    }


    public function view(?array $params = [], ?bool $forceAPI = false) {
    // Function view - ACTION consulter
    // Role: Traite l'affichage d'une vue d'un enregistrement modele
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
    //
    // ! A FAIRE: changer pour enlever la constante et faire une autre association avec le controleur->modele

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
    // Role: Traite et prépare l'affichage de modification d'un enregistrement d'un objet
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
    //
    // ! A FAIRE: changer pour enlever la constante et faire une autre association avec le controleur->modele

        $classe = constant(get_called_class() . "::MODELE");
        $table = constant($classe . "::TABLE");

        if ($this->session->isConnected()) {
                        
            // Verifie si l'id existe et n'est pas vide et affiche un message d'erreur
            if (empty($_GET["id"])) {
                $this->message("Aucun ID pour modifier l'objet!", "red");
            } else {

                $id = $_GET["id"];
                $paramsView = ["id" => $id];

                $objet = $this->depot->selectOne($classe, $table, $paramsView);
                
                if (!$objet) {
                    $this->message("Erreur objet ID " . $id . " - Contactez votre administrateur!", "red");
                } else {

                    $view = __DIR__ . '/../../src/template/page/view/' . $table . '_modifier.php';
                    if (!file_exists($view)) {
                        $template = 'view/default_modifier';
                    } else {
                        $template = 'view/'.$table.'_modifier';
                    }
                    return $this->afficher($template, ["table" => $table, "name" => $classe, "objet" => $objet]);
                }

            }
        } else {
            $this->message("Vous devez vous connecter pour utiliser cette fonction!", "red");
        }
    }

    
    public function add(?array $params = []): void {
    // Function add - ACTION ajouter
    // Role: Ajoute un enregistrement d'un objet dans la base de données
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
    //  
        if ($this->session->isConnected()) {

            $classe = constant(get_called_class() . "::MODELE");
            $table = constant($classe . "::TABLE");

            $champs = constant($classe . "::CHAMPS");
            $champsAssocies = constant($classe . "::CHAMPS_ASSOCIES");


            $paramsEnregistrer = [];
            $existeValeurVide = false;
            $valeurVide = "";
            // Creer le tableau avec les paramètres qui existent et verifie qu'ils ne sont pas vides
            foreach ($champs as $valeur => $champ) {
                if (isset($champ['champ_custom_type']) && $champ['champ_custom_type'] === 'user') {
                    $paramsEnregistrer[$valeur] = $this->session->userConnected()->id;
                } else {    
                    if (isset($_POST[$valeur])) {
                        if (!empty($_POST[$valeur])) {
                            $paramsEnregistrer[$valeur] = $_POST[$valeur];
                        } else {
                            $existeValeurVide = true;
                            $valeurVide = $valeur;
                        }
                    }
                }
            }

            foreach ($champsAssocies as $valeur => $champ) {
                if (isset($champ['champ_custom_type']) && $champ['champ_custom_type'] === 'user') {
                    $paramsEnregistrer[$valeur] = $this->session->userConnected()->id;
                } else {
                    if (isset($_POST[$valeur])) {
                        if (!empty($_POST[$valeur])) {
                            $paramsEnregistrer[$valeur] = $_POST[$valeur];
                        } else {
                            $valeurVide = $valeur;
                        }
                    }
                }
            }

            if ($valeurVide) {
                $this->message("Le champ " . $valeurVide . " ne peut pas être vide!", "red");
            } else {
                $resultat = $this->depot->create($classe, "", $paramsEnregistrer);
                if ($resultat == false) {
                    $this->message("Impossible d'ajouter l'objet ".$table." - Contactez votre administrateur!", "red");
                } else {
                    $idAjoutee = $this->depot->dernierId();
                    $this->message("Objet ".$table." ID " . $idAjoutee . " ajoutée!", "green");
                }
            }
        } else {
            $this->message("Vous devez vous connecter pour utiliser cette fonction!", "red");
        }
    }
    public function index(?array $params = []) {
    // Fonction index - ACTION recherche
    // Role: Traite les parametres du formulaire pour chercher un objet
    //    et prépare l'affichage des resultats
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
        
        $classe = constant(get_called_class() . "::MODELE");
        $table = constant($classe . "::TABLE");
        $champs = constant($classe . "::CHAMPS");

        // Creer le tableau avec les paramètres qui existent et verifie qu'ils ne sont pas vides
        $optionsFormulaire = [];

        $optionsFormulaire["nom"] = $table;
        $paramsRecherche = [];
        $requete = array_merge($params ?? [], $_GET, $_POST);
        foreach ($champs as $cle => $valeur) {
            if (!empty($requete[$cle])) {
                $paramsRecherche[$cle] = $requete[$cle];
            }
            if (!isset($champs[$cle]['champ_input_type'])) {
                $champs[$cle]['champ_input_type'] = "text";
            }
        }
        
        if (empty($paramsRecherche)) {
            return $this->afficher("formulaire", [
                "optionsFormulaire" => $optionsFormulaire,
                "champsRecherche" => $champs
                ]);
        } else {

            $objets = $this->depot->select($classe, $table, $paramsRecherche);
            if ($objets == false) {
                $this->message("Erreur recherche - Contactez votre administrateur!", "red", false);
            } elseif ($objets == []) {
                $this->message("Aucun Resultat", "blue", false);
            } else {
                $template = "resultats_recherche";

                return $this->afficher($template, ["table" => $table, "name" => $classe, "objets" => $objets]);
            }
        }
    }
    public function list(?array $params = [], ?bool $forceAPI = false) {
    // Fonction list - ACTION recherche
    // Role: Affiche toutes les enregistrements d'un modele dans la BDD
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router

        $template = "resultats_recherche";
        $classe = constant(get_called_class() . "::MODELE");
        $table = constant($classe . "::TABLE");
        if (file_exists(__DIR__ . "/../template/page/list/".$table.".php")) {
            $template = "list/".$table;
        }

        $paramsRecherche = [];
  
        $objets = $this->depot->select($classe, $table, $paramsRecherche);
        
        if ($objets === false) {
            $this->message("Erreur recherche - Contactez votre administrateur!", "red", false);
        } elseif ($objets === []) {
            $this->message("Aucun Resultat", "blue", false);
        } else {
            return $this->afficher($template, ["table" => $table, "name" => $classe, "objets" => $objets], $forceAPI);
        }
    }
    public function del(?array $params = []) {
    // Function del - ACTION effacer
    // Role: Efface un enregistrement d'un modele de la BDD
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router

        // ! A FAIRE: changer pour enlever la constante et faire une autre association avec le controleur->modele
        
        if ($this->session->isConnected()) {
            $classe = constant(get_called_class() . "::MODELE");
            $table = constant($classe . "::TABLE");
            $champs = constant($classe . "::CHAMPS");

            if (!isset($_GET["id"]) || empty($_GET["id"])) {
                $this->message("Aucun ID pour effacer l'objet!", "red");
            } else {
                $id = $_GET["id"];
                $effacer = $this->depot->delete($classe, $id);
                if (!$effacer) {
                    $this->message("Erreur effacer objet ID " . $id . " - Contactez votre administrateur!", "red");
                } else {
                    $this->message("Objet ID " . $id . " effacé!", "green", true);
                }
            }
        } else {
            $this->message("Vous devez vous connecter pour utiliser cette fonction!", "red");
        }
    }
    public function afficher(string $view, ?array $data = [], ?bool $forceAPI = false) {
    // Role: afficher un template d'une page
    //
    // Parametres: 
    //      $view - Le nom du template sans le .php
    //      $data - Les données à envoyer au template
    //
    // Retour:
    //      Néant

        $data['utilisateur'] = $this->session->userConnected();
        $data['erreur'] = null;
        $data['filAriane'] = $this->construireFilAriane($data);
        
        
        $estAPI = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
        (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
        if ($forceAPI) $estAPI = true;

        $BufferIniciale = ob_get_level();

        try {

            if ($estAPI) {
                header('Content-Type: application/json; charset=utf-8');
                unset($data['utilisateur'], $data['filAriane']);
                echo json_encode($data);
            } else {
                extract($data);

                $fichierTemplate = __DIR__ . '/../template/page/' . $view . '.php';
                
                if (!file_exists($fichierTemplate)) {
                    $fichierTemplate = __DIR__ . '/../template/' . $view . '.php';
                    if (!file_exists($fichierTemplate)) {
                        throw new Exception("Template non trouvée: " . $view);
                    }
                }

                ob_start();
                include($fichierTemplate);
                $rendu = ob_get_clean();

                $resultat = isset($resultat) ? $resultat . $rendu : $rendu;
                if (getenv("APP_DEBUG") == "true") {
                    $resultat .= $this->afficherErreursTemplate();
                }

                include(__DIR__ . '/../template/base/layout.php');
            }
        } catch (Throwable $erreur) {

            $renduPartiel = '';
            while (ob_get_level() > $BufferIniciale) {
                $renduPartiel = ob_get_clean() . $renduPartiel;
            }

            $afficherPartiel = getenv('APP_DEBUG') == "true" ? $renduPartiel : '';
            $this->gererErreur($erreur, $estAPI);
        }
    }

    public function afficherFragment(string $fragment, array $data = []): string {
    // Role: afficher un template d'une page
    //
    // Parametres: 
    //      $fragment - Le nom du fragment sans le .php
    //      $data - Les données à envoyer au fragment
    //
    // Retour:
    //      String avec le "output" du fragment
        // ! A FAIRE: filtres données sensibles
        $fichierFragment = __DIR__ . '/../../src/template/fragment/' . $fragment . '.php';

        if (!file_exists($fichierFragment)) {
            throw new Exception("Fragment non trouvé: " . $fragment);
        }
        $data['utilisateur'] = $this->session->userConnected();
        extract($data);
        ob_start();
        include($fichierFragment);
        return ob_get_clean(); 
    }

    protected function afficherErreursTemplate(): string {
    // Role: Recuperer les erreurs et afficher le template
    //
    // Parametres: NÉANT
    //
    // Retour:
    //      String avec le "output" du fragment
    
        if (getenv('APP_DEBUG') !== "true") {
            return '';
        }

        $erreurs = GestionnaireException::recupererErreurs();

        if (empty($erreurs)) {
            return '';
        }

        ob_start();
        include(__DIR__ . '/../../src/template/base/erreurs.php');
        return ob_get_clean();
    }
    protected function construireFilAriane(array $data = []): array {
    // Rôle: Construire le fil d'ariane en fonction du contrôleur et de l'action courante
    //
    // Parametres: OPTIONNEL $data - Les données de template/page comme l'objet pour recuperer le titre pour le fil d'ariane
    // Retour: tableau de ['label' => string, 'url' => string|null]

        $classeModele = defined(get_called_class() . '::MODELE') ? constant(get_called_class() . '::MODELE') : null;
        $nomModele = $classeModele && defined("$classeModele::TABLE") ? constant("$classeModele::TABLE") : null;
        $nomAffichage = $classeModele && defined("$classeModele::NAME") ? constant("$classeModele::NAME") : ucfirst((string)$nomModele);

        $action = $_GET['action'] ?? 'index';

        $filAriane = [
            ['label' => 'Page Principale', 'url' => 'index.php']
        ];


        if ($nomModele) {
            $filAriane[] = ['label' => $nomAffichage, 'url' => ""];
        }

        // Libellé du dernier niveau selon l'action
        $label = match ($action) {
            'add'    => 'Ajouter',
            'edit'   => 'Modifier',
            'search' => 'Recherche',
            'view', 'get' => isset($data['objet']) ? (string)$data['objet']->titre : null,
            'list', 'index' => null, // déjà représenté par le niveau du modèle
            default  => ucfirst($action),
        };

        if (!empty($label)) {
            $filAriane[] = ['label' => $label, 'url' => null];
        }

        return $filAriane;
    }
    function gererErreur(Throwable $erreur, bool $estAPI): void {
    // Role: Enregistrer un erreur et afficher la page/retour JSON pour l'API 
    //
    // Parametres: 
    //      $erreur - L'erreur en objet Throwable
    //      $estAPI - Boolean pour identifier si c'est une requête API
    //
    // Retour:
    //      Néant

        $codeStatus = ($erreur->getCode() >= 400 && $erreur->getCode() < 600) ? $erreur->getCode() : 500;
        http_response_code($codeStatus);

        if ($estAPI) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'error',
                'code'    => $codeStatus,
                'message' => $erreur->getMessage(),
                'trace'   => getenv('APP_DEBUG') == "true" ? $erreur->getTrace() : []
            ]);
            exit();
        }

  
        GestionnaireException::enregistrerException($erreur);

        $resultat = '';
        include(__DIR__ . '/../../src/template/base/layout.php');
        exit();
    }
    
    
}
?>