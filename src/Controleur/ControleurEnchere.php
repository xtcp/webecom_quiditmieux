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
                        $statusVente = (int)$vente->status->getValue();
                        if ($statusVente == 2) {
                            $this->message("La vente est terminée vous ne pouvez plus enchèrir!", "red");
                        } else {
                            if ($statusVente != 1) {
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
    public function search(?array $params = []) {
    // Fonction search - ACTION recherche
    // Role: Traite les parametres du formulaire pour chercher un objet
    //    et prépare l'affichage des resultats
    //
    // Parametres: $params - Parametres GET/POST envoyez par le router

        $classe = self::MODELE;
        $table = constant($classe . "::TABLE");

        $requete = array_merge($params ?? [], $_GET, $_POST);

        if (!isset($requete['vente'])) {
            $this->message("Aucune vente pour rechercher les encheres!", "red");
        } else {
            $idsVente = [];
            $tempIds = explode(',', $requete['vente']);
            foreach ($tempIds as $id) {
                $id = (int)$id;
                if ($id !== 0) {
                    $idsVente[] = $id;
                }
            }
            $utilisateur = $this->session->userConnected();
            if (!$utilisateur) {
                $this->message("Connection necessaire pour consulter les enchères d'une vente!", "blue", false);
                $objets = [];
            } else {
                foreach ($idsVente as $idVente) {
                    $objets = $this->depot->select($classe, $table, ["vente" => $idVente]);
                    if ($objets === false) {
                        $this->message("Erreur recherche - Contactez votre administrateur!", "red", false);
                        return;
                    } elseif ($objets === []) {
                        $this->message("Aucun Resultat", "blue", false);
                        return;
                    } else {
                    
                        $vente = $this->depot->selectOne(Vente::class, Vente::TABLE, ["id" => $idVente]);
                        
                        if (!$vente || $vente === []) {
                            $this->message("Aucun Resultat", "blue", false);
                            $objets = [];
                        } else {
                            $statusVente = (int)$vente->status->getValue();
                            $dateTime = \DateTime::createFromFormat('Y-m-d\TH:i:s', $vente->dateheure_fin);
                            if ($dateTime < new \DateTime()) {
                                $this->depot->update(Vente::class, "vente", ["status" => 2], ["id" => (int)$vente->id->getValue()]);
                                $statusVente = 2;
                            }

                            $estProprietaire = false;
                            if ($utilisateur && ((int)$vente->utilisateur->id->getValue() === (int)$utilisateur->id->getValue())) {
                                $estProprietaire = true;
                            }
                            if ($statusVente === 0) {
                                $this->message("La vente n'as pas demarrée", "blue", false);
                                $objets = [];
                            }
                            if ($statusVente > 0) {
                                $nonAutorisee = true;
                                $aEncherie = false;
                                if ($utilisateur) {
                                    foreach ($vente->enchere as $enchere) {
                                        if ((int)$enchere->utilisateur->id->getValue() === (int)$utilisateur->id->getValue()) {
                                            $aEncherie = true;
                                            break;
                                        }
                                    }
                                    // Si l'utilisateur este connectée et a enréchi, ne pas enlever
                                    if ($aEncherie) $nonAutorisee = false;
                                    // Si l'utilisateur este le proprietaire, ne pas enlever
                                    if ($estProprietaire) $nonAutorisee = false;
                                }
                                if ($nonAutorisee) {
                                    $objets = [];
                                }
                            }
                        }
                        
                    }
                }
            }
            return $this->afficher("", ["table" => $table, "name" => $classe, "objets" => $objets], true);
        }
    }
}