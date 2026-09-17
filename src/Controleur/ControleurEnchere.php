<?php
// Contrôleur ControleurEnchere.php
// Role: Ajoute une enchère
//
// Methodes:
//     (utilise les methodes du contrôleur générique)

namespace App\Controleur;

use App\Composant\Controleur;
use App\Modele\Enchere;
use App\Modele\Vente;

class ControleurEnchere extends Controleur {
    public const MODELE = Enchere::class;
    private array $paramsRechercher = ["utilisateur", "vente"];

    protected array $encheres = [];

    public function add(?array $params = []): void {
    // Function add
    // Role: Verifier si l'enchère existe dejá, sinon, continuer l'appel du contôleur générique
    //
    // Parametres: POST vente, enchere
    //      Néant
        if ($this->session->isConnected()) {
            if (isset($_POST["prix"]) && isset($_POST["vente"])) {
                $idVente = (int)$_POST["vente"];
                $utilisateur = $this->session->userConnected();
                $idUtilisateur = (int)$utilisateur->id->getValue();
                $prixEnchere = (int)$_POST["prix"];
                $derniereEnchere = $this->depot->selectOne(self::MODELE, Enchere::TABLE, ["vente" => $idVente], "ORDER BY `" . Enchere::TABLE . "`.`id` DESC LIMIT 1");
                if ($derniereEnchere) {
                    if ((int)$derniereEnchere->utilisateur->id->getValue() === $idUtilisateur) {
                        $this->message("Vous avez déjà lá derniére enchère!", "red");
                    }
                } else {
                    $vente = $this->depot->selectOne(Vente::class, Vente::TABLE, ["id" => $idVente]);
                    if ((int)$vente->utilisateur->id->getValue() === $idUtilisateur) {
                        $this->message("Vous ne pouvez pas enchèrir votre propre vente!", "red");
                    } else {
                        if ((int)$vente->status == 2) {
                            $this->message("La vente est terminée vous ne pouvez plus enchèrir!", "red");
                        } else {
                            if ((int)$vente->status != 1) {
                                $this->message("La vente n'est pas en cours, vous ne pouvez pas enchèrir!", "red");
                            } else {
                                $resultat = $this->depot->create(Enchere::class, Enchere::TABLE, ["vente" => $idVente, "utilisateur" => $idUtilisateur, "prix" => $prixEnchere]);
                                if ($resultat == false) {
                                    $this->message("Impossible d'enchèrir la vente - Contactez votre administrateur!", "red");
                                } else {
                                    if ($prixEnchere <= (int)$derniereEnchere->prix) {
                                        $this->message("Pour enchérir, il faut choisir un montant supérieur à celui de la dernière enchère!", "red");
                                    } else {
                                        if ((int)$vente->prix_depart->getValue() <= $prixEnchere) {
                                            $this->message("Pour enchérir, il faut choisir un montant supérieur au prix de départ!", "red");
                                        } else {
                                            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $vente->dateheure_fin);
                                            if ($dateTime < new \DateTime()) {
                                                $this->depot->update(Vente::class, "vente", ["status" => 2], ["id" => $idVente]);
                                                $this->message("La vent est terminée, vous ne pouvez plus enchérir!", "red");
                                            } else {
                                                $idAjoutee = $this->depot->dernierId();
                                                $this->message("Encher ID " . $idAjoutee . " ajoutée!", "green");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $this->message("Manque des paramètres pour enchérire une vente!", "red");
            }
        } else {
            $this->message("Vous devez vous connecter pour enchérire une vente!", "red");
        }
    }
}