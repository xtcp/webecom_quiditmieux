<?php
// Contrôleur ControleurUtilisateur.php
// Role:  Prends les paramètres envoié par l'utilisateur, recupére les donnés des repositories, prepare pour l'affichage et appele les templates
//
// Methodes:
//      index, getConnectedUser, telecharger, connecter, deconnecter, edit, create

namespace App\Controleur;

use App\Composant\Controleur;
use App\Composant\Debogueur;
use App\Modele\Utilisateur;
use App\Modele\Vente;
use Exception;
use ReCaptcha\ReCaptcha;


class ControleurUtilisateur extends Controleur {
    public const MODELE = Utilisateur::class;
    private array $paramsRechercher = ["pseudo", "email"];

    protected array $utilisateur = [];

    public function index(?array $params = []) {
    // Function index 
    // Role: Traite les donnés et prépare l'affichage du formulaire de recherche
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router
    // Retour: Néant
 
        $encheresEnCours = [];
        $mesEncheresEnCours = [];
        $mesEncheresRemportees = [];
        $dernieresVentes = [];


        // Imprimer la table des ventes et le formulaire de recherche
        //include(__DIR__ . './../template/base.php');
        if (!$this->session->isConnected()) {
            return $this->connecter();
        } else {
        
            $utilisateur = $this->session->userConnected();
            $encheresEnCours_paramsView['status'] = 1;
            $mesEncheresEnCours_paramsView['enchere.utilisateur'] = $utilisateur->id;
            $mesEncheresEnCours_paramsView['status'] = 1;
            $paramsView["utilisateur"] = $utilisateur->id;
  
            $encheresEnCours = $this->depot->select(
                Vente::class,
                Vente::TABLE,
                $encheresEnCours_paramsView,
                "ORDER BY (SELECT MAX(`e`.`dateheure`) FROM `enchere` `e` WHERE `e`.`vente` = `" . Vente::TABLE . "`.`id`) DESC LIMIT 10"
            );
            if (getenv("APP_DEBUG") == "true") {
                $ids = array_column($encheresEnCours ?: [], 'id');
                Debogueur::message($ids);
            }
            $mesEncheresEnCours = $this->depot->select(
                Vente::class,
                Vente::TABLE,
                $mesEncheresEnCours_paramsView,
                "ORDER BY (SELECT MAX(`e`.`dateheure`) FROM `enchere` `e` WHERE `e`.`vente` = `" . Vente::TABLE . "`.`id`) DESC LIMIT 10"
            );
            
            $dernieresVentes = $this->depot->select(Vente::class, Vente::TABLE, $paramsView, "ORDER BY `" . Vente::TABLE . "`.`dateheure_fin` LIMIT 10");


            // Afficher tableau de bord et la table des ventes 

            $fragment_dernieresVentes = $this->afficherFragment('section/tableau_de_bord_ventes', [
                "encheresEnCours" => $encheresEnCours,
                "mesEncheresEnCours" => $mesEncheresEnCours,
                "dernieresVentes" => $dernieresVentes
            ]);

            $this->afficher('tableau_de_bord', ['sectionVentes'  => $fragment_dernieresVentes]);
            
        }

    }

