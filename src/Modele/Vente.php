<?php

// Classe: Vente

// Modele de Vente avec ces attribues

namespace App\Modele;

use App\Composant\Modele;


class Vente extends Modele {
    public const TABLE = 'vente';
    public const NAME = "Ventes";
    public const DISPLAY_FIELD = 'titre';
    public const DATETIME = 'dateheure';
    // ! A FAIR -> Les champs typees pour DateTime par example (plus complexe, necessite de types de dates entre autres)
    public const CHAMPS_RECHERCHE = ['titre', 'description', 'categorie', 'status', 'prix_depart', 'etat_produit', 'dateheure', 'dateheure_fin'];
    public const CHAMPS = [
        "id" =>                 ["champ_nom" => "Id", "champ_type" => "int", "fixe" => true],
        "titre" =>              ["champ_nom" => "Titre", "champ_type" => "string", "champ_operateur" => "LIKE"],
        "categorie" =>          ["champ_nom" => "Categorie", "champ_type" => "int"],
        "status" =>             ["champ_nom" => "Statut", "champ_type" => "int"],
        "description" =>        ["champ_nom" => "Description", "champ_type" => "string"],
        "image_principale" =>   ["champ_nom" => "Image Principale", "champ_type" => "int"],
        "etat_produit" =>       ["champ_nom" => "État Produit", "champ_type" => "int"],
        "prix_depart" =>        ["champ_nom" => "Prix Depart", "champ_type" => "int"],
        "dateheure" =>          ["champ_nom" => "Date", "champ_type" => "string"],
        "dateheure_fin" =>      ["champ_nom" => "Date Fin", "champ_type" => "string"],
    ];
    public const CHAMPS_ASSOCIES = [
        "utilisateur" => [
            "champ_nom" => "Utilisateur",
            "champ_type" => Utilisateur::class,
            "champ_relation" => "1TO1",
            "champ_custom_type" => "user",
            'champ_cle_interne' => 'utilisateur'
        ],
        "enchere" => [
            "champ_nom" => "Encheres",
            "champ_type" => Enchere::class,
            "champ_relation" => "1TON",
            "champ_cle_etrangere" => "vente"
        ],
        "images" => [
            "champ_nom" => "Images",
            "champ_type" => Image::class,
            "champ_relation" => "1TON",
            "champ_cle_etrangere" => "vente"
        ]
    ];
    public function imagePrincipale(): ?Image {
    // Role: Retrouve l'objet Image correspondant a l'id stocke dans image_principale
    //
    // Retour: L'objet Image ou null si non trouve
        foreach ($this->images as $image) {
            if ((string)$image->id === (string)$this->image_principale) {
                return $image;
            }
        }
        return null;
    }
    public function estTerminee(): bool {
    // Role: Determine si la vente est terminee (statut fermé ou date de fin dépassée)
    //
    // Retour: bool - true si la vente est terminée

        if ($this->status->getValue() > 1) {
            return true;
        }

        $dateFin = \DateTime::createFromFormat('Y-m-d H:i:s', (string)$this->dateheure_fin);

        if (!$dateFin) {
            return false; // date invalide/absente -> ne pas planter, traiter comme non terminée
        }

        return $dateFin < new \DateTime();
    }
    public function derniereEnchere(): ?Enchere {
    // Role: Retrouve l'enchère la plus élevée sur cette vente (les enchères ne pouvant
    //       qu'augmenter, la plus élevée est toujours aussi la dernière placée)
    //       Ne fait aucune requête SQL supplémentaire : l'association "enchere" est déjà
    //       chargée par le LEFT JOIN de Depot::select()
    //
    // Retour: L'objet Enchere le plus élevé, ou null si aucune enchère n'a été placée

            $plusHaute = null;
            if ($this->enchere) {
                foreach ($this->enchere as $enchere) {
                    if ($plusHaute === null || $enchere->prix->getValue() > $plusHaute->prix->getValue()) {
                        $plusHaute = $enchere;
                    }
                }
            }
            return $plusHaute;
        }
    public function __construct(

    ) {}
}