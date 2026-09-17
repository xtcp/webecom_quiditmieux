<?php

// Classe: Categorie

// Modele de Categorie avec ces attribues

namespace App\Modele;
use App\Composant\Modele;


class Categorie extends Modele {
    public const TABLE = 'categorie';
    public const NAME = 'Categories';
    public const DISPLAY_FIELD = 'libelle';
    public const CHAMPS_RECHERCHE = ['libelle'];
    public const CHAMPS = [
        "id" =>                 ["champ_nom" => "ID", "champ_type" => "int"],
        "libelle" =>            ["champ_nom" => "Libellé", "champ_type" => "string"]
    ];

    public function __construct(
    ) {}
}