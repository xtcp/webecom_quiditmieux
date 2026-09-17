<?php

// Classe: Image

// Modele de Image avec ces attribues

namespace App\Modele;

use App\Composant\Modele;


class Image extends Modele {
    public const TABLE = 'image';
    public const NAME = 'Images';
    public const DISPLAY_FIELD = 'image';
    public const CHAMPS_RECHERCHE = ['vente', 'image'];
    public const CHAMPS = [
        "id" =>                  ["champ_nom" => "ID", "champ_type" => "int"],
        "vente" =>               ["champ_nom" => "Vente", "champ_type" => Vente::class],
        "image" =>                 ["champ_nom" => "Nom", "champ_type" => "string"]
    ];
    public function __construct(

    ) {}
}