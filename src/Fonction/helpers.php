<?php
//  App\Fonction\helpers.php
//    Role: Fonctions de formatation et autres
//
//
// Methodes:
//     e, formaterDuree, formaterJava


use App\Composant\Champ;


function e(mixed $value): string {
// Methode e
//   Role: Filtrer les valeurs "imprimés" en HTML avec htmlentities
//
// Retour: string filtrée par htmlentities
    if (is_array($value)) {
        return (string)count($value);
    }
    if ($value instanceof Champ) {
        return htmlentities((string)$value, ENT_QUOTES, 'UTF-8');
    }

    return htmlentities((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function formaterDuree(mixed $duree): string {
// Methode formaterDuree
//   Role: Format une valeur de durée en minutes/heures
//
// Retour: string traité (e.g.: 1h 30min)

    if ($duree instanceof \App\Composant\Champ) {
        $duree = $duree->getValue();
    }

    $minutes = is_numeric($duree) ? (int)$duree : 0;

    if ($minutes <= 0) return "—";

    $h = intdiv($minutes, 60);
    $m = $minutes % 60;

    if ($h > 0 && $m > 0) return "{$h}h {$m}min";
    if ($h > 0) return "{$h}h";
    return "{$m}min";
}
function normaliserValeur(string $valeur): string {
    // Si la valeur ressemble à un datetime-local ISO (2026-09-16T23:00 ou T23:00:10),
    // on la convertit au format MySQL. Sinon on la laisse telle quelle.
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $valeur)) {
        return str_replace('T', ' ', $valeur);
    }
    return $valeur;
}
function formaterJava(\Throwable $erreur, $enregistree=null, $traceOverride=null) {
// Methode formaterJava
//   Role: Format un "Throwable" php en format d'erreur en style Java pour meilleure visibilité
//
// Paramètres:
//      erreur - Le "Throwable" php avec le warning/erreur
//      enregistree - bool - A-t-on dejá enregistrée cette erreur?
//      traceOverride - array|null - Trace à utiliser à la place de $erreur->getTrace()
//                      (utile pour retirer une frame artificielle, ex: erreurs PHP)
//
// Retour: $resultat array - Liste de lignes de l'erreur

    $starter = $enregistree ? 'Causée par: ' : '';
    $resultat = array();
    if (!$enregistree) $enregistree = array();
    $trace  = $traceOverride ?? $erreur->getTrace();
    $precedent   = $erreur->getPrevious();
    $resultat[] = sprintf('%s%s: %s', $starter, get_class($erreur), $erreur->getMessage());
    $fichier = $erreur->getFile();
    $ligne = $erreur->getLine();
    while (true) {
        $resultat[] = sprintf(' en %s%s%s(%s%s%s)',
            count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
            count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
            count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
            $ligne === null ? $fichier : basename($fichier),
            $ligne === null ? '' : ':',
            $ligne === null ? '' : $ligne);

        if (is_array($enregistree))
            $enregistree[] = "$fichier:$ligne";
        if (!count($trace))
            break;
        $fichier = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Source unconnu';
        $ligne = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
        array_shift($trace);
    }
    $resultat = join("\n", $resultat);
    if ($precedent)
        $resultat  .= "\n" . formaterJava($precedent, $enregistree);

    return $resultat;
}
function pluriel(int $valeur, string $mot): string {
// Fonction pluriel
// Role: Accorde un mot au pluriel si besoin (regle simple, sans exceptions)
//
// Params: $valeur - Le nombre associe au mot
//         $mot - Le mot au singulier
// Retour: Le mot accorde

    return $valeur > 1 ? $mot . "s" : $mot;
}

function tempsRestant(string $dateheureStr): string {
// Fonction tempsRestant
// Role: Calcule le temps restant entre maintenant et une date de fin,
//       et le formate en texte lisible (jours/heures, ou heures/minutes)
//
// Params: $dateheureStr - Date au format "YYYY-MM-DD HH:MM:SS"
// Retour: string - Le temps restant formate (ex: "23 jours, 3 heures"),
//         ou "Terminé" si la date est passee

    $dateFin = new DateTime($dateheureStr);
    $maintenant = new DateTime();

    $diffSecondes = $dateFin->getTimestamp() - $maintenant->getTimestamp();

    if ($diffSecondes <= 0) return "Terminé";

    $jours = intdiv($diffSecondes, 86400);
    $heures = intdiv($diffSecondes % 86400, 3600);
    $minutes = intdiv($diffSecondes % 3600, 60);

    $parties = [];

    if ($jours > 0) {
        $parties[] = "$jours " . pluriel($jours, "jour");
        if ($heures > 0) $parties[] = "$heures " . pluriel($heures, "heure");
    } elseif ($heures > 0) {
        $parties[] = "$heures " . pluriel($heures, "heure");
        if ($minutes > 0) $parties[] = "$minutes " . pluriel($minutes, "minute");
    } else {
        $minutesAffichees = max(1, $minutes);
        $parties[] = "$minutesAffichees " . pluriel($minutesAffichees, "minute");
    }

    return implode(", ", $parties);
}
