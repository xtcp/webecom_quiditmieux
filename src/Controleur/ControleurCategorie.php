<?php
// Contrôleur ControleurCategorie.php
// Role:  Prends les paramètres envoié par l'utilisateur, recupére les donnés des repositories, prepare pour l'affichage et appele les templates
//
// Methodes:
//      
namespace App\Controleur;

use App\Composant\Controleur;
use App\Modele\Categorie;

class ControleurCategorie extends Controleur {
    public const MODELE = Categorie::class;
    public const API_URL = "https://api.mywebecom.ovh/play/qdm/categ.php";
    public const API_METHODE = "GET";
    private array $paramsRechercher = ["id", "libelle"];

    protected array|bool $categories = [];

    public function view(?array $params = [], ?bool $forceAPI = false) {
    // Function list - ACTION néant ou recherche
    // Role: Recupére les donnés par API et retourne le resultat en JSON
    //
    // Parametres:
    //      id ou ids - Id ou les Ids d'une categorie
    //
    // Retour:
    //      Retour des données en JSON forcée
        $idsBruts = $_POST["ids"] ?? $_GET["ids"] ?? $_POST["id"] ?? $_GET["id"] ?? null;

        if (empty($idsBruts)) {
            $this->message("Aucun ID pour afficher les categories!", "red");
            return;
        }
        $ids = array_values(array_filter(
            array_map('trim', explode(",", (string)$idsBruts)),
            fn($id) => $id !== ''
        ));

        $objets = [];
        foreach ($ids as $id) {
            $objet = $this->api->select(self::class, ["id" => $id]);
            if ($objet) {
                $objets[] = $objet;
            }
        }
        if (!$objets) {
            $this->message("Erreur recherche - Contactez votre administrateur!", "red", false);
            return;
        }

        return $this->afficher('', ["objets" => $objets], true);
    }

    public function search(?array $params = [], ?bool $forceAPI = false) {
    // Function list - ACTION néant ou recherche
    // Role: Traite les donnés et prépare l'affichage des categories
    //
    // Parametres:
    //      id ou ids - Id ou les Ids d'une categorie
    //
    // Retour:
    //      Retour des données en JSON forcée
    
        $texte = $_GET["text"] ?? $_POST["text"] ?? null;

        if (empty($texte)) {
            $this->message("Aucun texte pour rechercher les categories!", "red");
            return;
        }
        $objets = $this->api->select(self::class, ["search" => $texte]);

        if (!$objets) {
            $this->message("Erreur recherche - Contactez votre administrateur!", "red", false);
            return;
        }
        $objets = array_map(
            fn($id, $libelle) => ["id" => $id, "libelle" => $libelle],
            array_keys($objets),
            array_values($objets)
        );
        return $this->afficher('', ["objets" => $objets], true);
    }
}
?>