<?php
// Composant Debogueur.php
//
// Debogueur enregistre des données de deboggage pour afficher en base de page si la variable de deboggage est activée
//
//

namespace App\Composant;

class Debogueur {
    protected static array $requetesSQL = [];
    protected static array $requetesAPI = [];
    protected static array $messages = [];
    protected static float $debut;
    protected static ?FabriqueModele $fabrique = null;
    

    public static function demarrer(): void {
    // Role: Demarre le compteur de temps de chargement de page
    //
    // Parametres: Néant
    //
    // Retour: Néant

        self::$debut = microtime(true);
    }
    public static function enregistrerFabrique(FabriqueModele $fabrique): void {
    // Role: Enregistre une fabrique modele
    //
    // Parametres: $fabrique - L'objet FabriqueModele
    //
    // Retour: Néant
        self::$fabrique = $fabrique;
    }
    public static function enregistrerSQL(string $sql, array $params, float $duree): void {
    // Role: Enregistre une requête SQL
    //
    // Parametres:    $sql - La requête SQL 
    //                $params - Les params de la requête SQL
    //                $duree - Le temps d'execution de la requête
    //
    // Retour: Néant    
        self::$requetesSQL[] = [
            'sql'     => $sql,
            'params'  => $params,
            'duree'   => round($duree * 1000, 2),
        ];
    }

    public static function enregistrerAPI(string $url, float $duree): void {
    // Role: Enregistre une requête SQL
    //
    // Parametres:    $url - L'URL de la requête
    //                $duree - Le temps d'execution de la requête
    //
    // Retour: Néant    
        self::$requetesAPI[] = [
            'url'     => $url,
            'duree'   => round($duree * 1000, 2),
        ];
    }
    public static function statistiques(): array {
    // Role: Construit et retourne les données des statistiques d'execution
    //
    // Parametres: Néant
    // Retour: Liste avec les details d'execution 

        return [
            'temps_total_ms'   => round((microtime(true) - self::$debut) * 1000, 2),
            'memoire_actuelle' => self::formaterOctets(memory_get_usage()),
            'memoire_max'      => self::formaterOctets(memory_get_peak_usage()),
            'numero_requetes_sql'  => count(self::$requetesSQL),
            'numero_requetes_api'  => count(self::$requetesAPI),
            'requetes_sql'     => self::$requetesSQL,
            'requetes_api'     => self::$requetesAPI,
            'fabrique_modele'  => self::$fabrique ? self::$fabrique->statistiques() : null,
            'messages'         => self::$messages
        ];
    }

    protected static function formaterOctets(int $octets): string {
    // Role: Conversion de int memoire en o/Ko/Mo/Go
    //
    // Parametres: Néant
    // Retour: La memoire en o/Ko/Mo/Go
    
        $unites = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        while ($octets >= 1024 && $i < count($unites) - 1) {
            $octets /= 1024;
            $i++;
        }
        return round($octets, 2) . ' ' . $unites[$i];
    }
    public static function message(mixed $donnee, string $etiquette = ''): void {
    // Role: Enregistre un message ou une variable de deboggage custom (equivalent d'un echo/print_r visible dans la barre de debug)
    //
    // Parametres:    $donnee - La chaine, l'array ou l'objet a inspecter
    //                $etiquette - Un libelle optionnel pour retrouver le message plus facilement
    //
    // Retour: Néant

        self::$messages[] = [
            'etiquette' => $etiquette,
            'contenu'   => is_string($donnee) ? $donnee : print_r($donnee, true),
            'fichier'   => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? null,
            'ligne'     => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? null,
        ];
    }
}