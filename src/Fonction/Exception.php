<?php
//  App\Fonction\Exception.php
//    Role: Enregistre les erreurs PHP pour affichage en mode deboggage
//
//
// Methodes:
//     enregistrer, enregistrerException

namespace App\Fonction;
use Throwable;
use ErrorException;

class GestionnaireException {
    private static array $erreurs = [];
    private static bool $pageChargee = false;

    public static function enregistrer(int $niveau, string $message, string $file, int $line): bool {
    // Role: Capture l'appel de gestionnaire d'erreurs de "set_error_handler(['\App\Fonction\GestionnaireException', 'enregistrer'])"
    //
    // Paramètres: Throwable $exception - L'erreur/exception PHP
    // Retourn: bool
    
        if (!(error_reporting() & $niveau)) return false;
        $type = match($niveau) {
            E_USER_ERROR, E_RECOVERABLE_ERROR  => 'Danger / Erreur',
            E_WARNING, E_USER_WARNING          => 'Warning',
            E_NOTICE, E_USER_NOTICE            => 'Notice / Info',
            E_DEPRECATED, E_USER_DEPRECATED    => 'Déprécié',
            default                            => 'Autre Alerte'
        };
        $warningException = new ErrorException($message, 0, $niveau, $file, $line);
        $trace = $warningException->getTrace();
        array_shift($trace);

        self::$erreurs[] = [
            'type'  => $type,
            'objet' => $warningException,
            'trace' => $trace
        ];

        return true;
    }

    public static function enregistrerException(Throwable $exception): void {
    // Role: Enregistre les erreurs dans le tableau $erreurs
    //
    // Paramètres: Throwable $exception - L'erreur PHP
    // Retourn: Néant

        self::$erreurs[] = [
            "type"    => "Exception",
            "message" => $exception->getMessage(),
            "objet"   => $exception,
            "trace"   => $exception->getTrace(),
        ];
    }

    public function __invoke(Throwable $exception): void {
    // Role: Capture l'appel de gestionnaire d'erreurs de "set_exception_handler(new GestionnaireException)"
    //
    // Paramètres: Throwable $exception - L'erreur/exception PHP
    // Retourn: Néant
    
        if (ob_get_level() > 0) {
            ob_clean();
        }

        $codeStatus = $exception->getCode();
        if ($codeStatus < 400 || $codeStatus >= 600) {
            $codeStatus = 500;
        }
        //http_response_code($codeStatus);

        $estAPI = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
                 (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

        if ($estAPI) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                "status"  => "error",
                "code"    => $codeStatus,
                "message" => $exception->getMessage(),
                "trace"   => getenv('APP_DEBUG') == "true" ? $exception->getTrace() : []
            ]);
            exit();
        }

        self::enregistrerException($exception);
        
        if (!self::$pageChargee) {
            $erreurs = self::$erreurs;
            $resultat = "";
            include(__DIR__ . '/../../src/template/base/layout.php');
            //include(__DIR__ . '/../../src/template/base/debug.php');
        }
    }

    public static function recupererErreurs() {
    // Role: Retourne la liste d'$erreurs
    // Retour:: self::$erreurs

        self::$pageChargee = true;
        return self::$erreurs;
    }
}