    public function telecharger() {
    // Function telecharger
    // Role: Prepare les donnés d'utilisateur (SESSION et BDD) et les re-envoi en fichier de texte
    //
    // Parametres: Néant
    //
    // Retour:
    //       Fichier texte

        $utilisateur = $this->session->recupererId();

        if ($utilisateur) {
            $utilisateur = $this->depot->selectOne(Utilisateur::class, Utilisateur::TABLE, ["id" => $utilisateur]);
         
            $contenu = "Rapport des données de l'utilisateur. Rendu le ".date('Y-m-d H:i:s')."\n";
            $contenu .= "Données dans la BDD: \n";
            $contenu .= json_encode($utilisateur);
            $contenu .= "\n";
            $contenu .= "Id de session: ".session_id()."\n";
            $contenu .= "Données de session: \n";
            $contenu .= json_encode($_SESSION);
            header('Content-Type: text/plain; charset=UTF-8');
            header('Content-Disposition: attachment; filename="donnees_utilisateur.txt"');

            echo $contenu;
            exit;
        }

    }
    public function connecter() {
    // Function connecer
    // Role: Connecter un utilisateur avec pseudo ou email et mot de passe
    //
    // Parametres: POST: pseudoemail, motdepasse
    //
    // Retour:
    //       Retour page index ou afficher formulaire avec erreur de connection
        $erreur_login = "Mot de passe erroné ou email/pseudo non trouvé!";
        $message = [];
        $emailpseudo = "";
        if ($this->session->isConnected()) {
            $this->message("Utilisateur déjà connectée!", "red", true);
        } else {
            
            if (isset($_POST["emailpseudo"])) {
                $emailpseudo = $_POST["emailpseudo"];
                try {
                    if (str_contains($emailpseudo, "@")) {
                        $utilisateur = $this->depot->selectOne(Utilisateur::class, Utilisateur::TABLE, ["email" => $emailpseudo]);
                    } else {
                        $utilisateur = $this->depot->selectOne(Utilisateur::class, Utilisateur::TABLE, ["pseudo" => $emailpseudo]);
                    }
                    if (!$utilisateur) {
                        $message["couleur"] = "red";
                        $message["texte"] = $erreur_login;
                    } else {
                        if (password_verify($_POST["motdepasse"], $utilisateur->motdepasse)) {
                            $this->session->connection($utilisateur);
                            echo '<script type="text/javascript">
                                    window.location.href = "index.php?utilisateur";
                                </script>';
                            exit();
                        } else {
                            $message["couleur"] = "red";
                            $message["texte"] = $erreur_login;
                        }

                    }
                } catch (Exception $erreur) {
                    $message["couleur"] = "red";
                    $message["texte"] = "Erreur: " . $erreur->getMessage();
                }
            }
            return $this->afficher('formulaire_utilisateur', ["emailpseudo" => $emailpseudo, "message" => $message]);

        }
    }

    public function deconnecter(): void {
    // Function deconnecter
    // Role: Deconnecter un utilisateur et effacer la session
    //
    // Parametres: Néant
    //
    // Retour:
    //       Retour page index ou afficher erreur
    
        if (!$this->session->isConnected()) {
            $this->message("Utilisateur déjà deconnectée!", "red", false);
        } else {
            $this->session->disconnect();
            echo '<script type="text/javascript">
                window.location.href = "index.php";
            </script>';
        exit();
        }
    }

