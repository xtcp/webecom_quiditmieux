<?php
// Contrôleur ControleurFavori.php
// Role:  Prends les paramètres envoié par l'utilisateur, recupére les donnés des repositories, prepare pour l'affichage et appele les templates
//
// Methodes:
//     (utilise les methodes du contrôleur générique)

namespace App\Controleur;

use App\Composant\Controleur;
use App\Modele\Favori;

class ControleurFavori extends Controleur {
    public const MODELE = Favori::class;
    private array $paramsRechercher = [];

    protected array $encheres = [];

}