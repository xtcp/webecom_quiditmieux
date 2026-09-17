<?php

// Classe: Favori

// Modele de Favori avec ces attribues

namespace App\Modele;

use App\Composant\Modele;


class Favori extends Modele {
    public const TABLE = 'favori';
    public const NAME = 'Favoris';
    public const DISPLAY_FIELD = 'id';
    public const CHAMPS_RECHERCHE = ['utilisateur', 'vente'];
    public const CHAMPS = [
        "id" =>                 ["champ_nom" => "ID", "champ_type" => "int"],
        "utilisateur" =>        ["champ_nom" => "Nom", "champ_type" => Utilisateur::class],
        "vente" =>              ["champ_nom" => "Vente", "champ_type" => Vente::class]
    ];
    public function __construct(

    ) {}
}