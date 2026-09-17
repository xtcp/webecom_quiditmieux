<?php
// Composant Champ
//
// Modele générique pour les champs d'un Modele
//
// Role: Traite le type, les Iterations, get, count, toString de l'objet Champ
//
// Methodes: 


namespace App\Composant;

use Countable;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use EmptyIterator;
use JsonSerializable;

class Champ implements Countable, IteratorAggregate, JsonSerializable {
    private mixed $value;
    private array $meta;

    public function __construct(mixed $value, array $meta) {
        $type = $meta['champ_type'] ?? 'string';
        //$type = $meta['input_type'] ?? 'text';
        $this->value = $this->castValue($value, $type);
        $this->meta = $meta;
    }

    public function castValue(mixed $value, string $type): mixed {
    // Methode castValue
    //   Role: Attribue le type de valeur d'un Champ
    //
    // Retour: La valeur (en cas d'objet) ou le type (int, string, array)
    // ! A FAIR -> Les champs typees pour DateTime par example (plus complexe, necessite de types de dates entre autres)
        if (class_exists($type)) {
            return $value;
        }

        if (is_object($value) || is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return match ($type) {
                'int', 'integer'   => 0,
                'float', 'double'  => 0.0,
                'array'            => [],
                'bool', 'boolean'  => false,
                default            => ''
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

    public function __get(string $key) {
    // Methode magique __get  (Example: $modele->Champ )
    //   Role: Methode magique qui gere le retour de l'objet par rapport au type de valeur (array, objet, etc)
    //
    // Retour: La valeur (en cas d'objet) ou le type (int, string, array)
    
        if (array_key_exists($key, $this->meta)) {
            $champ = $this->meta[$key];
            // Verifier si camp est vide?
            
            return $champ;
        }
        
        if (is_object($this->value)) {
            return $this->value->$key;
        }

        if (is_array($this->value)) {
            $resultats = [];
            foreach ($this->value as $item) {
                if (is_object($item) && isset($item->$key)) {
                    $resultats[] = (string)$item->$key;
                }
            }
            return !empty($resultats) ? implode(', ', $resultats) : null;
        }
        return null;
    }

    public function __toString(): string {
    // Methode magique __toString
    //   Role: Methode magique qui est appelé quand on essaye "d'imprimer" le Champ (e.g.: echo $modele->Champ)
    //
    // Retour: string - La valeur traité (en cas d'array ou bool) ou string

        if (is_array($this->value)) {
            return implode(', ', array_map('strval', $this->value));
        }

        if (is_bool($this->value)) {
            return $this->value ? '1' : '';
        }
        return (string)$this->value;
    }
    public function __isset(string $name): bool {
    // Methode magique __isset
    //   Role: Methode magique qui est appelé quand on utilise isset sur le Champ (e.g.: isset($modele->Champ))
    //
    // Retour: bool - Verification si la valeur existe dans le tableau ou isset classique pour un objet

        if (array_key_exists($name, $this->meta)) {
            return true;
        }

        if (is_object($this->value)) {
            return isset($this->value->$name);
        }

        return false;
    }
    public function jsonSerialize(): mixed {
    // Methode jsonSerialize
    //   Role: Retourner la valeur du champ - Implementation de JsonSerializable, appelé automatiquement quand on appele json_encode
    //
    // Retour: La valeur du Champ

        return $this->value;
    }
    public function count(): int {
    // Methode count
    //   Role: Methode pour retourner la taille de la valeur du Champ (d'un array ou d'un objet de champ Countable) (e.g.: isset($modele->Champ->count()))
    //
    // Retour: int - Taille de la valeur du champ

        if (is_array($this->value)) {
            return count($this->value);
        }
        
        if ($this->value instanceof Countable) {
            return count($this->value);
        }
        return $this->value !== null ? 1 : 0;
    }
    public function contient(string $propriete, mixed $valeur): bool {
    // Methode contient
    //   Role: Methode pour verifier si une liste ou objet en Champ contient une valeur (e.g.: isset($modele->Champ->contient("abc")))
    //
    // Retour: bool - true ou false

        if (!is_array($this->value)) {
            return false;
        }
        foreach ($this->value as $item) {
            if (is_object($item) && (string)$item->$propriete === (string)$valeur) {
                return true;
            }
        }
        return false;
    }
    public function getIterator(): Traversable {
    // Methode getIterator
    //   Role: Gestion des boucles par rapport au type de Champ (IteratorAggregate)
    //
    // Retour: Traversable - La boucle (ArrayIterator/getIterator/Traversable/EmptyIterator)

        if (is_array($this->value)) {
            return new ArrayIterator($this->value);
        }
        if ($this->value instanceof IteratorAggregate) {
            return $this->value->getIterator();
        }
        if ($this->value instanceof Traversable) {
            return $this->value;
        }
        if ($this->value !== null) {
            return new ArrayIterator([$this->value]);
        }
        return new EmptyIterator();
    }
    public function getValue(): mixed {
    // Methode getValue
    //   Role: Retourner la valeur d'un champ
    //
    // Retour: mixed - La valeur du champ

        return $this->value;
    }
}