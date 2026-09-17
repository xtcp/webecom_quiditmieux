<?php
//  Session.php
// Role: Gérer la session de l'utilisateur
// 
// Methodes:
//      recupererId, recuperer, contient, connection, update, isConnected, userConnected, disconnect


namespace App\Http;

use App\Modele\Utilisateur;

class Session {
    private int $id = 0;
    private bool $connected = false;
    private int $last = 0;
    private ?Utilisateur $utilisateur = null;
    private static ?Session $instance = null;
    private $timeout = 1800;

    public function __construct() {
        // Role: Construction de la classe, demarre la session et les variables
        //
        // Paramètres:
        //      Néant
        //  Retour: Néant

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->connected = $_SESSION["connected"] ?? false;
        $this->id = $_SESSION["id"] ?? 0;
        $this->last = $_SESSION["last"] ?? 0;

        if (isset($_SESSION['last'])) {
            if (time() - $_SESSION['last'] > $this->timeout) {
                session_unset();
                session_destroy();
                header("Location: index.php");
                exit;
            }
        }
        $_SESSION['last'] = time();
    }

    public function recupererId() : int {
        // Role: Retourne l'ID de l'utilisateur en session
        //
        // Paramètres:
        //      Néant
        //  Retour: Néant
    
        if ($this->connected && $this->id > 0) {
            return $this->id;
        } else {
            return 0;
        }
    }
    public static function recuperer() {
        // Role: Crée ou récupére le singleton Session
        //
        // Paramètres:
        //      Néant
        //  Retour: Néant

        if (self::$instance == null) {
            self::$instance = new Session();
        }
        return self::$instance;
    }
    public function contient(string $cle): bool {
        // Role: Verifier si la _SESSION contien une cle
        //
        // Paramètres:
        //      string $cle - La clé a verifier
        //  Retour: bool (isset)
        return isset($_SESSION[$cle]);
    }
    public function connection(Utilisateur $utilisateur) {
        // Role: Define un utilisateur comme connecté
        //
        // Paramètres:
        //      $utilisateur - Objet Utilisateur
        //  Retour: Néant
        session_regenerate_id(true);
        $_SESSION["id"] = $utilisateur->id->getValue();
        $_SESSION["connected"] = true;
        $_SESSION["last"] = time();
        $this->connected = true;

        $this->id = $utilisateur->id->getValue();
        $this->last = $_SESSION["last"];
        $this->utilisateur = $utilisateur;
    }
    public function update(Utilisateur $utilisateur) {
        // Role: Actualise la session et l'objet avec l'utilisateur indiqué
        //
        // Paramètres:
        //      $utilisateur - Objet du modele Utilisateur
        //
        //  Retour: Néant

        $_SESSION["id"] = $utilisateur->id->getValue();
        $this->id = $utilisateur->id->getValue();
        $this->utilisateur = $utilisateur;
    } 

    public function isConnected() : bool {
        // Role: Verifie si un utilisateur est connecté
        //
        // Paramètres:
        //      Néant

        if ($this->connected && $this->id > 0) {
            return $this->connected;
        } else {
            return false;
        }
    }

    public function userConnected() {
        // Role: Retourne l'utilisateur connecté
        //
        // Paramètres:
        //      Néant

        if ($this->connected && $this->id > 0) {
            return $this->utilisateur;
        } else {
            return false;
        }
    }

    public function disconnect() {
        // Role: Marque l'utilisateur comme non connecté et efface les variables de session
        //
        // Paramètres:
        //      Néant

        $_SESSION = [];
        $this->connected = false;
        $this->id = 0;
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
    }
}