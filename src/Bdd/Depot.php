<?php
//  Depot.php
//    Role: Crée et execute les requêtes de base de donnés et créer les objets qui correspond
//
// Methodes:
//      messageErreur, dernierId, executeSQL, construireRequete, ajouterAssociations, assemblerAssociees, creerObjets, select, update, create, delete

namespace App\Bdd;
use PDO;
use PDOException;
use RuntimeException;
use Exception;
use App\Composant\Champ;
use App\Composant\FabriqueModele;
use App\Composant\Debogueur;

class Depot {
    protected PDO $bdd;
    protected FabriqueModele $fabrique;
    protected ?string $dernierErreur = null;
    protected ?string $dernierId = null;

    public function __construct(PDO $bdd, FabriqueModele $fabrique) {
        $this->bdd = $bdd;
        $this->fabrique = $fabrique;
    }
    // Affiche le dernier erreur
    public function messageErreur(): ?string {
        return $this->dernierErreur;
    }
    // Affiche le dernier ID
    public function dernierId(): ?string {
        return $this->dernierId;
    }
    // Les transactions PDO
    public function debuterTransaction(): bool {
        return $this->bdd->beginTransaction();
    }

    public function validerTransaction(): bool {
        return $this->bdd->commit();
    }

    public function annulerTransaction(): bool {
        if ($this->bdd->inTransaction()) {
            return $this->bdd->rollBack();
        }
        return false;
    }
    public function executeSQL(string $sql, ?array $params = []): bool {
        // Rôle: Executer une requête SQL
        //
        // Paramètres:
        //      $sql - Ligne brute de la requête
        //      $params - Paramètres de la requête
        //
        // Retour: true si la requête est executé ou false

        // Pour le debogueur
        $debut = microtime(true);

        if (getenv('APP_TRACE_SQL') === 'true') {
            echo "Executing: " . $sql ;
            echo "Parametres:";
            print_r($params);
        }
        try {
            // Preparer la requête dans la BDD
            $req = $this->bdd->prepare($sql);

            if (!$req) {
                return false;
            }

            // Executer la requête SQL avec les valeurs des paramètres:
            if (!$req->execute($params)) {
                return false;
            }

            // On verifie si une ligne était effacée:
            if ($req->rowCount() == 0) {
                return false;
            }
            Debogueur::enregistrerSQL($sql, $params, microtime(true) - $debut);
            return true;
        } catch (PDOException $erreur) {
            $this->dernierErreur = $erreur->getMessage();
            throw new RuntimeException("Error SQL l'ors de l'execution de la requête: " . $sql . "Erreur PDO: " . $erreur->getMessage());
        }
    }
    public function construireRequete(string $classe, string $table, ?array $paramsWHERE = [], ?string $options = "", ?array $champs = []): ?string {

        $prefixePrincipal = substr($table, 0, 1) . "_";

        $selectParts = [];
        if ($classe) {
            foreach (array_keys($classe::CHAMPS) as $champ) {
                $selectParts[] = "`$table`.`$champ` AS `{$prefixePrincipal}$champ`";
            }
        } else {
            $selectParts = $champs;
        }


        $joinParts = [];
        $associationsConfig = defined("$classe::CHAMPS_ASSOCIES") ? constant($classe . "::CHAMPS_ASSOCIES") : [];

        $this->ajouterAssociations($table, $associationsConfig, "", $selectParts, $joinParts, 1);

        $sql = "SELECT " . implode(", ", $selectParts) . " FROM `$table` " . implode(" ", $joinParts) . " WHERE " . implode(" AND ", $paramsWHERE) . " " . $options . ";";
        return $sql;
    }


