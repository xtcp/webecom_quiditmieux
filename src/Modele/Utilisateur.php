<?php

// Classe: Utilisateur

// Modele de Utilisateur avec ces attribues

namespace App\Modele;
use App\Composant\Modele;


class Utilisateur extends Modele {
    public const TABLE = 'utilisateur';
    public const NAME = 'Mon Compte';
    public const DISPLAY_FIELD = 'pseudo';
    public const CHAMPS_RECHERCHE = ['pseudo', 'email'];
    public const CHAMPS = [
        "id" =>                 ["champ_nom" => "Id", "champ_type" => "int"],
        "pseudo" =>             ["champ_nom" => "Pseudo", "champ_type" => "string"],
        "email" =>              ["champ_nom" => "Email", "champ_type" => "string", "sensible" => true],
        "motdepasse" =>         ["champ_nom" => "Mot de Passe", "champ_type" => "string", "sensible" => true]
    ];
    public const CHAMPS_ASSOCIES = [
        "favoris" => [
            "champ_nom" => "Favori",
            "champ_type" => Favori::class,
            "champ_relation" => "1TON",
            "champ_cle_etrangere" => "utilisateur",
        ]
    ];
    public function __construct(

    ) {}

}