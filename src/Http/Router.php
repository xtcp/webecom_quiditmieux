<?php
// Router
// Role: Acheminement des requêtes vers les controleurs et methodes qui correspond
//
// Methodes:
// orienter - Traiter l'URL, verifier l'acces et initialiser les modeles


namespace App\Http;

use App\Bdd\Depot;
use App\Http\Session;
use App\Bdd\Api;

use Exception;

class Router {
    protected Depot $depot;
    protected Session $session;
    protected Api $api;
    private array $routes = [
        "app" => ["politique"],
        "utilisateur" => ["index", "telecharger", "connecter", "deconnecter", "edit", "create", "del"],
        "vente" => ["view", "index", "add", "edit", "update", "create", "del", "search"],
        "categorie" => ["view", "search"],
        "enchere" => ["add", "search",]
    ];

    public function __construct(Depot $depot, Session $session, Api $api) {
        $this->depot = $depot;
        $this->session = $session;
        $this->api = $api;
    }

    public function orienter(string $uri) {
    // Methode orienter
    // Role: Traiter l'URL, verifier l'acces et initialiser les modeles
    //
    // Paramètres:
    //      $uri - Les données de l'URL 
    //
    // Retour:
    //      Le retour de l'action du modele

        // Enlever toutes les chemins de dossier justqu'a index.php 
        $uri = parse_url($uri, PHP_URL_PATH) ?? '';

        $pos = strpos($uri, 'index.php');
        if ($pos !== false) {
            $uri = substr($uri, $pos + strlen('index.php'));
        }
        $uri = trim($uri, '/');
        $filtre = ['/public', '/index.php'];
        $uri = str_replace($filtre, '', $uri);
        
        if (($uri === '' || $uri === '/') && !empty($_GET)) {
            // Utiliser les parametres GET si on n'as pas de redirect configurée dans le serveur web
            // Example: ?produit&action=view&id=1
            // $nomModele = "produit"
            // $nomMethode (action) = "view"
            // $parametresGET = ["id" => '1']

            $keys = array_keys($_GET);
            $nomModele = isset($keys[0]) ? $keys[0] : 'vente'; 
            
            // Definition de l'Action/Methode
            $nomMethode = isset($_GET['action']) ? $_GET['action'] : 'index';
            $params = $_GET;
            unset($params[$nomModele]);
            unset($params['action']);
            $parametresGET = $params;

        } else {
            // Filtre des URL (URI) si on a un redirect dans le serveur 
            // Example: /produit/view/1
            // $nomModele = "produit"
            // $nomMethode (action) = "view"
            // $parametresGET = ['1']

            $args = explode('/', trim($uri, '/'));

            // Controleur Utilisateur par defaut avec action/methode index
            $nomModele = !empty($segments[0]) ? $args[0] : 'vente';
            $nomMethode = !empty($segments[1]) ? $args[1] : 'index';
            $parametresGET = array_slice($args, 2);
        }
        $classeControleur = "App\\Controleur\\Controleur" . ucfirst($nomModele);
        

        if (!isset($this->routes[$nomModele])) {
            throw new Exception("404 - Modéle non authorisée: {$nomModele}", 404);
        }  else {
            if (!in_array($nomMethode, $this->routes[$nomModele])) {
                throw new Exception("404 - Action non authorisée: {$nomMethode}", 404);
            }  else {
                $controleur = new $classeControleur($this->depot, $this->session, $this->api);

                if (!method_exists($controleur, $nomMethode)) {
                    throw new Exception("404 - Methode {$nomMethode} non trouvé dans la classe: {$classeControleur}", 404);
                }
                return $controleur->$nomMethode($parametresGET);
            }
        }
    }
}