    protected function ajouterAssociations(string $tableParente, array $associationsConfig, string $prefixeParent, array &$selectParts, array &$joinParts, int $profondeur, int $profondeurMax = 2): void {
        foreach ($associationsConfig as $nomAssoc => $config) {
            $classeLiee = $config['champ_type'];
            $tableLiee = $classeLiee::TABLE;
            $prefixe = $prefixeParent . substr($nomAssoc, 0, 3) . "_";

            $aliasTable = $prefixe . $tableLiee;

            foreach (array_keys($classeLiee::CHAMPS) as $champLie) {
                $selectParts[] = "`$aliasTable`.`$champLie` AS `{$prefixe}{$champLie}`";
            }

            if ($config['champ_relation'] === 'NTON') {
                $pivot = $config['champ_table_pivot'];
                $aliasPivot = "pv_" . $prefixe . $config['champ_cle_distante'] . $config['champ_cle_locale'];
                $joinParts[] = "LEFT JOIN `$pivot` `$aliasPivot` ON `$tableParente`.`id` = `$aliasPivot`.`{$config['champ_cle_locale']}`";
                $joinParts[] = "LEFT JOIN `$tableLiee` `$aliasTable` ON `$aliasPivot`.`{$config['champ_cle_distante']}` = `$aliasTable`.`id`";

                if (!empty($config['champ_champs_pivot'])) {
                    foreach ($config['champ_champs_pivot'] as $champPivot) {
                        $selectParts[] = "`$aliasPivot`.`$champPivot` AS `{$prefixe}{$champPivot}`";
                    }
                }

                if (!empty($config['champ_associations_pivot'])) {
                    foreach ($config['champ_associations_pivot'] as $nomSousAssoc => $sousConfig) {
                        $classeSousLiee = $sousConfig['champ_type'];
                        $tableSousLiee = $classeSousLiee::TABLE;
                        $sousPrefixe = $prefixe . substr($nomSousAssoc, 0, 3) . "_";
                        $aliasSousTable = $sousPrefixe . $tableSousLiee;
                        $colonnePivot = $sousConfig['champ_cle_pivot'];

                        foreach (array_keys($classeSousLiee::CHAMPS) as $champSousLie) {
                            $selectParts[] = "`$aliasSousTable`.`$champSousLie` AS `{$sousPrefixe}{$champSousLie}`";
                        }

                        $joinParts[] = "LEFT JOIN `$tableSousLiee` `$aliasSousTable` ON `$aliasPivot`.`$colonnePivot` = `$aliasSousTable`.`id`";
                    }
                }
            } elseif ($config['champ_relation'] === '1TON' || $config['champ_relation'] === '1TO1') {
                if (isset($config['champ_cle_interne'])) {
                    $joinParts[] = "LEFT JOIN `$tableLiee` `$aliasTable` ON `$tableParente`.`{$config['champ_cle_interne']}` = `$aliasTable`.`id`";
                } else {
                    $joinParts[] = "LEFT JOIN `$tableLiee` `$aliasTable` ON `$tableParente`.`id` = `$aliasTable`.`{$config['champ_cle_etrangere']}`";
                }
            }

            if ($profondeur < $profondeurMax) {
                $associationsEnfant = defined("$classeLiee::CHAMPS_ASSOCIES") ? $classeLiee::CHAMPS_ASSOCIES : [];
                if (!empty($associationsEnfant)) {
                    $this->ajouterAssociations($aliasTable, $associationsEnfant, $prefixe, $selectParts, $joinParts, $profondeur + 1, $profondeurMax);
                }
            }
        }
    }
    protected function assemblerAssociees(string $classe, array $lignes, array $associationsConfig) {
        $objetsRaw = [];
        $donneesAssocieesGroupes = []; 
        
        $prefixePrincipal = substr($classe::TABLE, 0, 1) . "_";

        foreach ($lignes as $ligne) {
            $idPrincipal = $ligne["{$prefixePrincipal}id"]; 

            if (!isset($objetsRaw[$idPrincipal])) {
                $objetsRaw[$idPrincipal] = [];
                foreach ($ligne as $cle => $valeur) {
                    if (str_starts_with($cle, $prefixePrincipal)) {
                        $objetsRaw[$idPrincipal][substr($cle, 2)] = $valeur;
                    }
                }
                
                $donneesAssocieesGroupes[$idPrincipal] = [];
                foreach (array_keys($associationsConfig) as $nomAssoc) {
                    $donneesAssocieesGroupes[$idPrincipal][$nomAssoc] = [];
                }
            }

            foreach ($associationsConfig as $nomAssoc => $config) {
                $prefixe = substr($nomAssoc, 0, 3) . "_";
                $classeLiee = $config['champ_type'];
                
                if (!empty($ligne["{$prefixe}id"])) {
                    $idLie = $ligne["{$prefixe}id"];

                    if (!isset($donneesAssocieesGroupes[$idPrincipal][$nomAssoc][$idLie])) {
                        $donneesLigneLiee = [];
                        foreach ($ligne as $cle => $valeur) {
                            if (str_starts_with($cle, $prefixe)) {
                                $donneesLigneLiee[substr($cle, strlen($prefixe))] = $valeur;
                            }
                        }
                        if (!empty($config['champ_associations_pivot'])) {
                            foreach ($config['champ_associations_pivot'] as $nomSousAssoc => $sousConfig) {
                                $sousPrefixe = substr($nomSousAssoc, 0, 3) . "_";
                                $sousDonnees = [];
                                $aUneValeur = false;
                                foreach ($donneesLigneLiee as $cle => $valeur) {
                                    if (str_starts_with($cle, $sousPrefixe)) {
                                        $sousDonnees[substr($cle, strlen($sousPrefixe))] = $valeur;
                                        unset($donneesLigneLiee[$cle]);
                                        if (!empty($valeur)) {
                                            $aUneValeur = true;
                                        }
                                    }
                                }
                                $donneesLigneLiee[$nomSousAssoc] = $aUneValeur ? $this->fabriquerObjet($sousConfig['champ_type'], $sousDonnees) : null;
                            }
                        }

                        $donneesAssocieesGroupes[$idPrincipal][$nomAssoc][$idLie] = $this->fabriquerObjet($classeLiee, $donneesLigneLiee);
                    }
                }
            }
        }

        $collectionFinal = [];

        foreach ($objetsRaw as $idPrincipal => $donneesBrutes) {
            $instanceModele = new $classe();
            $champs = [];

            foreach ($classe::CHAMPS as $nomChamp => $config) {
                $valeur = $donneesBrutes[$nomChamp] ?? null;
                $champs[$nomChamp] = new Champ($valeur, $config);
            }

            foreach ($associationsConfig as $nomAssoc => $config) {
                $listeObjetsLies = array_values($donneesAssocieesGroupes[$idPrincipal][$nomAssoc]);
                
                if (isset($config['champ_relation']) && $config['champ_relation'] === '1TO1') {
                    $valeurFinale = !empty($listeObjetsLies) ? $listeObjetsLies[0] : null;
                } else {
                    $valeurFinale = $listeObjetsLies;
                }

                $champs[$nomAssoc] = new Champ($valeurFinale, $config);
            }

            $instanceModele->hydrater($champs);
            $collectionFinal[] = $instanceModele;
        }

        return $collectionFinal;
    }
    protected function fabriquerObjet(string $classe, array $ligne) {
        $id = $ligne['id'] ?? null;
        return $this->fabrique->obtenir($classe, $id, function () use ($classe, $ligne) {
            return $this->creerObjets($classe, $ligne);
        });
    }
    public function creerObjets(string $nomClasse, array $ligne) {

        if (!class_exists($nomClasse)) {
            throw new Exception("404 - Modele {$nomClasse} non trouvée", 404);
        }

        $instanceModele = new $nomClasse();
        $champs = [];

        if (defined("$nomClasse::CHAMPS")) {

            $champs = $nomClasse::CHAMPS;

            foreach ($champs as $nomChamp => $config) {
                $valeurBrute = array_key_exists($nomChamp, $ligne) ? $ligne[$nomChamp] : null;
                $champs[$nomChamp] = new Champ($valeurBrute, $config);
            }

            $associationsConfig = defined("$nomClasse::CHAMPS_ASSOCIES") ? $nomClasse::CHAMPS_ASSOCIES : [];

            foreach ($associationsConfig as $nomAssoc => $config) {
                $prefixe = substr($nomAssoc, 0, 3) . "_";
                $classeLiee = $config['champ_type'];

                $donneesLigneLiee = [];
                $aUneValeur = false;
                foreach ($ligne as $cle => $valeur) {
                    if (str_starts_with($cle, $prefixe)) {
                        $donneesLigneLiee[substr($cle, strlen($prefixe))] = $valeur;
                        unset($ligne[$cle]);
                        if (!empty($valeur)) {
                            $aUneValeur = true;
                        }
                    }
                }

                $objetLie = $aUneValeur ? $this->fabriquerObjet($classeLiee, $donneesLigneLiee) : null;

                if (isset($config['champ_relation']) && $config['champ_relation'] !== '1TO1') {
                    $champs[$nomAssoc] = new Champ($objetLie ? [$objetLie] : [], $config);
                } else {
                    $champs[$nomAssoc] = new Champ($objetLie, $config);
                }
            }

            foreach ($ligne as $cle => $valeur) {
                if (!isset($champs[$cle])) {
                    $champs[$cle] = new Champ($valeur, []);
                }
            }
        }
        $instanceModele->hydrater($champs);
        return $instanceModele;
    }

