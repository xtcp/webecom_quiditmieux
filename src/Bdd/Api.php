<?php
//  Api.php
//    Role: Recuperer des données d'une API
//
// Methodes: select
//      
namespace App\Bdd;
use App\Composant\Debogueur;

class Api {
    protected ?string $dernierErreur = null;
    protected Depot $depot;

    public function __construct(Depot $depot) {
        $this->depot = $depot;
    }

    public function select(string $classe, ?array $parametres = []): array {
    // Rôle: Rechercher dans une API
    // Paramètres:
    //      $classe - Classe du modele (pour l'URL de l'API et les IN)
    //      $parametres - Un tableau (liste simple) des paramètres à rechercher.
    //                    Une valeur en tableau (ex: ["id" => [1,3,4]]) est jointe
    //                    par virgule et la cle URL passe au pluriel (id -> ids)
    //
    // Retour: Un tableau (liste) d'objets de la classe chargés
        $debut = microtime(true);
        $url = constant($classe . "::API_URL");

        if (!empty($parametres)) {
            $paramsURL = [];
            foreach ($parametres as $cle => $valeur) {
                if (is_array($valeur)) {
                    $valeurs = array_values($valeur);
                    $cleURL = count($valeurs) === 1 ? $cle : $cle . 's';
                    $paramsURL[$cleURL] = implode(',', $valeurs);
                } else {
                    $paramsURL[$cle] = $valeur;
                }
            }
            $url .= '?' . http_build_query($paramsURL);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        $reponse = curl_exec($ch);

        if ($reponse === false) {
            $this->dernierErreur = curl_error($ch);
            return [];
        }

        $lignes = json_decode($reponse, true);
        if (!is_array($lignes)) {
            $this->dernierErreur = "Réponse API invalide";
            return [];
        }
        Debogueur::enregistrerAPI($url, microtime(true) - $debut);
        return $lignes;
    }

}