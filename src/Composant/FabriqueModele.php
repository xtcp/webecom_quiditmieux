<?php
// Composant FabriqueModele.php
//
// Fabrique (Factory) pour enregistrer et recuperer les objets Modele qui sont dejá créer plutôt que initialiser un nouveau à chaque fois
//
//

namespace App\Composant;

class FabriqueModele {
    protected array $objets = [];

    protected int $compteurCrees = 0;
    protected int $compteurReutilises = 0;

    public function obtenir(string $classe, $id, callable $fabricant) {
    // Role: Verifier si une modele este dejáa créer et retourne l'objet/methode
    //
    // Parametres: Néant
    // Retour: Objet ou methode

        if ($id === null) {
            $this->compteurCrees++;
            return $fabricant();
        }

        if (isset($this->objets[$classe][$id])) {
            $this->compteurReutilises++;
            return $this->objets[$classe][$id];
        }

        $this->compteurCrees++;
        $this->objets[$classe][$id] = $fabricant();
        return $this->objets[$classe][$id];
    }

    public function reinitialiser(): void {
    // Role: Reinitialiser les données de la fabrique
    // Parametres: Néant
    // Retour: Néant
    
        $this->objets = [];
        $this->compteurCrees = 0;
        $this->compteurReutilises = 0;
    }

    public function statistiques(): array {
    // Role: Construire et retournes les données statistiques de la fabrique (objets, demandes)
    // Parametres: Néant
    // Retour: Liste avec les données de statistiques de la fabrique
        $parClasse = [];
        foreach ($this->objets as $classe => $items) {
            $parClasse[$classe] = count($items);
        }

        return [
            'objets_crees'      => $this->compteurCrees,
            'objets_reutilises' => $this->compteurReutilises,
            'total_demandes'    => $this->compteurCrees + $this->compteurReutilises,
            'par_classe'        => $parClasse,
        ];
    }
}