    public function selectOne(string $classe, ?string $table = "", array $parametres = [], string $options = "", ?array $champs = []): mixed {
    // Rôle: Rechercher dans la base de données et retourne le premier resultat
    // Paramètres:
    //      $classe - Classe de l'objet
    //      $table - Table dans la base de données
    //      $parametres - Un tableau (liste simple) des paramètres à rechercher
    //      $options - Options SELECT
    //      $champs - Dans le cas d'une requête sans objet, on utilise le tableau champs pour identifier les champs
    //
    // Retour: Un tableau (liste) d'objets de la classe chargés ou un tableau (liste) avec les valeurs

        $resultats = $this->select($classe, $table, $parametres, $options, $champs);

        if (!$resultats) {
            return null;
        }
        return $resultats[0];
    }
    public function select(string $classe, ?string $table = "", array $parametres = [], string $options = "", ?array $champs = []): array|bool {
    // Rôle: Rechercher dans la base de données
    // Paramètres:
    //      $classe - Classe de l'objet
    //      $table - Table dans la base de données
    //      $parametres - Un tableau (liste simple) des paramètres à rechercher
    //      $options - Options SELECT
    //      $champs - Dans le cas d'une requête sans objet, on utilise le tableau champs pour identifier les champs
    //
    // Retour: Un tableau (liste) d'objets de la classe chargés ou un tableau (liste) avec les valeurs

        $paramsSQL = [];
        $paramsWHERE = [];
        $debut = microtime(true);

        if (!$table) { $table = constant($classe . "::TABLE"); }

        if (!empty($parametres)) {
            $champsAssocies = defined("$classe::CHAMPS_ASSOCIES") ? constant($classe . "::CHAMPS_ASSOCIES") : [];
            $champsRecherche = defined("$classe::CHAMPS") ? constant($classe . "::CHAMPS") : [];

            foreach ($parametres as $cle => $valeur) {

                // Support des associations imbriquées, example: "enchere.utilisateur"
                if (str_contains($cle, '.')) {
                    [$nomAssoc, $sousChamp] = explode('.', $cle, 2);

                    if (isset($champsAssocies[$nomAssoc])) {
                        $classeLiee = $champsAssocies[$nomAssoc]['champ_type'];
                        $prefixe = substr($nomAssoc, 0, 3) . "_";
                        $sousAssociees = defined("$classeLiee::CHAMPS_ASSOCIES")
                            ? constant($classeLiee . "::CHAMPS_ASSOCIES")
                            : [];

                        $clePlaceholder = ":" . str_replace('.', '_', $cle);

                        if (isset($sousAssociees[$sousChamp])) {
                            // ex: enchere.utilisateur -> jointure enc_uti_utilisateur
                            $classeSousLiee = $sousAssociees[$sousChamp]['champ_type'];
                            $tableSousLiee = $classeSousLiee::TABLE;
                            $sousPrefixe = $prefixe . substr($sousChamp, 0, 3) . "_";
                            $paramsWHERE[] = "`{$sousPrefixe}{$tableSousLiee}`.`id` = $clePlaceholder";
                        } else {
                            // ex: enchere.texte -> champ direct sur enc_enchere
                            $tableLiee = $classeLiee::TABLE;
                            $paramsWHERE[] = "`{$prefixe}{$tableLiee}`.`$sousChamp` = $clePlaceholder";
                        }
                        $paramsSQL[$clePlaceholder] = "$valeur";
                    }
                    continue;
                }
                // Recherche par plage: un param se terminant en _min ou _max est traduit en plus grande ou egal, ou plus petit ou egal
                if (str_ends_with($cle, '_min')) {
                    $champReel = substr($cle, 0, -4);
                    $paramsWHERE[] = "`$table`.`$champReel` >= :$cle";
                    $paramsSQL[":$cle"] = "$valeur";
                    continue;
                }
                if (str_ends_with($cle, '_max')) {
                    $champReel = substr($cle, 0, -4);
                    $paramsWHERE[] = "`$table`.`$champReel` <= :$cle";
                    $paramsSQL[":$cle"] = "$valeur";
                    continue;
                }
                $operateur = $champsRecherche[$cle]['champ_operateur'] ?? "EQUAL";
                if (is_array($valeur)) {
                    $placeholders = [];
                    foreach (array_values($valeur) as $i => $valeurItem) {
                        $clePlaceholder = ":{$cle}_{$i}";
                        $placeholders[] = $clePlaceholder;
                        $paramsSQL[$clePlaceholder] = "$valeurItem";
                    }
                    $paramsWHERE[] = "`$table`.`$cle` IN (" . implode(", ", $placeholders) . ")";
                } elseif ($operateur === "LIKE") {
                    $paramsWHERE[] = "`$table`.`$cle` LIKE :$cle";
                    $paramsSQL[":$cle"] = "%$valeur%";
                } else {
                    $paramsWHERE[] = "`$table`.`$cle` = :$cle";
                    $paramsSQL[":$cle"] = "$valeur";
                }
            }
        }

        if (empty($paramsWHERE)) {
            $paramsWHERE = ["1"];
        }
        //$champs = array_keys($classe::CHAMPS);
        

        // Construire la requête SQL brute
        $sql = $this->construireRequete($classe, $table, $paramsWHERE, $options, $champs);

        if (getenv('APP_TRACE_SQL') === 'true') {
            echo "Executing: " . $sql ;
            echo "Parametres:";
            print_r($paramsSQL);
        }
        try {

            // Preparer la requête dans la BDD
            $req = $this->bdd->prepare($sql);

            if (!$req) {
                $this->dernierErreur = "Erreur de préparation de la requête";
                return false;
            }

            // Executer la requête SQL avec les valeurs des paramètres:
            if (!$req->execute($paramsSQL)) {
                $this->dernierErreur = "Erreur d'exécution de la requête";
                return false;
            }
                
            // Exploiter le résultat sous forme d'un tableau de tableeaux (liste des notes)
            $lignes = $req->fetchAll(PDO::FETCH_ASSOC);
            Debogueur::enregistrerSQL($sql, $paramsSQL, microtime(true) - $debut);
            if (count($lignes) === 0) {
                // Aucune ligne recupéré
                $this->dernierErreur = "Aucun resultat recupéré";
                return [];
            } else {
                $associationsConfig = defined("$classe::CHAMPS_ASSOCIES") ? $classe::CHAMPS_ASSOCIES : [];
                if ($classe) {
                    return $this->assemblerAssociees($classe, $lignes, $associationsConfig) ?? [];
                } else {
                    return $lignes;
                }
            }
            
        } catch (PDOException $erreur) {
            $this->dernierErreur = $erreur->getMessage();
            throw new RuntimeException("Error SQL l'ors de l'execution de la requête: " . $sql . "Erreur PDO: " . $erreur->getMessage());
        }
    }
    
