<?php
// Composant Modele
//
// Modele générique pour les modéles sans classe
//
// Traite les Iterations, get, set, et les type des valeurs des champs
//

namespace App\Composant;

use IteratorAggregate;
use Traversable;
use ArrayIterator;
use DateTime;
use JsonSerializable;
use App\Composant\Champ;

abstract class Modele implements IteratorAggregate, JsonSerializable  {
    protected array $champsInstancies = [];

    public array $data = [];
    protected string $nomModele = '';
    protected static array $config = [];

    public function __construct(array $data = [], string $nomModele = '') {

        //$this->nomModele = $nomModele ?: strtolower((new ReflectionClass($this))->getShortName());
        // Moins lourde, utilisation de ReflectionClass sans y avoir besoin:
        $this->nomModele = $nomModele ?: strtolower(basename(str_replace('\\', '/', static::class)));

        // ! Est-ce que ce bout de code est vraiment Utilisée??? - À verifier
        $existingProperties = get_object_vars($this);
        unset($existingProperties['data'], $existingProperties['nomModele'], $existingProperties['config']);
        
        $dataSource = array_merge($existingProperties, $data);
        
        if (defined(static::class . '::CHAMPS')) {
            foreach (static::CHAMPS as $nomChamp => $configChamp) {
                $rawValue = $dataSource[$nomChamp] ?? null;
                
                $type = $configChamp['champ_type'] ?? 'string';

                $castValue = $this->castValue($rawValue, $type);

                $this->champsInstancies[$nomChamp] = new Champ($castValue, $configChamp);
                unset($dataSource[$nomChamp]);
            }
        }
        foreach ($dataSource as $key => $value) {
            $this->data[$key] = $value;
        }

    }

    public function hydrater(array $champs): void {
    // Role: Creer/instancier les objets des champs ou retourner les champs dejá instanciés
    // Parametres: $champs - Les champs a crrer
    // Retour: Néant

        $this->champsInstancies = $champs;

        if (defined(static::class . "::DATETIME")) {
            
            $nomChampDate = constant(static::class . "::DATETIME");

            if (isset($this->champsInstancies[$nomChampDate])) {
                $objetChampDate = $this->champsInstancies[$nomChampDate];
                
                $dateHeureRaw = $objetChampDate->getValue();

                if ($dateHeureRaw) {
                    $dateFormatee = (new DateTime($dateHeureRaw))->format('d/m/Y H:i');
                    
                    $this->champsInstancies['dateFormatee'] = new Champ($dateFormatee, [
                        'champ_nom' => 'Date Formatée',
                        'champ_type' => 'string'
                    ]);
                }
            }
        }
    }
    public function __get(string $nomChamp): mixed {
    // Methode magique __get  (Example: $modele->nomChamp )
    //   Role: Methode magique qui gere le retour de l'objet par rapport au type de valeur (array, objet, etc)
    //
    // Retour: La valeur (en cas d'objet) ou le type (int, string, array)

        if (array_key_exists($nomChamp, $this->champsInstancies)) {
            return $this->champsInstancies[$nomChamp];
        }
        // Si une propriété physique existe sur l'objet (ex: $resultat->utilisateur)
        if (property_exists($this, $nomChamp)) {
            return $this->$nomChamp;
        }
        // Par défaut, retourner le tableau data générique
        return $this->data[$nomChamp] ?? null;
    }
    public function __set(string $key, mixed $value): void {
    // Methode magique __set  (Example: $modele->nomChamp = "test")
    //   Role: Methode magique qui gere l'attribuition d'une valeur de l'objet.
    //         Si le champ est connu du Modele (déclaré dans CHAMPS ou CHAMPS_ASSOCIES), on le
    //         (re)construit en Champ typé — que ce champ ait déjà été hydraté ou non — sinon
    //         __get ne le retrouverait jamais (il regarde champsInstancies en premier).
    //
    // Retour: Néant

        $champs = defined(static::class . '::CHAMPS') ? static::CHAMPS : [];
        $champsAssocies = defined(static::class . '::CHAMPS_ASSOCIES') ? static::CHAMPS_ASSOCIES : [];

        if (array_key_exists($key, $champs) || array_key_exists($key, $champsAssocies)) {
            $configChamp = $champs[$key] ?? $champsAssocies[$key];
            $this->champsInstancies[$key] = new Champ($value, $configChamp);
            return;
        }

        $this->data[$key] = $value;
    }
    public function __unset(string $nomChamp): void {
    // Methode magique __unset  (Example: unset($modele->nomChamp) )
    //   Role: Methode magique qui gere l d'une valeur de l'objet
    //
    // Retour: Néant
        unset($this->champsInstancies[$nomChamp]);
    }
    public function __isset(string $name): bool {
    // Methode magique __isset
    //   Role: Methode magique qui est appelé quand on utilise isset sur un champ du Modele (e.g.: isset($modele->Champ))
    //
    // Retour: bool - Verification si la valeur existe dans le tableau des champs instancies

        return isset($this->champsInstancies[$name]);
    }
    public function champsAffichage(): array {
    // Role: Retourne les champs à afficher pour ce Modele, filtrés par CHAMPS_RECHERCHE
    //       si la classe enfant la définit (dans l'ordre de CHAMPS_RECHERCHE), sinon tous les champs
    //
    // Retour: array - [nomChamp => Champ|valeur]

        $tousLesChamps = array_merge($this->data, $this->champsInstancies);

        if (!defined(static::class . '::CHAMPS_RECHERCHE')) {
            return $tousLesChamps;
        }

        $champsFiltres = [];
        foreach (static::CHAMPS_RECHERCHE as $nomChamp) {
            if (array_key_exists($nomChamp, $tousLesChamps)) {
                $champsFiltres[$nomChamp] = $tousLesChamps[$nomChamp];
            }
        }
        return $champsFiltres;
    }
    public function jsonSerialize(): mixed {
    // Methode jsonSerialize
    //   Role: Retourner les valeurs des champs - Implementation de JsonSerializable, appelé automatiquement quand on appele json_encode
    //
    // Retour: array - Liste avec les valeurs des champs

        $tousLesChamps = array_merge($this->data, $this->champsInstancies);

        return array_filter($tousLesChamps, function ($valeur) {
            if ($valeur instanceof Champ) {
                return empty($valeur->sensible);
            }
            return true;
        });
    }
    
