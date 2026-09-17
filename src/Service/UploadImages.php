<?php
namespace App\Service;

use RuntimeException;

class UploadImages {
// Role: Gere l'upload securise d'images (validation, renommage, stockage) pour n'importe
//       quelle entite qui possede une table pivot vers `image`
//
// Methodes: televerser, televerserPlusieurs

    private const TYPES_AUTORISES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    private const TAILLE_MAX_OCTETS = 2 * 1024 * 1024; // 2 Mo, doit matcher MAX_FILE_SIZE du formulaire
    private const LARGEUR_MAX = 4000;
    private const HAUTEUR_MAX = 4000;

    public function __construct(
        private readonly string $dossierDestination
    ) {
        // Role: Prepare le service avec le dossier absolu de destination des images
        //
        // Parametres: $dossierDestination - Chemin absolu du dossier (ex: __DIR__ . '/public/images/vente')

        if (!is_dir($this->dossierDestination)) {
            throw new RuntimeException("Le dossier de destination n'existe pas: {$this->dossierDestination}");
        }
        if (!is_writable($this->dossierDestination)) {
            throw new RuntimeException("Le dossier de destination n'est pas accessible en ecriture: {$this->dossierDestination}");
        }
    }

    public function televerser(array $fichier): string {
    // Fonction televerser
    // Role: Valide et deplace un seul fichier uploade (une entree de $_FILES) vers le
    //       dossier de destination, avec un nom genere aleatoirement (jamais le nom client)
    //
    // Parametres: $fichier - Une entree $_FILES (ex: $_FILES['image'], ou un sous-tableau
    //                        d'un $_FILES['images'] deja "aplati" par index)
    //
    // Retour: Le nom du fichier stocke (a sauvegarder en base, pas le chemin complet)
    // Leve: RuntimeException si le fichier est invalide

        $this->validerErreurUpload($fichier['error'] ?? UPLOAD_ERR_NO_FILE);

        if (!is_uploaded_file($fichier['tmp_name'])) {
            // Protege contre un appel avec un chemin arbitraire au lieu d'un vrai upload HTTP
            throw new RuntimeException("Fichier invalide (pas un upload HTTP légitime)");
        }

        if ($fichier['size'] <= 0 || $fichier['size'] > self::TAILLE_MAX_OCTETS) {
            throw new RuntimeException("La taille de l'image dépasse la limite autorisée (2 Mo)");
        }

        // Detection du type reel via le contenu du fichier (finfo), jamais via
        // $fichier['type'] qui vient du navigateur et est falsifiable
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $typeMime = $finfo->file($fichier['tmp_name']);

        if (!isset(self::TYPES_AUTORISES[$typeMime])) {
            throw new RuntimeException("Type de fichier non autorisé (jpeg, png, webp uniquement)");
        }

        // Verifie que c'est reellement une image exploitable (rejette un fichier
        // qui aurait un bon type MIME de tete mais un contenu corrompu/malveillant)
        $infosImage = @getimagesize($fichier['tmp_name']);
        if ($infosImage === false) {
            throw new RuntimeException("Le fichier n'est pas une image valide");
        }
        [$largeur, $hauteur] = $infosImage;
        if ($largeur > self::LARGEUR_MAX || $hauteur > self::HAUTEUR_MAX) {
            throw new RuntimeException("Dimensions de l'image trop grandes (max " . self::LARGEUR_MAX . "x" . self::HAUTEUR_MAX . "px)");
        }

        // Nom de fichier entierement genere cote serveur - jamais derive du nom client
        $extension = self::TYPES_AUTORISES[$typeMime];
        $nomFichier = bin2hex(random_bytes(16)) . '.' . $extension;
        $cheminDestination = $this->dossierDestination . DIRECTORY_SEPARATOR . $nomFichier;

        if (!move_uploaded_file($fichier['tmp_name'], $cheminDestination)) {
            throw new RuntimeException("Impossible de déplacer le fichier téléversé");
        }
        chmod($cheminDestination, 0644);

        return $nomFichier;
    }
    public function televerserPlusieurs(array $filesEntry): array {
        $resultats = [];

        try {
            foreach ($filesEntry['tmp_name'] as $index => $tmpName) {
                if (($filesEntry['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $fichierUnitaire = [
                    'name'     => $filesEntry['name'][$index],
                    'type'     => $filesEntry['type'][$index],
                    'tmp_name' => $filesEntry['tmp_name'][$index],
                    'error'    => $filesEntry['error'][$index],
                    'size'     => $filesEntry['size'][$index],
                ];

                $resultats[$index] = $this->televerser($fichierUnitaire);
            }
        } catch (RuntimeException $erreur) {
            // Nettoyer les fichiers deja televerses avant l'echec, pour ne pas les laisser orphelins
            foreach ($resultats as $nomFichier) {
                $chemin = $this->dossierDestination . DIRECTORY_SEPARATOR . $nomFichier;
                if (is_file($chemin)) {
                    unlink($chemin);
                }
            }
            throw $erreur; // Rethrow: le caller doit toujours savoir que ça a echoué
        }

        return $resultats;
    }

    private function validerErreurUpload(int $codeErreur): void {
    // Fonction validerErreurUpload
    // Role: Traduit les codes d'erreur PHP d'upload en messages explicites
    //
    // Parametres: $codeErreur - Une des constantes UPLOAD_ERR_*

        match ($codeErreur) {
            UPLOAD_ERR_OK => null,
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => throw new RuntimeException("Le fichier dépasse la taille maximale autorisée par le serveur"),
            UPLOAD_ERR_PARTIAL => throw new RuntimeException("Le fichier n'a été que partiellement téléversé"),
            UPLOAD_ERR_NO_FILE => throw new RuntimeException("Aucun fichier n'a été téléversé"),
            default => throw new RuntimeException("Erreur lors du téléversement (code $codeErreur)"),
        };
    }
}