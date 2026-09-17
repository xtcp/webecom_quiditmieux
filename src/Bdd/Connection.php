<?php
//  Connection.php - Objet de la connection de la BDD
//   Role: Gére la connection à la BDD
//
//   Methodes: recupererPDO, recupererOptions

namespace App\Bdd;

use PDO;
use PDOException;
use Exception;

class Connection {
    protected PDO $bdd;
    private const OPTIONS_BDD = [
        // Declanche une exception PDOException en cas d'erreur
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        // Evite l'utilisation de fecth(PDO::FETCH_ASSOC) et permet de ommetre ce parametre
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Desactive l'emulation de la requête et envoi les deux requêtes separemment au serveur SQL pour eviter les attaques multi-octet (0x5c)
        PDO::ATTR_EMULATE_PREPARES => false,
        // Faite la conversion des types des données retournés de la BDD automatiquement (INT = int, DECIMAL = float) 
        PDO::ATTR_STRINGIFY_FETCHES => false
    ];

    public static function recupererPDO() {
    // Methode recupererPDO
    // Role: Creer et renvoyer l'objet PDO
    // 
    // Parametres: Neant
    // Retour: Objet PDO

        try {
            return new PDO(sprintf("mysql:host=%s;dbname=%s;charset=%s",
                getenv("BDD_HOTE"),
                getenv("BDD_NOM"),
                getenv("BDD_CHARSET")),
                getenv("BDD_UTILISATEUR"),
                getenv("BDD_MDP"),
                self::recupererOptions());
        } catch (PDOException $erreur) {
            throw new Exception("Erreur de connection: " . $erreur->getMessage());
        }
    }
    private static function recupererOptions(): array {
    // Methode recupererOptions
    // Role: Traites les options de l'objet PDO et reenvoi
    // 
    // Parametres: Neant
    // Retour: const Options PDO
        $options = self::OPTIONS_BDD;
        if (getenv('APP_DEBUG') == "true") {
            $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }
        return $options;
    }
}