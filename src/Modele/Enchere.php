<?php

// Classe: Enchere

// Modele de Enchere avec ces attribues

namespace App\Modele;
use App\Modele\Utilisateur;
use App\Modele\Vente;
use App\Composant\Modele;

class Enchere extends Modele {
    public const TABLE = 'enchere';
    public const NAME = 'Encheres';
    public const DISPLAY_FIELD = 'id';
    public const DATETIME = 'dateheure';
    public const CHAMPS_RECHERCHE = ['prix', 'vente', 'dateheure'];
    public const CHAMPS = [
        "id" =>                 ["champ_nom" => "ID", "champ_type" => "int"],
        "prix" =>               ["champ_nom" => "Prix", "champ_type" => "int"],
        "vente" =>              ["champ_nom" => "Vente", "champ_type" => Vente::class],
        "dateheure" =>          ["champ_nom" => "Date", "champ_type" => "string"]
    ];
    public const CHAMPS_ASSOCIES = [
        "utilisateur" => [
            "champ_nom" => "Utilisateur",
            "champ_type" => Utilisateur::class,
            "champ_custom_type" => "user",
            "champ_relation" => "1TO1",
            'champ_cle_interne' => 'utilisateur'
        ],
    ];
    public function __construct(

    ) {}
}