    public function update(string $classe, string $table, array $parametres, array $parametresWHERE) {
        // Rôle: Actualiser  dans la base de données avec les paramètres fournies
        // Paramètres:
        //      $classe - Classe de l'objet
        //      $parametres - Un tableau (liste simple) des paramètres
        //
        // Retour: true en cas de succés ou false
        if ($classe) {
            $table = constant($classe . "::TABLE");
        }

        $sql = "UPDATE `" . $table . "` SET ";


        // Fabriquer le tableau des paramêtres à passer à la requête SQL
        $params = [];
        $paramSQL = [];

        // Verifier les paramètres
        foreach ($parametres as $cle => $valeur) {
            if (empty($valeur)) {
                continue;
            }
            $paramSQL[] = "`$cle` = :$cle";
            $params[":$cle"] = "$valeur";
        }
        $paramsWHERE = [];
        foreach ($parametresWHERE as $cle => $valeur) {
            if (empty($valeur)) {
                continue;
            }
            $paramsWHERE[] = "`$cle` = :$cle";
            $params[":$cle"] = "$valeur";
        }

        if (empty($paramSQL)) {
            // Retourner false si on n'a pas des paramètres
            return false;
        }
        $sql = $sql . implode(", ", $paramSQL) . " WHERE " . implode("AND ", $paramsWHERE);

        try {
            // Construire la requête SQL brute
            

            $req = $this->executeSQL($sql, $params);
            if ($req) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $erreur) {
            $this->dernierErreur = $erreur->getMessage();
            throw new RuntimeException("Error SQL l'ors de l'execution de la requête: " . $sql . "Erreur PDO: " . $erreur->getMessage());
        }
    }
    public function create(string $classe, string $table, array $parametres) {
        // Rôle: Créer dans la base de données avec les paramètres fournies
        // Paramètres:
        //      $classe - Classe de l'objet
        //      $parametres - Un tableau (liste simple) des paramètres
        //
        // Retour: 
        if ($classe) { $table = constant($classe . "::TABLE"); }
        try {
            // Construire la requête SQL brute
            $sql = "INSERT INTO `$table` SET ";

            // Fabriquer le tableau des paramêtres à passer à la requête SQL
            $params = [];
            $paramSQL = [];

            // Paramètres accéptés:

            foreach ($parametres as $cle => $valeur) {
                if (empty($valeur)) {
                    continue;
                }
                $paramSQL[] = "`$cle` = :$cle";
                $params[":$cle"] = "$valeur";
            }


            if (empty($paramSQL)) {
                // Retourner une liste vide si on n'a pas des paramètres
                return null;
            }
            $sql .= implode(", ", $paramSQL);          

            $req = $this->executeSQL($sql, $params);
            // On récupére l'id d'insertion
            if (!$req) {
                return false;
            }
            $this->dernierId = $this->bdd->lastInsertId();


            return true;
        } catch (PDOException $erreur) {
            $this->dernierErreur = $erreur->getMessage();
            throw new RuntimeException("Error SQL l'ors de l'execution de la requête: " . ($sql ?? "") . "Erreur PDO: " . $erreur->getMessage());
        }
    }
    public function delete(string $classe, int $id) {
        // Rôle: Effacer de la base de données 
        // Paramètres:
        //      $id - ID à effacer
        //
        // Retour: true ou false
        try {
            // Construire la requête SQL brute
            $sql = "DELETE FROM " . $classe::TABLE . " WHERE `id` = :id";
            // Fabriquer le tableau des paramêtres à passer à la requête SQL
            $params = [ ":id" => $id];

            // On retourne true si la note est effacé
            $req = $this->executeSQL($sql, $params);

            return $req;
        } catch (PDOException $erreur) {
            $this->dernierErreur = $erreur->getMessage();
            throw new RuntimeException("Error SQL l'ors de l'execution de la requête: " . ($sql ?? "") . "Erreur PDO: " . $erreur->getMessage());
        }
    }
    

}