    private function castValue(mixed $value, string $type) {
    // Methode castValue
    //   Role: Attribue le type de valeur d'un Champ
    //
    // Retour: La valeur (en cas d'objet) ou le type (int, string, array)
    // ! A FAIR -> Les champs typees pour DateTime par example (plus complexe, necessite de types de dates entre autres)

        if (class_exists($type)) {
            return $value;
        }

        if ($value === null) {
            return match ($type) {
                'int', 'integer' => 0,
                'array'          => [],
                'bool', 'boolean'=> false,
                default           => ''
            };
        }

        return match ($type) {
            'int', 'integer'   => (int)$value,
            'float', 'double'  => (float)$value,
            'bool', 'boolean'  => (bool)$value,
            'array'            => (array)$value,
            default            => (string)$value,
        };
    }
    public function getIterator(): Traversable {
    // Methode getIterator
    //   Role: Permet a l'objet d'être utilisée en boucle
    //
    // Retour: La boucle avec les données de l'objet

        $allData = array_merge($this->data, $this->champsInstancies);
        return new ArrayIterator($allData);
    }
    public function __toString(): string {
    // Role: Utilisée pour imprimer un champ definie en DISPLAY_FIELD pour chaque Modele pour quand on boucle sur les champs pour
    //     les imprimer pour qu'il ait un valeur qui sort plutot que erreur de conversion string
        
        $champAffichage = defined(static::class . '::DISPLAY_FIELD') ? static::DISPLAY_FIELD : 'id';

        if (isset($this->champsInstancies[$champAffichage])) {
            return (string)$this->champsInstancies[$champAffichage];
        }
        if (property_exists($this, $champAffichage)) {
            return (string)$this->$champAffichage;
        }
        return (string)($this->data[$champAffichage] ?? '');
    }
}