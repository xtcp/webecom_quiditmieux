<?php
// Contrôleur ControleurApp.php
// Role:  Affiche la page de politique
//
// Methodes:
//      politique

namespace App\Controleur;

use App\Composant\Controleur;

class ControleurApp extends Controleur {
    public const MODELE = "";

    public function politique() {
    // Function politique - ACTION politique
    // Role: Affiche la page de la Politique de confidentialité
    //
    // Parametres: Néant

        $this->afficher('politique', []);
    }
}
?>