    public function edit(?array $params = []): void {
    // Function edit
    // Role: Modification des données de l'utilisateur
    //
    // Parametres: Néant ou POST: pseudo, email, motdepasse, motdepasse_confirmation
    //
    // Retour:
    //       Retour page edit

        if ($this->session->isConnected()) {
            $utilisateur = $this->session->userConnected();
            $erreurs_form = [];
            $updateParams = [];
            $message = [];
            
            if (isset($_POST["pseudo"]) || 
                isset($_POST["email"]) ||
                isset($_POST["motdepasse"])) {
                if (isset($_POST["pseudo"]) && $_POST["pseudo"] != $utilisateur->pseudo) {
                    $pseudo = $_POST["pseudo"];
                    if (!$this->verifierPseudo($pseudo)) {
                        $erreurs_form["pseudo"] = "Pseudo invalide! Characteres acceptées: a-z, A-Z, 0-9, _ et - ";
                    }
                    $existants = $this->depot->select(Utilisateur::class, Utilisateur::TABLE, ["pseudo" => $pseudo]);
                    if ($existants) {
                        $erreurs_form["pseudo"] = "Un compte existe déjà avec ce pseudo!";
                    }

                    if (!isset($erreurs_form["pseudo"])) {
                        $updateParams["pseudo"] = $pseudo;
                    }
                }
                if (isset($_POST["email"]) && $_POST["email"] != $utilisateur->email) {
                    $email = $_POST["email"];
                    if (!$this->verifierEmail($email)) {
                        $erreurs_form["email"] = "Ce mail est invalide! Format acceptée: email@email.fr";
                    }
                    $existants = $this->depot->select(Utilisateur::class, Utilisateur::TABLE, ["email" => $email]);
                    if ($existants) {
                        $erreurs_form["email"] = "Un compte existe déjà avec ce mail!";
                    }

                    if (!isset($erreurs_form["email"])) {
                        $updateParams["email"] = $email;
                    }
                }
                if (isset($_POST["motdepasse"]) && $_POST["motdepasse"] != "") {
                    $mdp = $_POST["motdepasse"];
                    if (!$this->verifierMdp($mdp)) {
                        $erreurs_form["motdepasse_confirmation"] = "Le mot de passe doit contenir aux moins 10 characters, une majuscule en un symbole";
                    }
                    if (!isset($_POST["motdepasse"])) {
                        $erreurs_form["motdepasse_confirmation"] = "Confirmez votre mot de passe!";
                    }
                    if ($_POST["motdepasse"] !== $_POST["motdepasse_confirmation"]) {
                        $erreurs_form["motdepasse_confirmation"] = "Les mots de passe ne correspondent pas!";
                    }
                    if (!isset($erreurs_form["motdepasse"])) {
                        $mdp = password_hash($mdp, PASSWORD_DEFAULT);
                        $updateParams["motdepasse"] = $mdp;
                    }
                }
                if (empty($updateParams)) {
                    $message["couleur"] = "blue";
                    $message["texte"] = "Aucun valeur modifiée!";
                } else {
                    if (empty($erreurs_form)) {
                        $update = $this->depot->update("", "utilisateur", $updateParams, ["id" => $utilisateur->id]);
                        if ($update) {
                            $message["couleur"] = "green";
                            $message["texte"] = "Votre profil est mis a jour!";
                        } else {
                            $message["texte"] = "Erreur de mis a jour des données, contacter votre administrateur";
                        }
                    } else {
                        $message["couleur"] = "red";
                        $message["texte"] = "Vous avez des erreurs, verifier les messages dans le formulaire!";
                    }
                }
            }
            $this->afficher('edit/utilisateur', ["utilisateur" => $utilisateur, "message" => $message, "erreurs_form" => $erreurs_form]);
        
        } else {
            $this->message("Vous n'etes pas connectée!", "red", false);

        }
    }
    public function create(?array $params = []) {
    // Function create - ACTION enregistrer
    // Role: Traite les parametres du formulaire pour créer un utilisateur
    //    apres verification anti-robot et prépare l'affichage du resultat
    //
    // Parametres:
    //      POST:
    //          pseudo, email, motdepasse, g-recaptcha-response
        $erreurs_form = [];
        $message = [];
        // Verifie d'abord la réponse anti-robot avant de traiter le reste
        if (!$this->verifierCaptcha()) {
            $message["couleur"] = "red";
            $message["texte"] = "Verification anti-robot échouée, veuillez réessayer!";
        } else {
            
            $paramsEnregistrer = [];

            $valeurVide = "";
            // Creer le tableau avec les paramètres qui existent et verifie qu'ils ne sont pas vides
            foreach (Utilisateur::CHAMPS as $cle => $valeur) {
                if ($cle === "id") continue;
                if (isset($_POST[$cle])) {
                    if (!empty($_POST[$cle])) {
                        $paramsEnregistrer[$cle] = $_POST[$cle];
                    } else {

                        $valeurVide = $cle;
                    }
                } else {

                    $valeurVide = $cle;
                }
            }
            if (empty($_POST["consentement"])) {
                $erreurs_form["consentement"] = "Pour créer un compte, vous devez accepter la politique de confidentialité";
            } else {
                // Affiche un message d'erreur si un des valeurs est vide
                if ($valeurVide) {
                    $erreurs_form[$valeurVide] = "Le champ " . $valeurVide . " ne peut pas être vide!";
                }
                if (!$this->verifierPseudo($_POST["pseudo"])) {
                    $erreurs_form["pseudo"] = "Ce pseudo est invalide! Characteres acceptées: a-zA-Z0-9, - et _!";
                }
                if (!$this->verifierEmail($_POST["email"])) {
                    $erreurs_form["email"] = "Ce mail est invalide! Format acceptée: email@email.fr";
                }
                if (!$this->verifierMdp($_POST["motdepasse"])) {
                    $erreurs_form["motdepasse"] = "Le mot de passe doit contenir aux moins 10 characters, une majuscule en un symbole";
                }
                // Verifie que l'email n'est pas deja utilisée
                $existants = $this->depot->select(Utilisateur::class, Utilisateur::TABLE, ["email" => $paramsEnregistrer["email"]]);
                if ($existants) {
                    $message["couleur"] = "red";
                    $message["texte"] = "Un compte existe déjà avec cet email!";
                }
                $existants = $this->depot->select(Utilisateur::class, Utilisateur::TABLE, ["pseudo" => $paramsEnregistrer["pseudo"]]);
                if ($existants) {
                    $message["couleur"] = "red";
                    $message["texte"] = "Un compte existe déjà avec ce pseudo! ";
                }

                if (empty($erreurs_form) && empty($message)) {
                    // Hacher le mot de passe avant l'enregistrement
                    $paramsEnregistrer["motdepasse"] = password_hash($paramsEnregistrer["motdepasse"], PASSWORD_DEFAULT);
            
                    // Appele la fonction create et verifie si ok à créer l'utilisateur
                    $resultat = $this->depot->create(Utilisateur::class, "", $paramsEnregistrer);
                    if ($resultat == false) {
                        $message["couleur"] = "red";
                        $message["texte"] = "Impossible de créer l'utilisateur - Contactez votre administrateur!";
                    } else {
                        $idAjoutee = $this->depot->dernierId();
                        $message["couleur"] = "green";
                        $message["texte"] = "Utilisateur créée! Vous pouvez maintenant vous connecter à votre compte!";
                    }
                }
            }
        }
        if (!empty($erreurs_form) && empty($message)) {
            $message["couleur"] = "red";
            $message["texte"] = "Verifier les erreus dans les champs!";
        }
        $this->afficher('formulaire_utilisateur', ["message" => $message, "erreurs_form" => $erreurs_form]);
            
    }
    public function getConnectedUser() {
    // Function getConnectedUser
    // Role: Retour l'objet Utilisateur actuellement connectée
    //
    // Parametres: Néant
    //
    // Retour:
    //       Objet Utilisateur ou false

        if ($this->session->isConnected()) {
            return $this->session->userConnected();
        } else {
            return false;
        }
    }
    private function verifierCaptcha(): bool {
    // Function verifierCaptcha
    // Role: Verifie la réponse Google reCAPTCHA envoyée par le formulaire
    //
    // Parametres:
    //      POST:
    //          g-recaptcha-response
    //
    // Retour:
    //      true si la verification est réussie, false sinon

        $reponse = $_POST['g-recaptcha-response'] ?? '';
 
        if (empty($reponse)) {
            return false;
        }
 
        $recaptcha = new ReCaptcha(getenv('RECAPTCHA_SECRET_KEY'));
        $resultat = $recaptcha->verify($reponse, $_SERVER['REMOTE_ADDR']);
 
        return $resultat->isSuccess();
    }
    private function verifierEmail(string $email): bool {
    // Function verifierEmail
    // Role: Verifier si un email est valide (en utilisant filter_var FILTER_VALIDATE_EMAIL de php)
    //
    // Parametres: emal - L'email a verifier
    //
    // Retour:
    //      bool true ou false
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }
        return false;
    }
    private function verifierMdp(string $mdp): bool {
    // Function verifierMdp
    // Role: Verifier si un mot de passe est valide (min 10 characters, avec majuscule et au moins un symbole)
    //
    // Parametres: mdp - Le mot de passe a verifier
    //
    // Retour:
    //      bool true ou false
        if (preg_match('/^(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{10,}$/', $mdp)) {
            return true;
        }
        return false;
    }
    private function verifierPseudo(string $pseudo): bool {
    // Function verifierPseudo
    // Role: Verifier si un pseudo est valide (seulement characters A-Za-z0-9_-)
    //
    // Parametres: pseudo - Le pseudo a verifier
    //
    // Retour:
    //      bool true ou false
    
        if (preg_match('/^[A-Za-z0-9_-]+$/', $pseudo)) {
            return true;
        }
        return false;
    }
}
?>