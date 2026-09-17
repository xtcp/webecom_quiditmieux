<?php


declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_set_cookie_params(['lifetime' => 0,'path' => '/','domain' => '','secure' => true,'httponly' => true,'samesite' => 'Strict']);

$env = parse_ini_file(__DIR__ . '/app.env', false, INI_SCANNER_RAW);
foreach ($env as $key => $value) { putenv("$key=$value"); }

require_once(__DIR__ . '/vendor/autoload.php');


use App\Composant\Debogueur;
use App\Composant\FabriqueModele;
$fabrique = new FabriqueModele();

if (getenv("APP_DEBUG") == "true") {
    Debogueur::demarrer();
    Debogueur::enregistrerFabrique($fabrique);
}

require_once(__DIR__ . '/src/Fonction/helpers.php');
require_once(__DIR__ . '/src/Fonction/Exception.php');

use App\Fonction\GestionnaireException;

$gestionnaireException = new GestionnaireException();
set_exception_handler($gestionnaireException);
set_error_handler(['\App\Fonction\GestionnaireException', 'enregistrer']);


use App\Http\Router;
use App\Http\Session;
use App\Bdd\Connection;
use App\Bdd\Depot;
use App\Bdd\Api;
use App\Modele\Utilisateur;

use App\Controleur\ControleurUtilisateur;


$pdo = Connection::recupererPDO();
$session = new Session();
$depot = new Depot($pdo, $fabrique);
$api = new Api($depot);
$router = new Router($depot, $session, $api);

$controleurUtilisateur = new ControleurUtilisateur($depot, $session, $api);

$utilisateur = [];


if ($session->contient('id')) {
    $utilisateur = $depot->select(Utilisateur::class, "", ["id" => $session->recupererId()])[0];
    //$utilisateur = $controleurUtilisateur->getUser($session->recupererId());
    if ($utilisateur) {
        $session->update($utilisateur);
    } else {
        $session->disconnect();
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


$router->orienter($uri);


?>
